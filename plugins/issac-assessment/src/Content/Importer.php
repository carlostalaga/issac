<?php
namespace Issac\Content;

defined('ABSPATH') || exit;

/**
 * Idempotent importer: reads data/instrument-2023.06.json and creates (or
 * updates) the CPT posts that represent the instrument's content model.
 *
 * Matching rules:
 *   - Domains:     by ACF `domain_code`
 *   - Subsections: by post_title within a parent domain
 *   - Items:       by ACF `item_code`
 *
 * Never deletes; only creates missing posts (and optionally refreshes text
 * when $updateText is true). Sets `menu_order` from the document sequence.
 */
final class Importer
{
    /** @var array<string,int> domain_code → post ID */
    private array $domainMap = [];

    /** @var array<string,int> "domainId:title" → post ID */
    private array $subsectionMap = [];

    /** @var array<string,int> item_code → post ID */
    private array $itemMap = [];

    private array $tally = [
        'domains'     => ['created' => 0, 'matched' => 0, 'updated' => 0],
        'subsections' => ['created' => 0, 'matched' => 0, 'updated' => 0],
        'items'       => ['created' => 0, 'matched' => 0, 'updated' => 0],
    ];

    public function __construct(
        private readonly bool $dryRun = false,
        private readonly bool $updateText = false,
    ) {
    }

    /**
     * Run the full import and return a tally of what happened.
     *
     * @return array{domains: array, subsections: array, items: array,
     *               per_domain: array<string,int>}
     */
    public function run(): array
    {
        $json = $this->loadJson();
        $this->loadExistingPosts();

        $domainOrder     = 0;
        $perDomain       = [];

        foreach ($json as $domainData) {
            $domainOrder++;
            $domainId = $this->importDomain($domainData, $domainOrder);

            $subsectionOrder = 0;
            $domainItemCount = 0;

            foreach ($domainData['subsections'] as $subsectionData) {
                $subsectionOrder++;
                $subsectionId = $this->importSubsection(
                    $subsectionData,
                    $domainId,
                    $subsectionOrder
                );

                $itemOrder = 0;
                foreach ($subsectionData['items'] as $itemData) {
                    $itemOrder++;
                    $domainItemCount++;
                    $this->importItem($itemData, $subsectionId, $itemOrder);
                }
            }

            $perDomain[$domainData['domain_code']] = $domainItemCount;
        }

        $this->tally['per_domain'] = $perDomain;
        return $this->tally;
    }

    private function loadJson(): array
    {
        $path = ISSAC_PATH . 'data/instrument-2023.06.json';
        if (!file_exists($path)) {
            throw new \RuntimeException("Instrument JSON not found at {$path}");
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || empty($data)) {
            throw new \RuntimeException('Instrument JSON is empty or malformed');
        }

        return $data;
    }

    /**
     * Pre-load all existing instrument posts so matching is one query per CPT
     * rather than per-record.
     */
    private function loadExistingPosts(): void
    {
        foreach ($this->fetchAll(PostTypes::DOMAIN) as $post) {
            $code = get_field('domain_code', $post->ID);
            if ($code !== '' && $code !== null && $code !== false) {
                $this->domainMap[(string) $code] = (int) $post->ID;
            }
        }

        foreach ($this->fetchAll(PostTypes::SUBSECTION) as $post) {
            $domainId = (int) get_field('domain', $post->ID);
            $key = $domainId . ':' . $post->post_title;
            $this->subsectionMap[$key] = (int) $post->ID;
        }

        foreach ($this->fetchAll(PostTypes::ITEM) as $post) {
            $code = get_field('item_code', $post->ID);
            if ($code !== '' && $code !== null && $code !== false) {
                $this->itemMap[(string) $code] = (int) $post->ID;
            }
        }
    }

    /** @return \WP_Post[] */
    private function fetchAll(string $postType): array
    {
        return get_posts([
            'post_type'              => $postType,
            'post_status'            => ['publish', 'draft', 'private'],
            'posts_per_page'         => -1,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'suppress_filters'       => false,
        ]);
    }

    private function importDomain(array $data, int $order): int
    {
        $code = (string) $data['domain_code'];

        if (isset($this->domainMap[$code])) {
            $postId = $this->domainMap[$code];
            $this->tally['domains']['matched']++;

            if (!$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'menu_order' => $order,
                ]);
            }

