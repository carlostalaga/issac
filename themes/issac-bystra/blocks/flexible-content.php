<?php
// Check value exists. test pull!!`¡ !
if( have_rows('flexible_content') ):
    $iBlock = 0;

    // Loop through rows.
    while ( have_rows('flexible_content') ) : the_row();

        if( get_row_layout() == 'accordion' ):
            include get_theme_file_path('/blocks/block-accordion.php');
        elseif( get_row_layout() == 'banner' ):
            include get_theme_file_path('/blocks/block-banner.php');
        elseif( get_row_layout() == 'breadcrumbs' ):
            include get_theme_file_path('/blocks/block-breadcrumbs.php');
        elseif( get_row_layout() == 'cards' ):
            include get_theme_file_path('/blocks/block-cards.php');
        elseif( get_row_layout() == 'contact' ):
            include get_theme_file_path('/blocks/block-contact.php');
        elseif( get_row_layout() == 'content_basic' ):
            include get_theme_file_path('/blocks/block-content-basic.php');
        elseif( get_row_layout() == 'document_resources' ):
            include get_theme_file_path('/blocks/block-document-resources.php');
        elseif( get_row_layout() == 'embed_media' ):
            include get_theme_file_path('/blocks/block-embed-media.php');
        elseif( get_row_layout() == 'form' ):
            include get_theme_file_path('/blocks/block-form.php');
        elseif( get_row_layout() == 'gallery' ):
            include get_theme_file_path('/blocks/block-gallery.php');
        elseif( get_row_layout() == 'hero' ):
            include get_theme_file_path('/blocks/block-hero.php');
        elseif( get_row_layout() == 'locations' ):
            include get_theme_file_path('/blocks/block-locations.php');
        elseif( get_row_layout() == 'quote' ):
            include get_theme_file_path('/blocks/block-quote.php');
        elseif( get_row_layout() == 'services' ):
            include get_theme_file_path('/blocks/block-services.php');
        elseif( get_row_layout() == 'slider' ):
            include get_theme_file_path('/blocks/block-slider.php');
        elseif( get_row_layout() == 'slider_cards' ):
        elseif( get_row_layout() == 'slider_secondary' ):
            include get_theme_file_path('/blocks/block-slider-secondary.php');
            include get_theme_file_path('/blocks/block-slider-cards.php');
        elseif( get_row_layout() == 'slider_lightgallery' ):
            include get_theme_file_path('/blocks/block-slider-lightgallery.php');
        elseif( get_row_layout() == 'slider_tabs' ):
            include get_theme_file_path('/blocks/block-slider-tabs.php');
        elseif( get_row_layout() == 'spacer' ):
            include get_theme_file_path('/blocks/block-spacer.php');
        elseif( get_row_layout() == 'tabs' ):
            include get_theme_file_path('/blocks/block-tabs.php');
        elseif( get_row_layout() == 'team' ):
            include get_theme_file_path('/blocks/block-team.php');
        elseif( get_row_layout() == 'testimonials' ):
            include get_theme_file_path('/blocks/block-testimonials.php');

// Ends the last case if/elseif/
        endif;
// End C A S E S
        $iBlock++;
// End loop.
    endwhile;
// No value.
else :
// Do something...
endif;
?>