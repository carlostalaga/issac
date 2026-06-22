<?php
namespace Issac\Tests\Unit;

use Issac\Frontend\Shortcodes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DashboardHelpersTest extends TestCase
{
    #[DataProvider('ctaLabelProvider')]
    public function test_domain_cta_label(float $completion, string $expected): void
    {
        $this->assertSame($expected, Shortcodes::domainCtaLabel($completion));
    }

    public static function ctaLabelProvider(): array
    {
        return [
            'zero completion'       => [0.0, 'Start'],
            'partial low'           => [0.1, 'Resume'],
            'partial mid'           => [50.0, 'Resume'],
            'partial high'          => [99.9, 'Resume'],
            'full completion'       => [100.0, 'Review'],
        ];
    }
}
