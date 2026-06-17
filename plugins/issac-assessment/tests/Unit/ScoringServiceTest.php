<?php
namespace Issac\Tests\Unit;

use Issac\Domain\DomainNode;
use Issac\Domain\ItemNode;
use Issac\Domain\ScoringService;
use Issac\Domain\SubsectionNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ScoringService — pure PHP, no WordPress bootstrap.
 *
 * The full instrument has 69 items (D1:9, D2:15, D3:22, D4:5, D5:18),
 * but tests build minimal fixtures to isolate specific behaviour.
 */
final class ScoringServiceTest extends TestCase
{
    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Build a minimal item with sensible defaults.
     */
    private static function item(
        string $code,
        bool $isActive = true,
        int $subsectionId = 1,
    ): ItemNode {
        return new ItemNode(
            id: 0,
            itemCode: $code,
            label: "Item {$code}",
            prompt: "Prompt for {$code}",
            descriptor1: '',
            descriptor3: '',
            descriptor5: '',
            isActive: $isActive,
            menuOrder: 0,
            subsectionId: $subsectionId,
        );
    }

    private static function subsection(
        array $items,
        int $id = 1,
        string $title = 'Sub',
        int $domainId = 1,
    ): SubsectionNode {
        return new SubsectionNode(
            id: $id,
            title: $title,
            menuOrder: 0,
            domainId: $domainId,
            items: $items,
        );
    }

    private static function domain(
        string $code,
        array $subsections,
        string $title = '',
    ): DomainNode {
        return new DomainNode(
            id: 0,
            code: $code,
            title: $title ?: "Domain {$code}",
            description: '',
            menuOrder: 0,
            subsections: $subsections,
        );
    }

    /**
     * Build a realistic 5-domain tree matching the actual instrument counts.
     * D1:9, D2:15, D3:22, D4:5, D5:18 = 69 items.
     */
    private static function fullTree(): array
    {
        $counts = [1 => 9, 2 => 15, 3 => 22, 4 => 5, 5 => 18];
        $domains = [];
        foreach ($counts as $d => $itemCount) {
            $items = [];
            for ($i = 1; $i <= $itemCount; $i++) {
                $items[] = self::item("{$d}.{$i}");
            }
            $domains[] = self::domain((string) $d, [
                self::subsection($items, $d, "Sub {$d}", $d),
            ]);
        }
        return $domains;
    }

    // ── Happy paths ─────────────────────────────────────────────────

    public function test_single_response(): void
    {
        $tree      = self::fullTree();
        $responses = ['1.1' => 4];
        $summary   = ScoringService::summary($tree, $responses);

        $this->assertSame(1, $summary['overall']['answered']);
        $this->assertSame(69, $summary['overall']['total']);

        // D1 average: single score of 4.
        $d1 = $summary['domains'][0];
        $this->assertSame(4.0, $d1['average']);
        $this->assertSame('Sustained Action', $d1['band']);
    }

    public function test_full_domain_completion(): void
    {
        $tree = self::fullTree();

        // Score all 9 D1 items.
        $responses = [];
        for ($i = 1; $i <= 9; $i++) {
            $responses["1.{$i}"] = 3;
        }

        $completion = ScoringService::completion($tree, $responses);

        $this->assertSame(100.0, $completion['domains'][0]['percent']);
        $this->assertSame(0.0, $completion['domains'][1]['percent']);
        $this->assertSame(0.0, $completion['domains'][2]['percent']);
        $this->assertSame(0.0, $completion['domains'][3]['percent']);
        $this->assertSame(0.0, $completion['domains'][4]['percent']);
    }

