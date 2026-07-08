<?php

namespace Issac\Admin;

use Issac\Domain\InstrumentRepository;
use Issac\Domain\ResponseRepository;
use Issac\Domain\ScoringService;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WP_List_Table showing all users who have an ISSAC assessment.
 */
final class UsersListTable extends \WP_List_Table
{
    /** @var \Issac\Domain\DomainNode[] */
    private array $tree;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'issac-user',
            'plural'   => 'issac-users',
            'ajax'     => false,
        ]);

        $this->tree = InstrumentRepository::tree();
    }

    /** @return array<string, string> */
    public function get_columns(): array
    {
        $columns = [
            'user'          => __('User', 'issac-assessment'),
            'started'       => __('Started', 'issac-assessment'),
            'last_activity' => __('Last Activity', 'issac-assessment'),
            'overall'       => __('Overall %', 'issac-assessment'),
        ];

        foreach ($this->tree as $domain) {
            $columns['domain_' . $domain->code] = sprintf(
                __('D%s', 'issac-assessment'),
                esc_html($domain->code)
            );
        }

        $columns['status'] = __('Status', 'issac-assessment');

        return $columns;
    }

    /** @return array<string, array{string, bool}> */
    public function get_sortable_columns(): array
    {
        return [
            'user'          => ['user', false],
            'started'       => ['started', false],
            'last_activity' => ['last_activity', true],
            'overall'       => ['overall', false],
            'status'        => ['status', false],
        ];
    }

    public function prepare_items(): void
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $perPage     = 20;
        $currentPage = $this->get_pagenum();

        global $wpdb;
        $assessments = $wpdb->prefix . 'issac_assessments';
        $users       = $wpdb->users;

        $search   = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        $orderby  = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'last_activity';
        $order    = isset($_REQUEST['order']) && strtoupper($_REQUEST['order']) === 'ASC' ? 'ASC' : 'DESC';

        $orderClause = match ($orderby) {
            'user'    => "u.display_name {$order}",
            'started' => "a.started_at {$order}",
            'overall' => "overall_pct {$order}",
            'status'  => "a.status {$order}",
            default   => "a.updated_at {$order}",
        };

        // Count active items for overall % computation in SQL
        $totalActiveItems = 0;
        foreach ($this->tree as $domain) {
            foreach ($domain->subsections as $sub) {
                foreach ($sub->items as $item) {
                    if ($item->isActive) {
                        $totalActiveItems++;
                    }
                }
            }
        }
        $totalActiveItems = max($totalActiveItems, 1);

        $responses = $wpdb->prefix . 'issac_responses';

        $where = '';
        $params = [];
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where = "AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
            $params[] = $like;
            $params[] = $like;
        }

        // Total items for pagination
        $countSql = "SELECT COUNT(*)
            FROM {$assessments} a
            JOIN {$users} u ON u.ID = a.user_id
            WHERE 1=1 {$where}";

        $totalItems = (int) ($params
            ? $wpdb->get_var($wpdb->prepare($countSql, ...$params))
            : $wpdb->get_var($countSql)
        );

        // Main query with overall % subquery for sorting
        $sql = "SELECT a.*, u.display_name, u.user_email,
                ROUND(
                    (SELECT COUNT(*) FROM {$responses} r WHERE r.assessment_id = a.id)
                    / %d * 100, 1
                ) AS overall_pct
            FROM {$assessments} a
            JOIN {$users} u ON u.ID = a.user_id
            WHERE 1=1 {$where}
            ORDER BY {$orderClause}
            LIMIT %d OFFSET %d";

        $queryParams = array_merge(
            [$totalActiveItems],
            $params,
            [$perPage, ($currentPage - 1) * $perPage]
        );

        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$queryParams));

        // Compute per-row summaries for domain columns
        $this->items = [];
        foreach ($rows as $row) {
            $responses = ResponseRepository::forAssessment((int) $row->id);
            $summary   = ScoringService::summary($this->tree, $responses);

            $row->_summary    = $summary;
            $row->overall_pct = (float) ($row->overall_pct ?? 0);
            $this->items[]    = $row;
        }

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($totalItems / $perPage),
        ]);
    }

    /** @param object $item */
    public function column_default($item, $columnName): string
    {
        if (str_starts_with($columnName, 'domain_')) {
            $code = substr($columnName, 7);
            foreach ($item->_summary['domains'] as $d) {
                if ($d['code'] === $code) {
                    return esc_html(number_format($d['completion'], 1)) . '%';
                }
            }
            return '0.0%';
        }

        return '';
    }

    /** @param object $item */
    public function column_user($item): string
    {
        $url  = admin_url('admin.php?page=issac-user-detail&assessment_id=' . absint($item->id));
        $name = esc_html($item->display_name);
        return sprintf('<a href="%s"><strong>%s</strong></a>', esc_url($url), $name);
    }

    /** @param object $item */
    public function column_started($item): string
    {
        return esc_html(wp_date(get_option('date_format'), strtotime($item->started_at)));
    }

    /** @param object $item */
    public function column_last_activity($item): string
    {
        return esc_html(wp_date(get_option('date_format'), strtotime($item->updated_at)));
    }

    /** @param object $item */
    public function column_overall($item): string
    {
        $pct = $item->_summary['overall']['completion'] ?? 0;
        return esc_html(number_format((float) $pct, 1)) . '%';
    }

    /** @param object $item */
    public function column_status($item): string
    {
        $label = $item->status === 'completed'
            ? __('Completed', 'issac-assessment')
            : __('In Progress', 'issac-assessment');

        $class = $item->status === 'completed' ? 'color:#00a32a' : 'color:#d63638';

        return sprintf('<span style="%s">%s</span>', esc_attr($class), esc_html($label));
    }
}
