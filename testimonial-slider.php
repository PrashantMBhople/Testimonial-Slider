<?php
/*
Plugin Name: Testimonial Slider
Plugin URI: https://example.com/
Description: Adds a custom post type for Testimonials and displays them in a slider via shortcode.
Version: 1.0
Author: Prashant Bhople
Author URI: https://example.com/
License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Code to Register Testimonial Custom Post Type
function ts_register_testimonial_cpt() {
    $labels = array(
        'name' => 'Testimonials',
        'singular_name' => 'Testimonial',
        'menu_name' => 'Testimonials',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Testimonial',
        'edit_item' => 'Edit Testimonial',
        'new_item' => 'New Testimonial',
        'view_item' => 'View Testimonial',
        'search_items' => 'Search Testimonials',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => false,
        'rewrite' => array('slug' => 'testimonials'),
        'show_in_rest' => true,
    );

    register_post_type('testimonial', $args);
}
add_action('init', 'ts_register_testimonial_cpt');

// Code to Register Testimonial Category Taxonomy
function ts_register_testimonial_category() {
    $labels = array(
        'name' => 'Testimonial Categories',
        'singular_name' => 'Testimonial Category',
    );

    register_taxonomy('testimonial_category', 'testimonial', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'ts_register_testimonial_category');

// Code to Add Custom Meta Boxes
function ts_add_testimonial_meta_boxes() {
    add_meta_box(
        'ts_testimonial_details',
        'Testimonial Details',
        'ts_render_testimonial_meta_box',
        'testimonial',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'ts_add_testimonial_meta_boxes');

function ts_render_testimonial_meta_box($post) {
    $job_title = get_post_meta($post->ID, 'ts_job_title', true);
    $company = get_post_meta($post->ID, 'ts_company', true);
    $bio = get_post_meta($post->ID, 'ts_bio', true);

    wp_nonce_field('ts_save_testimonial_meta', 'ts_meta_nonce');

    ?>
    <p><label>Job Title:</label><br>
        <input type="text" name="ts_job_title" value="<?php echo esc_attr($job_title); ?>" class="widefat">
    </p>
    <p><label>Company:</label><br>
        <input type="text" name="ts_company" value="<?php echo esc_attr($company); ?>" class="widefat">
    </p>
    <p><label>Bio:</label><br>
        <textarea name="ts_bio" rows="4" class="widefat"><?php echo esc_textarea($bio); ?></textarea>
    </p>
    <?php
}

function ts_save_testimonial_meta($post_id) {
    if (!isset($_POST['ts_meta_nonce']) || !wp_verify_nonce($_POST['ts_meta_nonce'], 'ts_save_testimonial_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, 'ts_job_title', sanitize_text_field($_POST['ts_job_title']));
    update_post_meta($post_id, 'ts_company', sanitize_text_field($_POST['ts_company']));
    update_post_meta($post_id, 'ts_bio', sanitize_textarea_field($_POST['ts_bio']));
}
add_action('save_post', 'ts_save_testimonial_meta');

// Code to add CSS and JS from CDN and Enqueue Scripts and Styles
function ts_enqueue_assets() {
    wp_enqueue_style('ts-slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_script('ts-slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
    wp_enqueue_style('ts-style', plugin_dir_url(__FILE__) . 'css/testimonial-slider.css');
    wp_enqueue_script('ts-script', plugin_dir_url(__FILE__) . 'js/testimonial-slider.js', array('jquery', 'ts-slick-js'), null, true);
}
add_action('wp_enqueue_scripts', 'ts_enqueue_assets');

// Code to create a Shortcode [testimonial_slider category="slug"]
function ts_testimonial_slider_shortcode($atts) {
    $atts = shortcode_atts(array('category' => ''), $atts);

    $args = array(
        'post_type' => 'testimonial',
        'posts_per_page' => -1,
    );

    if ($atts['category']) {
        $args['tax_query'] = array(array(
            'taxonomy' => 'testimonial_category',
            'field' => 'slug',
            'terms' => $atts['category'],
        ));
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<div class="ts-slider">';
        while ($query->have_posts()) {
            $query->the_post();
            $job_title = get_post_meta(get_the_ID(), 'ts_job_title', true);
            $company = get_post_meta(get_the_ID(), 'ts_company', true);
            $bio = get_post_meta(get_the_ID(), 'ts_bio', true);
            $image = get_the_post_thumbnail(get_the_ID(), 'thumbnail', array('class' => 'ts-thumb'));

            echo '<div class="ts-slide">';
            echo $image;
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p class="ts-job">' . esc_html($job_title) . ' at ' . esc_html($company) . '</p>';
            echo '<p class="ts-bio">' . esc_html($bio) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    }

    return ob_get_clean();
}
add_shortcode('testimonial_slider', 'ts_testimonial_slider_shortcode'); // Use [testimonial_slider] shortcode to display the testimonial slider.