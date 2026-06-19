# Background-Driven Colour System Integration

This document explains how to integrate the AHCSA-style background-driven colour system into the Farmscape theme, specifically around:

- `scss/_colours.scss`
- `blocks/block-content-basic.php`

The goal is to let a block background class control the text, heading, and link colours of everything inside that block.

## Current Farmscape Setup

`blocks/block-content-basic.php` already reads an ACF background field:

```php
$content_basic_background = get_sub_field('content_basic_background');
```

It then validates that value against an allowed list:

```php
$allowed_backgrounds = array('bg-olive', 'bg-olive-light', 'bg-humo', 'bg-white');
$background_class = in_array($content_basic_background, $allowed_backgrounds, true) ? $content_basic_background : '';
```

Finally, the selected background class is printed on the outer block wrapper:

```php
<div id="<?php echo esc_attr('content-' . $iBlock); ?>" class="container-fluid px-5 px-md-0 <?php echo esc_attr($background_class); echo esc_attr($content_basic_hero_class); ?>">
```

This means the rendered block may look like:

```html
<div class="container-fluid px-5 px-md-0 bg-olive content-basic-regular">
```

That is already the right place to hook in the colour-variable system.

## How The AHCSA System Works

AHCSA defines global CSS variables first:

```scss
:root {
  --colour-text: #000000;
  --colour-text-inverse: #ffffff;
  --colour-surface: #ffffff;

  --heading-colour: var(--colour-text);
  --text-colour: var(--colour-text);
  --link-colour: var(--colour-primary);
}
```

Then each background class overrides those semantic variables:

```scss
.bg-olive-light {
  --heading-colour: var(--colour-text-inverse);
  --text-colour: var(--colour-surface);
  --link-colour: var(--colour-text-inverse);
}
```

Typography rules consume the semantic variables:

```scss
h1, h2, h3, h4, h5, h6 {
  color: var(--heading-colour);
}

p, ul, ol {
  color: var(--text-colour);
}

a {
  color: var(--link-colour);
}
```

Because CSS custom properties inherit, setting `--heading-colour` on `.bg-olive-light` affects headings inside that block only.

## Recommended Farmscape Integration

Farmscape already uses Sass variables and a `$colors` map in `scss/_colours.scss`. Keep that system. Add CSS custom properties alongside it so blocks can inherit semantic colours.

Add a root variable section after the colour variables or after the `$colors` map:

```scss
:root {
    --colour-olive: #{$olive};
    --colour-olive-light: #{$olive-light};
    --colour-humo: #{$humo};
    --colour-white: #{$blanco};
    --colour-text: #{$tinta};
    --colour-text-inverse: #{$blanco};

    --heading-colour: var(--colour-text);
    --text-colour: var(--colour-text);
    --link-colour: var(--colour-olive);
}
```

Then update the existing background generation loop so the generated `.bg-*` classes can also participate in the semantic colour system:

```scss
@each $name, $value in $colors {
    .#{$name},
    .text-#{$name} {
        color: $value;
    }

    .bg-#{$name} {
        background-color: $value;
    }

    .border-#{$name} {
        border-color: $value;
    }
}
```

Keep that generator, then add explicit contrast rules underneath it for backgrounds used by `block-content-basic.php`:

```scss
.bg-olive,
.bg-olive-light {
    --heading-colour: var(--colour-text-inverse);
    --text-colour: var(--colour-text-inverse);
    --link-colour: var(--colour-text-inverse);
}

.bg-humo,
.bg-white,
.bg-blanco {
    --heading-colour: var(--colour-text);
    --text-colour: var(--colour-text);
    --link-colour: var(--colour-olive);
}
```

Then add scoped typography rules. Prefer scoping them to content blocks first, instead of changing every heading/link across the whole site at once:

```scss
.content-basic-regular,
.content-basic-hero {
    color: var(--text-colour);

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        color: var(--heading-colour);
    }

    p,
    ul,
    ol {
        color: var(--text-colour);
    }

    a:not(.btn):not(.btn-cta) {
        color: var(--link-colour);
    }
}
```

This keeps the change focused on `block-content-basic.php` and avoids unexpectedly changing menus, buttons, cards, or other shared components.

## PHP Changes Needed

No major PHP rewrite is needed for `blocks/block-content-basic.php`, because it already prints the background class on the block wrapper.

This current line is the key integration point:

```php
<div id="<?php echo esc_attr('content-' . $iBlock); ?>" class="container-fluid px-5 px-md-0 <?php echo esc_attr($background_class); echo esc_attr($content_basic_hero_class); ?>">
```

The selected ACF value, such as `bg-olive`, becomes the CSS scope that overrides:

- `--heading-colour`
- `--text-colour`
- `--link-colour`

If more background options are added in ACF, also add them to:

```php
$allowed_backgrounds = array('bg-olive', 'bg-olive-light', 'bg-humo', 'bg-white');
```

And add matching contrast rules in `scss/_colours.scss`.

## Example Flow

If ACF returns `bg-olive`, the PHP outputs:

```html
<div class="container-fluid px-5 px-md-0 bg-olive content-basic-regular">
```

The SCSS rule for `.bg-olive` sets:

```scss
--heading-colour: var(--colour-text-inverse);
--text-colour: var(--colour-text-inverse);
--link-colour: var(--colour-text-inverse);
```

Then `.content-basic-regular h2`, paragraphs, lists, and normal links inherit white text for contrast on the green background.

If ACF returns `bg-humo`, the SCSS sets dark text instead:

```scss
--heading-colour: var(--colour-text);
--text-colour: var(--colour-text);
--link-colour: var(--colour-olive);
```

## Suggested Implementation Order

1. Add `:root` CSS custom properties to `scss/_colours.scss`.
2. Keep the existing Sass `$colors` map and generated utility classes.
3. Add explicit semantic colour overrides for `bg-olive`, `bg-olive-light`, `bg-humo`, and `bg-white`.
4. Add scoped typography rules for `.content-basic-regular` and `.content-basic-hero`.
5. Only update `blocks/block-content-basic.php` if new ACF background values need to be added to `$allowed_backgrounds`.
6. Let CodeKit compile the SCSS and then visually check each background option in the CMS.

## Notes

Do not replace the existing Sass colour map. The custom properties should sit alongside it:

- Sass variables remain useful for compile-time styling.
- CSS custom properties are useful for inherited, context-aware colours.

This gives the theme both systems: generated utility classes from Sass and flexible block-level text contrast from CSS variables.
