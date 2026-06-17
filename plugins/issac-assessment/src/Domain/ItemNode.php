<?php
namespace Issac\Domain;

/**
 * Immutable value object for a single instrument item.
 *
 * Descriptor text exists only at the anchors 1, 3 and 5; scores 2 and 4 are
 * valid midpoints with no descriptor paragraph. The selectable score range
 * (1–5) is enforced elsewhere (response table + REST layer), not here.
 */
final class ItemNode
{
    public function __construct(
        public readonly int $id,
        public readonly string $itemCode,
        public readonly string $label,
        public readonly string $prompt,
        public readonly string $descriptor1,
        public readonly string $descriptor3,
        public readonly string $descriptor5,
        public readonly bool $isActive,
        public readonly int $menuOrder,
        public readonly int $subsectionId
    ) {
    }
}
