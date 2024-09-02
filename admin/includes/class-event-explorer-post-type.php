<?php

// admin/includes/class-event-explorer-post-type.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Events_Explorer_Post_Type {

    private $plugin_name;
	private $version;

    public  function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
		$this->version = $version;
        add_action('init', array($this, 'register_post_type'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => _x('Events', 'post type general name', $this->plugin_name),
            'singular_name'      => _x('Event', 'post type singular name', $this->plugin_name),
            'menu_name'          => _x('Events', 'admin menu', $this->plugin_name),
            'name_admin_bar'     => _x('Event', 'add new on admin bar', $this->plugin_name),
            'add_new'            => _x('Add New', 'event', $this->plugin_name),
            'add_new_item'       => __('Add New Event', $this->plugin_name),
            'new_item'           => __('New Event', $this->plugin_name),
            'edit_item'          => __('Edit Event', $this->plugin_name),
            'view_item'          => __('View Event', $this->plugin_name),
            'all_items'          => __('All Events', $this->plugin_name),
            'search_items'       => __('Search Events', $this->plugin_name),
            'parent_item_colon'  => __('Parent Events:', $this->plugin_name),
            'not_found'          => __('No events found.', $this->plugin_name),
            'not_found_in_trash' => __('No events found in Trash.', $this->plugin_name)
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'event'),
            'taxonomies'         => array('events-location'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes'),
            'show_in_rest'       => true,
            'rest_base'          => 'events',
        );

        register_post_type('event', $args);
    }
}