    public function test_mixed_scores_average(): void
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = self::item("1.{$i}");
        }
        $tree = [self::domain('1', [self::subsection($items)])];
        $responses = [
            '1.1' => 1, '1.2' => 2, '1.3' => 3, '1.4' => 4, '1.5' => 5,
        ];

        $avg = ScoringService::overallAverage($tree, $responses);
        $this->assertSame(3.0, $avg);
        $this->assertSame('Implementing', ScoringService::band($avg));
    }

    public function test_summary_structure(): void
    {
        $tree = self::fullTree();
        $responses = ['1.1' => 3, '2.1' => 4];
        $summary = ScoringService::summary($tree, $responses);

        // Overall keys.
        $this->assertArrayHasKey('completion', $summary['overall']);
        $this->assertArrayHasKey('average', $summary['overall']);
        $this->assertArrayHasKey('band', $summary['overall']);
        $this->assertArrayHasKey('answered', $summary['overall']);
        $this->assertArrayHasKey('total', $summary['overall']);

        // 5 domains.
        $this->assertCount(5, $summary['domains']);

        $d1 = $summary['domains'][0];
        $this->assertArrayHasKey('code', $d1);
        $this->assertArrayHasKey('title', $d1);
        $this->assertArrayHasKey('completion', $d1);
        $this->assertArrayHasKey('average', $d1);
        $this->assertArrayHasKey('band', $d1);
        $this->assertArrayHasKey('answered', $d1);
        $this->assertArrayHasKey('total', $d1);
        $this->assertArrayHasKey('subsections', $d1);

        // Subsection keys.
        $sub = $d1['subsections'][0];
        $this->assertArrayHasKey('title', $sub);
        $this->assertArrayHasKey('average', $sub);
        $this->assertArrayHasKey('band', $sub);
        $this->assertArrayHasKey('answered', $sub);
        $this->assertArrayHasKey('total', $sub);
    }

    // ── Edge cases ──────────────────────────────────────────────────

    public function test_empty_responses(): void
    {
        $tree    = self::fullTree();
        $summary = ScoringService::summary($tree, []);

        $this->assertSame(0, $summary['overall']['answered']);
        $this->assertSame(69, $summary['overall']['total']);
        $this->assertSame(0.0, $summary['overall']['completion']);
        $this->assertNull($summary['overall']['average']);
        $this->assertSame('Not yet rated', $summary['overall']['band']);
    }

    public function test_zero_active_domain(): void
    {
        // All items in the domain are inactive.
        $items = [
            self::item('1.1', isActive: false),
            self::item('1.2', isActive: false),
        ];
        $tree = [self::domain('1', [self::subsection($items)])];

        $completion = ScoringService::completion($tree, []);
        $this->assertSame(0, $completion['domains'][0]['total']);
        $this->assertSame(0.0, $completion['domains'][0]['percent']);

        $avg = ScoringService::domainAverage($tree[0], []);
        $this->assertNull($avg);

        $avg = ScoringService::domainAverage($tree[0], ['1.1' => 3]);
        $this->assertNull($avg);
    }

    public function test_inactive_items_excluded(): void
    {
        $items = [
            self::item('1.1', isActive: true),
            self::item('1.2', isActive: false),
            self::item('1.3', isActive: true),
        ];
        $tree = [self::domain('1', [self::subsection($items)])];
        $responses = ['1.1' => 4, '1.2' => 5];

        $completion = ScoringService::completion($tree, $responses);

        // Only 2 active items, only 1 answered among active.
        $this->assertSame(2, $completion['overall']['total']);
        $this->assertSame(1, $completion['overall']['answered']);
        $this->assertSame(50.0, $completion['overall']['percent']);

        // Average only from active answered items (1.1 = 4).
        $avg = ScoringService::domainAverage($tree[0], $responses);
        $this->assertSame(4.0, $avg);
    }

    public function test_answered_then_deactivated(): void
    {
        // Item was answered then deactivated — must not count toward completion.
        $items = [
            self::item('1.1', isActive: true),
            self::item('1.2', isActive: false),
        ];
        $tree = [self::domain('1', [self::subsection($items)])];
        $responses = ['1.1' => 3, '1.2' => 5];

        $completion = ScoringService::completion($tree, $responses);

        // 1 active total, 1 answered active → 100%, NOT 2/1 = 200%.
        $this->assertSame(1, $completion['overall']['total']);
        $this->assertSame(1, $completion['overall']['answered']);
        $this->assertSame(100.0, $completion['overall']['percent']);
    }

    public function test_completion_never_exceeds_100(): void
    {
        // Stress: every active item answered, plus responses to inactive items.
        $items = [
            self::item('1.1', isActive: true),
            self::item('1.2', isActive: true),
            self::item('1.3', isActive: false),
        ];
        $tree = [self::domain('1', [self::subsection($items)])];
        $responses = ['1.1' => 3, '1.2' => 4, '1.3' => 5];

        $completion = ScoringService::completion($tree, $responses);

        $this->assertLessThanOrEqual(100.0, $completion['overall']['percent']);
        $this->assertSame(100.0, $completion['overall']['percent']);

        foreach ($completion['domains'] as $d) {
            $this->assertLessThanOrEqual(100.0, $d['percent']);
            foreach ($d['subsections'] as $s) {
                $this->assertLessThanOrEqual(100.0, $s['percent']);
            }
        }
    }

    #[DataProvider('bandBoundaryProvider')]
    public function test_band_boundaries(float $avg, string $expected): void
    {
        $this->assertSame($expected, ScoringService::band($avg));
    }

    public static function bandBoundaryProvider(): array
    {
        return [
            'below implementing'         => [2.4, 'Exploring'],
            'at implementing threshold'  => [2.5, 'Implementing'],
            'mid implementing'           => [3.9, 'Implementing'],
            'at sustained threshold'     => [4.0, 'Sustained Action'],
            'above sustained threshold'  => [5.0, 'Sustained Action'],
            'very low'                   => [1.0, 'Exploring'],
        ];
    }

    public function test_rounding_before_banding(): void
    {
        // 19 items scored 4 and 1 item scored 3 → raw mean = (19*4 + 3)/20 = 79/20 = 3.95.
        // Rounded to 1 d.p. → 4.0 → band = 'Sustained Action'.
        $items = [];
        $responses = [];
        for ($i = 1; $i <= 20; $i++) {
            $items[] = self::item("1.{$i}");
            $responses["1.{$i}"] = ($i <= 19) ? 4 : 3;
        }
        $tree = [self::domain('1', [self::subsection($items)])];

        $avg = ScoringService::domainAverage($tree[0], $responses);
        $this->assertSame(4.0, $avg);
        $this->assertSame('Sustained Action', ScoringService::band($avg));
    }

    public function test_rounding(): void
    {
        // 3 items scored [1, 2, 3] → raw mean = 2.0 → rounded = 2.0.
        $items = [
            self::item('1.1'), self::item('1.2'), self::item('1.3'),
        ];
        $tree = [self::domain('1', [self::subsection($items)])];
        $responses = ['1.1' => 1, '1.2' => 2, '1.3' => 3];

        $avg = ScoringService::overallAverage($tree, $responses);
        $this->assertSame(2.0, $avg);

        // 3 items scored [1, 1, 2] → raw mean = 1.333... → rounded = 1.3.
        $responses2 = ['1.1' => 1, '1.2' => 1, '1.3' => 2];
        $avg2 = ScoringService::overallAverage($tree, $responses2);
        $this->assertSame(1.3, $avg2);

        // 3 items scored [2, 2, 3] → raw mean = 2.333... → rounded = 2.3.
        $responses3 = ['1.1' => 2, '1.2' => 2, '1.3' => 3];
        $avg3 = ScoringService::overallAverage($tree, $responses3);
        $this->assertSame(2.3, $avg3);
    }

    public function test_band_null_average(): void
    {
        $this->assertSame('Not yet rated', ScoringService::band(null));
    }
}
