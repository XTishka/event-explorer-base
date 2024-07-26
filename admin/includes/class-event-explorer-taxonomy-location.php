<?php

// admin/includes/class-event-explorer-taxonomy-location.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Events_Explorer_Taxonomy_Location {

    private $plugin_name;
	private $version;

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

        add_action('init', array($this, 'register_taxonomy'), 0);
    }

    public function register_taxonomy() {
        $labels = array(
            'name' => _x('Locations', 'taxonomy general name', $this->plugin_name),
            'singular_name' => _x('Location', 'taxonomy singular name', $this->plugin_name),
            'search_items' => __('Search Locations', $this->plugin_name),
            'all_items' => __('All Locations', $this->plugin_name),
            'parent_item' => __('Parent Location', $this->plugin_name),
            'parent_item_colon' => __('Parent Location:', $this->plugin_name),
            'edit_item' => __('Edit Location', $this->plugin_name),
            'update_item' => __('Update Location', $this->plugin_name),
            'add_new_item' => __('Add New Location', $this->plugin_name),
            'new_item_name' => __('New Location Name', $this->plugin_name),
            'menu_name' => __('Location', $this->plugin_name),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'events-location'),
            'show_in_rest'       => true,
        );

        register_taxonomy('events-location', array('event'), $args);
    }
}
