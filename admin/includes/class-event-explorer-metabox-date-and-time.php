<?php

// admin/includes/class-event-explorer-metabox-date-and-time.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Events_Explorer_Metabox_Date_And_Time {

    private $plugin_name;
	private $version;

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box_data'));
        add_action('init', array($this, 'register_meta_fields'));
    }

    public function add_meta_box() {
        add_meta_box(
            'event_dates', 
            __('Dates and Times', $this->plugin_name), 
            array($this, 'render_meta_box_content'), 
            'event', 
            'side', 
            'high'
        );
    }

    public function render_meta_box_content($post) {
        wp_nonce_field('event_dates', 'event_dates_nonce');

        $date_start = get_post_meta($post->ID, 'date_start', true);
        $time_start = get_post_meta($post->ID, 'time_start', true);
        $date_end = get_post_meta($post->ID, 'date_end', true);
        $time_end = get_post_meta($post->ID, 'time_end', true);

        echo '<p>';
        echo '<label for="date_start">' . __('Date Start', $this->plugin_name) . '</label>';
        echo '<input type="date" id="date_start" name="date_start" value="' . esc_attr($date_start) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="time_start">' . __('Time Start', $this->plugin_name) . '</label>';
        echo '<input type="time" id="time_start" name="time_start" value="' . esc_attr($time_start) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="date_end">' . __('Date End', $this->plugin_name) . '</label>';
        echo '<input type="date" id="date_end" name="date_end" value="' . esc_attr($date_end) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="time_end">' . __('Time End', $this->plugin_name) . '</label>';
        echo '<input type="time" id="time_end" name="time_end" value="' . esc_attr($time_end) . '" class="widefat" />';
        echo '</p>';
    }

    public function save_meta_box_data($post_id) {
        if (!isset($_POST['event_dates_nonce']) || !wp_verify_nonce($_POST['event_dates_nonce'], 'event_dates')) {
            return;
        }
        if (isset($_POST['date_start'])) {
            $date_start = sanitize_text_field($_POST['date_start']);
            update_post_meta($post_id, 'date_start', $date_start);
        }
        if (isset($_POST['time_start'])) {
            $time_start = sanitize_text_field($_POST['time_start']);
            update_post_meta($post_id, 'time_start', $time_start);
        }
        if (isset($_POST['date_end'])) {
            $date_end = sanitize_text_field($_POST['date_end']);
            update_post_meta($post_id, 'date_end', $date_end);
        }
        if (isset($_POST['time_end'])) {
            $time_end = sanitize_text_field($_POST['time_end']);
            update_post_meta($post_id, 'time_end', $time_end);
        }
    }

    public function register_meta_fields() {
        register_post_meta('event', 'date_start', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
        register_post_meta('event', 'time_start', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
        register_post_meta('event', 'date_end', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
        register_post_meta('event', 'time_end', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ));
    }
}
