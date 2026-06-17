<?php
namespace Issac\Domain;

/**
 * Pure PHP scoring logic — no WordPress functions, unit-testable without WP.
 *
 * THE single source of truth for all completion, average, and band calculations.
 * Dashboard, domain pages, PDF, and admin all consume this service; nothing else
 * in the codebase computes these numbers.
 *
 * Key rules:
 *  - Averages over answered active items only; unanswered ≠ 0.
 *  - Inactive items excluded from denominators and averages.
 *  - Existing responses to retired items preserved, never dropped.
 *  - Division by zero → null average, 0% completion.
 *  - Rounding (1 d.p.) happens once, before banding.
 */
final class ScoringService
{
    public const BAND_EXPLORING        = 'Exploring';
    public const BAND_IMPLEMENTING     = 'Implementing';
    public const BAND_SUSTAINED_ACTION = 'Sustained Action';
    public const BAND_NOT_RATED        = 'Not yet rated';

    private const THRESHOLD_IMPLEMENTING     = 2.5;
    private const THRESHOLD_SUSTAINED_ACTION = 4.0;

    /**
     * Overall and per-domain/per-subsection completion breakdown.
     *
     * @param DomainNode[] $tree
     * @param array<string, int> $responses item_code => score
     */
    public static function completion(array $tree, array $responses): array
    {
        $overallAnswered = 0;
        $overallTotal    = 0;
        $domains         = [];

        foreach ($tree as $domain) {
            $domainAnswered = 0;
            $domainTotal    = 0;
            $subsections    = [];

            foreach ($domain->subsections as $subsection) {
                $subAnswered = 0;
                $subTotal    = 0;

                foreach ($subsection->items as $item) {
                    if (!$item->isActive) {
                        continue;
                    }
                    $subTotal++;
                    if (isset($responses[$item->itemCode])) {
                        $subAnswered++;
                    }
                }

                $subsections[] = [
                    'title'    => $subsection->title,
                    'answered' => $subAnswered,
                    'total'    => $subTotal,
                    'percent'  => self::safePercent($subAnswered, $subTotal),
                ];

                $domainAnswered += $subAnswered;
                $domainTotal    += $subTotal;
            }

            $domains[] = [
                'code'        => $domain->code,
                'title'       => $domain->title,
                'answered'    => $domainAnswered,
                'total'       => $domainTotal,
                'percent'     => self::safePercent($domainAnswered, $domainTotal),
                'subsections' => $subsections,
            ];

            $overallAnswered += $domainAnswered;
            $overallTotal    += $domainTotal;
        }

        return [
            'overall' => [
                'answered' => $overallAnswered,
                'total'    => $overallTotal,
                'percent'  => self::safePercent($overallAnswered, $overallTotal),
            ],
            'domains' => $domains,
        ];
    }

    /**
     * Mean of answered active items in a domain, rounded to 1 d.p.
     * Returns null if zero items answered.
     *
     * @param array<string, int> $responses item_code => score
     */
    public static function domainAverage(DomainNode $domain, array $responses): ?float
    {
        $sum   = 0;
        $count = 0;

        foreach ($domain->subsections as $subsection) {
            foreach ($subsection->items as $item) {
                if (!$item->isActive) {
                    continue;
                }
                if (isset($responses[$item->itemCode])) {
                    $sum += $responses[$item->itemCode];
                    $count++;
                }
            }
        }

        return $count > 0 ? self::roundAverage($sum / $count) : null;
    }

    /**
     * Mean of answered active items in a subsection, rounded to 1 d.p.
     *
     * @param array<string, int> $responses item_code => score
     */
    public static function subsectionAverage(SubsectionNode $subsection, array $responses): ?float
    {
        $sum   = 0;
        $count = 0;

        foreach ($subsection->items as $item) {
            if (!$item->isActive) {
                continue;
            }
            if (isset($responses[$item->itemCode])) {
                $sum += $responses[$item->itemCode];
                $count++;
            }
        }

        return $count > 0 ? self::roundAverage($sum / $count) : null;
    }

    /**
     * Mean across all domains, rounded to 1 d.p.
     *
     * @param DomainNode[] $tree
     * @param array<string, int> $responses item_code => score
     */
    public static function overallAverage(array $tree, array $responses): ?float
    {
        $sum   = 0;
        $count = 0;

        foreach ($tree as $domain) {
            foreach ($domain->subsections as $subsection) {
                foreach ($subsection->items as $item) {
                    if (!$item->isActive) {
                        continue;
                    }
                    if (isset($responses[$item->itemCode])) {
                        $sum += $responses[$item->itemCode];
                        $count++;
                    }
                }
            }
        }

        return $count > 0 ? self::roundAverage($sum / $count) : null;
    }

    /**
     * Returns the band label for a (pre-rounded) average.
     */
    public static function band(?float $average): string
    {
        if ($average === null) {
            return self::BAND_NOT_RATED;
        }

        if ($average < self::THRESHOLD_IMPLEMENTING) {
            return self::BAND_EXPLORING;
        }

        if ($average < self::THRESHOLD_SUSTAINED_ACTION) {
            return self::BAND_IMPLEMENTING;
        }

        return self::BAND_SUSTAINED_ACTION;
    }

    /**
     * Full summary consumed by dashboard, PDF, and admin.
     *
     * @param DomainNode[] $tree
     * @param array<string, int> $responses item_code => score
     */
    public static function summary(array $tree, array $responses): array
    {
        $completion = self::completion($tree, $responses);
        $overallAvg = self::overallAverage($tree, $responses);

        $domains = [];
        foreach ($tree as $i => $domain) {
            $domainAvg     = self::domainAverage($domain, $responses);
            $domainCompl   = $completion['domains'][$i];

            $subsections = [];
            foreach ($domain->subsections as $j => $subsection) {
                $subAvg = self::subsectionAverage($subsection, $responses);
                $subCompl = $domainCompl['subsections'][$j];

                $subsections[] = [
                    'title'    => $subsection->title,
                    'average'  => $subAvg,
                    'band'     => self::band($subAvg),
                    'answered' => $subCompl['answered'],
                    'total'    => $subCompl['total'],
                ];
            }

            $domains[] = [
                'code'        => $domain->code,
                'title'       => $domain->title,
                'completion'  => $domainCompl['percent'],
                'average'     => $domainAvg,
                'band'        => self::band($domainAvg),
                'answered'    => $domainCompl['answered'],
                'total'       => $domainCompl['total'],
                'subsections' => $subsections,
            ];
        }

        return [
            'overall' => [
                'completion' => $completion['overall']['percent'],
                'average'    => $overallAvg,
                'band'       => self::band($overallAvg),
                'answered'   => $completion['overall']['answered'],
                'total'      => $completion['overall']['total'],
            ],
            'domains' => $domains,
        ];
    }

    /**
     * Single rounding point — all averages pass through here.
     */
    private static function roundAverage(float $value): float
    {
        return round($value, 1);
    }

    private static function safePercent(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