            if ($this->updateText && !$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'post_title' => $data['title'],
                ]);
                update_field('description', $data['description'], $postId);
                $this->tally['domains']['updated']++;
            }

            return $postId;
        }

        $this->tally['domains']['created']++;

        if ($this->dryRun) {
            $fakeId = -(100 + (int) $code);
            $this->domainMap[$code] = $fakeId;
            return $fakeId;
        }

        $postId = wp_insert_post([
            'post_type'   => PostTypes::DOMAIN,
            'post_status' => 'publish',
            'post_title'  => $data['title'],
            'menu_order'  => $order,
        ], true);

        if (is_wp_error($postId)) {
            throw new \RuntimeException(
                "Failed to create domain {$code}: " . $postId->get_error_message()
            );
        }

        update_field('domain_code', $code, $postId);
        update_field('description', $data['description'], $postId);

        $this->domainMap[$code] = $postId;
        return $postId;
    }

    private function importSubsection(array $data, int $domainId, int $order): int
    {
        $title = (string) $data['title'];
        $key   = $domainId . ':' . $title;

        if (isset($this->subsectionMap[$key])) {
            $postId = $this->subsectionMap[$key];
            $this->tally['subsections']['matched']++;

            if (!$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'menu_order' => $order,
                ]);
            }

            if ($this->updateText && !$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'post_title' => $title,
                ]);
                $this->tally['subsections']['updated']++;
            }

            return $postId;
        }

        $this->tally['subsections']['created']++;

        if ($this->dryRun) {
            $fakeId = -(200 + count($this->subsectionMap));
            $this->subsectionMap[$key] = $fakeId;
            return $fakeId;
        }

        $postId = wp_insert_post([
            'post_type'   => PostTypes::SUBSECTION,
            'post_status' => 'publish',
            'post_title'  => $title,
            'menu_order'  => $order,
        ], true);

        if (is_wp_error($postId)) {
            throw new \RuntimeException(
                "Failed to create subsection '{$title}': " . $postId->get_error_message()
            );
        }

        update_field('domain', $domainId, $postId);

        $this->subsectionMap[$key] = $postId;
        return $postId;
    }

    private function importItem(array $data, int $subsectionId, int $order): int
    {
        $code  = (string) $data['item_code'];
        $label = $code . ' — ' . mb_substr($data['prompt'], 0, 80);

        if (isset($this->itemMap[$code])) {
            $postId = $this->itemMap[$code];
            $this->tally['items']['matched']++;

            if (!$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'menu_order' => $order,
                ]);
                update_field('subsection', $subsectionId, $postId);
            }

            if ($this->updateText && !$this->dryRun) {
                wp_update_post([
                    'ID'         => $postId,
                    'post_title' => $label,
                ]);
                update_field('prompt', $data['prompt'], $postId);
                update_field('descriptor_1', $data['descriptor_1'], $postId);
                update_field('descriptor_3', $data['descriptor_3'], $postId);
                update_field('descriptor_5', $data['descriptor_5'], $postId);
                $this->tally['items']['updated']++;
            }

            return $postId;
        }

        $this->tally['items']['created']++;

        if ($this->dryRun) {
            $fakeId = -(300 + count($this->itemMap));
            $this->itemMap[$code] = $fakeId;
            return $fakeId;
        }

        $postId = wp_insert_post([
            'post_type'   => PostTypes::ITEM,
            'post_status' => 'publish',
            'post_title'  => $label,
            'menu_order'  => $order,
        ], true);

        if (is_wp_error($postId)) {
            throw new \RuntimeException(
                "Failed to create item {$code}: " . $postId->get_error_message()
            );
        }

        update_field('item_code', $code, $postId);
        update_field('prompt', $data['prompt'], $postId);
        update_field('descriptor_1', $data['descriptor_1'], $postId);
        update_field('descriptor_3', $data['descriptor_3'], $postId);
        update_field('descriptor_5', $data['descriptor_5'], $postId);
        update_field('subsection', $subsectionId, $postId);
        update_field('is_active', true, $postId);

        $this->itemMap[$code] = $postId;
        return $postId;
    }
}
