<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 * 
 * Sets up theme defaults and registers support for various WordPress features.
 */
function bystra_theme_setup() {
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add theme support for HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for custom logo
    add_theme_support('custom-logo');
    
    // Add theme support for responsive embeds
    add_theme_support('responsive-embeds');
    
    // Register navigation menus
    register_nav_menus(array(
        'main-menu' => __('Main menu', 'bystra') ,
        'super-menu' => __('Super menu', 'bystra') ,
        'footer-menu' => __('Footer menu', 'bystra') ,
        'privacy-menu' => __('Privacy menu', 'bystra') ,
        'surgical-services-menu' => __('Surgical services menu', 'bystra') ,
    ));
}
add_action('after_setup_theme', 'bystra_theme_setup');

/**
 * Enqueue Theme Scripts and Styles
 * 
 * Properly enqueues all CSS and JavaScript files for the theme.
 */
function add_theme_scripts() {
    /* CSS Styles */
    wp_enqueue_style('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css', array(), '5.3.3');
    wp_enqueue_style('swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.css', array(), '11.0.5');
    wp_enqueue_style('slickcss', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css', array(), '1.9.0');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css', array(), '6.2.0');
    wp_enqueue_style('animatecss', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1');

    wp_enqueue_style('adobe-fonts', 'https://use.typekit.net/rif1jjc.css', array(), null);
    
    wp_enqueue_style('lightgallerycss', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css', array(), '2.7.2');
    wp_enqueue_style('lightgalleryAutoplay', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-autoplay.min.css', array(), '2.7.2');

    // Theme's main stylesheet (loaded last to allow overrides)
    wp_enqueue_style('bystra-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
    
    /* JavaScript Files */
    // Version numbers are added to each enqueued file for cache busting.
    // The 'true' parameter is used to load JavaScript files in the footer.
    // Dependencies are specified for scripts that rely on other scripts.
    
    // Replace WordPress jQuery with CDN version
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0', true);
    
    // Bootstrap and related scripts
    wp_enqueue_script('popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js', array('jquery'), '2.9.3', true);
    wp_enqueue_script('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js', array('jquery', 'popper'), '5.3.3', true);
    
    // Slider and carousel scripts
    wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array('jquery'), '1.9.0', true);
    wp_enqueue_script('swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js', array(), '11.0.5', true);
    
    // Utility scripts
    wp_enqueue_script('matchHeight', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js', array('jquery'), '0.7.2', true);
    
    // Google Maps API
    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCGl9_mfrwvgesQtcuilMcOap3qUYqpYWs', array(), null, true);
    
    // Lightgallery and plugins
    wp_enqueue_script('lightgallery', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js', array('jquery'), '2.7.2', true);
    wp_enqueue_script('lightgallerypluginthumbnails', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.min.js', array('lightgallery'), '2.7.2', true);
    wp_enqueue_script('lightgallerypluginzoom', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.min.js', array('lightgallery'), '2.7.2', true);
    wp_enqueue_script('lightgallerypluginautoplay', 'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/autoplay/lg-autoplay.umd.min.js', array('lightgallery'), '2.7.2', true);

    // GSAP Animation library
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js', array(), '3.11.5', true);
    wp_enqueue_script('gsapScrollTrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/ScrollTrigger.min.js', array('gsap'), '3.11.5', true);

    // Theme's main JavaScript file
    wp_enqueue_script('bystra-main', get_template_directory_uri() . '/js/main.js', array('jquery', 'gsap'), wp_get_theme()->get('Version'), true);
    
    // Localize script for AJAX and other data
    wp_localize_script('bystra-main', 'bystra_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bystra_nonce'),
        'theme_url' => get_template_directory_uri(),
    ));
    
    
    // Get the ACF field value
    $iframe_code = get_field('google_map_iframe','options'); // Adjust 'google_map_iframe' to your ACF field name
    // Localize the script with the iframe code
    wp_localize_script('bystra-main', 'acfData', array(
        'iframe_code' => $iframe_code,
    ));
    

}
add_action('wp_enqueue_scripts', 'add_theme_scripts');




/*

███    ██  █████  ██    ██ ██     ██  █████  ██      ██   ██ ███████ ██████
████   ██ ██   ██ ██    ██ ██     ██ ██   ██ ██      ██  ██  ██      ██   ██
██ ██  ██ ███████ ██    ██ ██  █  ██ ███████ ██      █████   █████   ██████
██  ██ ██ ██   ██  ██  ██  ██ ███ ██ ██   ██ ██      ██  ██  ██      ██   ██
██   ████ ██   ██   ████    ███ ███  ██   ██ ███████ ██   ██ ███████ ██   ██


Register Custom Navigation Walker
*/

if ( ! file_exists( get_template_directory() . '/bs533-navwalker.php' ) ) {
    // Log an error or display an admin notice, but do not return.
    error_log( 'Error: bs533-navwalker.php file is missing in the theme directory.' );
    // Optionally, you can display an admin notice.
    if ( is_admin() ) {
        function bs533_navwalker_missing_notice() {
            echo '<div class="error"><p>';
            _e( 'Warning: The bs533-navwalker.php file is missing in the theme directory.', 'your-theme-text-domain' );
            echo '</p></div>';
        }
        add_action( 'admin_notices', 'bs533_navwalker_missing_notice' );
    }
    // Do not use return; let the script continue.
} else {
    require_once get_template_directory() . '/bs533-navwalker.php';
}


/*
███████ ██    ██ ███    ██  ██████ ████████ ██  ██████  ███    ██
██      ██    ██ ████   ██ ██         ██    ██ ██    ██ ████   ██
█████   ██    ██ ██ ██  ██ ██         ██    ██ ██    ██ ██ ██  ██
██      ██    ██ ██  ██ ██ ██         ██    ██ ██    ██ ██  ██ ██
██       ██████  ██   ████  ██████    ██    ██  ██████  ██   ████


██████  ███████ ███████  ██████  ██    ██ ██████   ██████ ███████ ███████
██   ██ ██      ██      ██    ██ ██    ██ ██   ██ ██      ██      ██
██████  █████   ███████ ██    ██ ██    ██ ██████  ██      █████   ███████
██   ██ ██           ██ ██    ██ ██    ██ ██   ██ ██      ██           ██
██   ██ ███████ ███████  ██████   ██████  ██   ██  ██████ ███████ ███████


*/
// Loads display_resources(), the shared ACF file/link resource repeater renderer.
// Used by: blocks/block-accordion.php, blocks/block-document-resources.php, blocks/block-content-basic.php, and blocks/block-cards-downloads.php.
require_once get_template_directory() . '/inc/component-resources.php';
require_once get_template_directory() . '/inc/component-backgrounds.php';



/*
 ██████  ██    ██ ████████ ███████ ███    ██ ██████  ███████ ██████   ██████
██       ██    ██    ██    ██      ████   ██ ██   ██ ██      ██   ██ ██
██   ███ ██    ██    ██    █████   ██ ██  ██ ██████  █████   ██████  ██   ███
██    ██ ██    ██    ██    ██      ██  ██ ██ ██   ██ ██      ██   ██ ██    ██
 ██████   ██████     ██    ███████ ██   ████ ██████  ███████ ██   ██  ██████


 ██████  ██    ██ ████████
██    ██ ██    ██    ██
██    ██ ██    ██    ██
██    ██ ██    ██    ██
 ██████   ██████     ██


*/

add_filter('use_block_editor_for_post', '__return_false');


function remove_default_post_content_editor() {
    remove_post_type_support('post', 'editor');
    remove_post_type_support('page', 'editor');
    //remove_post_type_support('store', 'editor');
}
add_action('init', 'remove_default_post_content_editor');




/*
 █████   ██████ ███████
██   ██ ██      ██
███████ ██      █████
██   ██ ██      ██
██   ██  ██████ ██


 ██████  ██████  ██      ██       █████  ██████  ███████ ███████
██      ██    ██ ██      ██      ██   ██ ██   ██ ██      ██
██      ██    ██ ██      ██      ███████ ██████  ███████ █████
██      ██    ██ ██      ██      ██   ██ ██           ██ ██
 ██████  ██████  ███████ ███████ ██   ██ ██      ███████ ███████


*/
function collapse_acf_flexible_content() {
    ?>
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        // Collapse all flexible content layouts
        $('.acf-flexible-content .layout').addClass('-collapsed');

        // Optionally, you can add a button to toggle collapse/expand all layouts
        $('.acf-hndle').after('<button id="toggle-layouts" type="button">Toggle Layouts</button>');

        $('#toggle-layouts').on('click', function() {
            $('.acf-flexible-content .layout').toggleClass('-collapsed');
        });
    });
})(jQuery);
</script>
<?php
}
add_action('acf/input/admin_footer', 'collapse_acf_flexible_content');








/**
 * Custom Excerpt Length
 */
function bystra_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'bystra_excerpt_length');

