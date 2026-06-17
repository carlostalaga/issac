<?php
namespace Issac\Domain;

/**
 * Immutable value object for a subsection and its ordered items.
 */
final class SubsectionNode
{
    /**
     * @param ItemNode[] $items Ordered by menu_order.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly int $menuOrder,
        public readonly int $domainId,
        public readonly array $items
    ) {
    }
}
