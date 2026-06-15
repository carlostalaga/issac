<?php
namespace Issac\Domain;

defined('ABSPATH') || exit;

/**
 * Immutable value object for a domain and its ordered subsections.
 */
final class DomainNode
{
    /**
     * @param SubsectionNode[] $subsections Ordered by menu_order.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $title,
        public readonly string $description,
        public readonly int $menuOrder,
        public readonly array $subsections
    ) {
    }
}