/**
 * Custom Excerpt More
 */
function bystra_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'bystra_excerpt_more');

/**
 * Add custom image sizes
 */
function bystra_image_sizes() {
    add_image_size('1080p', 1920, 1080, array(
        'center',
        'center'
    ));
    add_image_size('720p', 1280, 720, array(
        'center',
        'center'
    ));
    add_image_size('5-7r686', 686, 960, array(
        'center',
        'center'
    ));

        add_image_size('7-5r960', 960, 686, array(
        'center',
        'center'
    ));
    add_image_size('16-9r720', 720, 405, array(
        'center',
        'center'
    ));
    add_image_size('25-41r576', 576, 945, array(
        'center',
        'center'
    ));
    add_image_size('576sm', 576, 576, array(
        'center',
        'center'
    ));
    add_image_size('3-4r576', 432, 576, array(
        'center',
        'center'
    ));
    add_image_size('4-3r576', 576, 432, array(
        'center',
        'center'
    ));
}
add_action('after_setup_theme', 'bystra_image_sizes');


















/**
 * Remove unnecessary WordPress features for better performance
 */
function bystra_cleanup() {
    // Remove WordPress version from head
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove wlwmanifest.xml
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Remove feed links
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
}
add_action('init', 'bystra_cleanup');

/**
 * Disable WordPress emojis
 */
