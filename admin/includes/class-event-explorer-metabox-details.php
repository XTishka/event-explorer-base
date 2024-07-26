<?php

// admin/includes/class-event-explorer-metabox-details.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Events_Explorer_Metabox_Details {

    private $plugin_name;
	private $version;

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box_data'));
        add_action('init', array($this, 'register_meta_fields'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'event_subtitle', 
            __('Event Details', $this->plugin_name), 
            array($this, 'render_subtitle_meta_box_content'), 
            'event', 
            'normal', 
            'high'
        );
    }

    public function render_subtitle_meta_box_content($post) {
        wp_nonce_field('event_subtitle', 'event_subtitle_nonce');
        wp_nonce_field('next_preview_title', 'next_preview_title_nonce');
        wp_nonce_field('next_preview_description', 'next_preview_description_nonce');


        $subtitle = get_post_meta($post->ID, 'event_subtitle', true);
        $next_preview_title = get_post_meta($post->ID, 'next_preview_title', true);
        $next_preview_description = get_post_meta($post->ID, 'next_preview_description', true);

        echo '<p>';
        echo '<label for="event_subtitle">' . __('Subtitle', $this->plugin_name) . '</label>';
        echo '<input type="text" id="event_subtitle" name="event_subtitle" value="' . esc_attr($subtitle) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="next_preview_title">' . __('Next Preview Title', $this->plugin_name) . '</label>';
        echo '<input type="text" id="next_preview_title" name="next_preview_title" value="' . esc_attr($next_preview_title) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="next_preview_description">' . __('Next Preview Description', $this->plugin_name) . '</label>';
        echo '<textarea id="next_preview_description" name="next_preview_description" class="widefat">' . esc_textarea($next_preview_description) . '</textarea>';
        echo '</p>';
    }

    public function save_meta_box_data($post_id) {
        // Save subtitle
        if (!isset($_POST['event_subtitle_nonce']) || !wp_verify_nonce($_POST['event_subtitle_nonce'], 'event_subtitle')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST['post_type']) && 'event' == $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        if (isset($_POST['event_subtitle'])) {
            $subtitle = sanitize_text_field($_POST['event_subtitle']);
            update_post_meta($post_id, 'event_subtitle', $subtitle);
        }

        // Save next preview title
        if (!isset($_POST['next_preview_title_nonce']) || !wp_verify_nonce($_POST['next_preview_title_nonce'], 'next_preview_title')) {
            return;
        }
        if (isset($_POST['next_preview_title'])) {
            $next_preview_title = sanitize_text_field($_POST['next_preview_title']);
            update_post_meta($post_id, 'next_preview_title', $next_preview_title);
        }

        // Save next preview description
        if (!isset($_POST['next_preview_description_nonce']) || !wp_verify_nonce($_POST['next_preview_description_nonce'], 'next_preview_description')) {
            return;
        }
        if (isset($_POST['next_preview_description'])) {
            $next_preview_description = sanitize_textarea_field($_POST['next_preview_description']);
            update_post_meta($post_id, 'next_preview_description', $next_preview_description);
        }
    }

    public function register_meta_fields() {
        register_post_meta('event', 'event_subtitle', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
        register_post_meta('event', 'next_preview_title', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
        register_post_meta('event', 'next_preview_description', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
    }
}