function bystra_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'bystra_disable_emojis');






/*
████████ ███████  █████  ███    ███
   ██    ██      ██   ██ ████  ████
   ██    █████   ███████ ██ ████ ██
   ██    ██      ██   ██ ██  ██  ██
   ██    ███████ ██   ██ ██      ██


 ██████ ██████  ████████
██      ██   ██    ██
██      ██████     ██
██      ██         ██
 ██████ ██         ██


*/

/**
 * Register Team Custom Post Type
 */
function bystra_register_team_post_type() {
    $labels = array(
        'name'                  => _x('Team', 'Post type general name', 'bystra'),
        'singular_name'         => _x('Team Member', 'Post type singular name', 'bystra'),
        'menu_name'             => _x('Team', 'Admin Menu text', 'bystra'),
        'name_admin_bar'        => _x('Team Member', 'Add New on Toolbar', 'bystra'),
        'add_new'               => __('Add New', 'bystra'),
        'add_new_item'          => __('Add New Team Member', 'bystra'),
        'new_item'              => __('New Team Member', 'bystra'),
        'edit_item'             => __('Edit Team Member', 'bystra'),
        'view_item'             => __('View Team Member', 'bystra'),
        'all_items'             => __('All Team Members', 'bystra'),
        'search_items'          => __('Search Team Members', 'bystra'),
        'parent_item_colon'     => __('Parent Team Member:', 'bystra'),
        'not_found'             => __('No team members found.', 'bystra'),
        'not_found_in_trash'    => __('No team members found in Trash.', 'bystra'),
        'archives'              => _x('Team archives', 'The post type archive label', 'bystra'),
        'insert_into_item'      => _x('Insert into team member', 'Overrides the "Insert into post" phrase', 'bystra'),
        'uploaded_to_this_item' => _x('Uploaded to this team member', 'Overrides the "Uploaded to this post" phrase', 'bystra'),
        'filter_items_list'     => _x('Filter team members list', 'Screen reader text', 'bystra'),
        'items_list_navigation' => _x('Team members list navigation', 'Screen reader text', 'bystra'),
        'items_list'            => _x('Team members list', 'Screen reader text', 'bystra'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'team-member'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array('title'),
        'show_in_rest'       => true,
    );

    register_post_type('team', $args);
}
add_action('init', 'bystra_register_team_post_type');


/*
████████ ███████ ███████ ████████ ██ ███    ███  ██████  ███    ██ ██  █████  ██      ███████
   ██    ██      ██         ██    ██ ████  ████ ██    ██ ████   ██ ██ ██   ██ ██      ██
   ██    █████   ███████    ██    ██ ██ ████ ██ ██    ██ ██ ██  ██ ██ ███████ ██      ███████
   ██    ██           ██    ██    ██ ██  ██  ██ ██    ██ ██  ██ ██ ██ ██   ██ ██           ██
   ██    ███████ ███████    ██    ██ ██      ██  ██████  ██   ████ ██ ██   ██ ███████ ███████

 */
function bystra_register_testimonials_post_type() {
    $labels = array(
        'name'                  => _x('Testimonials', 'Post type general name', 'bystra'),
        'singular_name'         => _x('Testimonial', 'Post type singular name', 'bystra'),
        'menu_name'             => _x('Testimonials', 'Admin Menu text', 'bystra'),
        'name_admin_bar'        => _x('Testimonial', 'Add New on Toolbar', 'bystra'),
        'add_new'               => __('Add New', 'bystra'),
        'add_new_item'          => __('Add New Testimonial', 'bystra'),
        'new_item'              => __('New Testimonial', 'bystra'),
        'edit_item'             => __('Edit Testimonial', 'bystra'),
        'view_item'             => __('View Testimonial', 'bystra'),
        'all_items'             => __('All Testimonials', 'bystra'),
        'search_items'          => __('Search Testimonials', 'bystra'),
        'parent_item_colon'     => __('Parent Testimonial:', 'bystra'),
        'not_found'             => __('No testimonials found.', 'bystra'),
        'not_found_in_trash'    => __('No testimonials found in Trash.', 'bystra'),
        'archives'              => _x('Testimonials archives', 'The post type archive label', 'bystra'),
        'insert_into_item'      => _x('Insert into testimonial', 'Overrides the "Insert into post" phrase', 'bystra'),
        'uploaded_to_this_item' => _x('Uploaded to this testimonial', 'Overrides the "Uploaded to this post" phrase', 'bystra'),
        'filter_items_list'     => _x('Filter testimonials list', 'Screen reader text', 'bystra'),
        'items_list_navigation' => _x('Testimonials list navigation', 'Screen reader text', 'bystra'),
        'items_list'            => _x('Testimonials list', 'Screen reader text', 'bystra'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'testimonials'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-format-quote',
        'supports'           => array('title'),
        'taxonomies'         => array('segment'),
        'show_in_rest'       => true,
    );

    register_post_type('testimonials', $args);
}
add_action('init', 'bystra_register_testimonials_post_type');

/**
 * Register Segment Taxonomy for Testimonials
 */
function bystra_register_segment_taxonomy() {
    $labels = array(
        'name'              => _x('Segments', 'Taxonomy general name', 'bystra'),
        'singular_name'     => _x('Segment', 'Taxonomy singular name', 'bystra'),
        'search_items'      => __('Search Segments', 'bystra'),
        'all_items'         => __('All Segments', 'bystra'),
        'parent_item'       => __('Parent Segment', 'bystra'),
        'parent_item_colon' => __('Parent Segment:', 'bystra'),
        'edit_item'         => __('Edit Segment', 'bystra'),
        'update_item'       => __('Update Segment', 'bystra'),
        'add_new_item'      => __('Add New Segment', 'bystra'),
        'new_item_name'     => __('New Segment Name', 'bystra'),
        'menu_name'         => __('Segments', 'bystra'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'segment'),
        'show_in_rest'      => true,
    );

    register_taxonomy('segment', array('testimonials'), $args);
}
add_action('init', 'bystra_register_segment_taxonomy');





/*
 █████   ██████ ███████
██   ██ ██      ██
███████ ██      █████
██   ██ ██      ██
██   ██  ██████ ██


██       █████  ██    ██  ██████  ██    ██ ████████ ███████
██      ██   ██  ██  ██  ██    ██ ██    ██    ██    ██
██      ███████   ████   ██    ██ ██    ██    ██    ███████
██      ██   ██    ██    ██    ██ ██    ██    ██         ██
███████ ██   ██    ██     ██████   ██████     ██    ███████


*/

/**
 * Append content_basic_headline to the "Content Basic" flexible layout title
 * so collapsed rows are easier to navigate.
 */
add_filter('acf/fields/flexible_content/layout_title/name=flexible_content', function ($title, $field, $layout, $i) {

    // Only affect the "content_basic" layout
    if ($layout['name'] !== 'content_basic') {
        return $title;
    }

    // Pull the headline sub field from this row
    $headline = get_sub_field('content_basic_headline');

    if (!empty($headline)) {
        $title .= ' <span style="opacity:.7">— ' . esc_html($headline) . '</span>';
    }

    return $title;

}, 10, 4);