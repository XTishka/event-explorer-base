<?php

// admin/class-event-explorer-admin.php

class Event_Explorer_Admin
{

	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();
		$this->init();
	}

	public function load_dependencies()
	{
		$dependencies = [
			'admin/includes/class-event-explorer-post-type.php',
			'admin/includes/class-event-explorer-taxonomy-location.php',
			'admin/includes/class-event-explorer-metabox-details.php',
			'admin/includes/class-event-explorer-metabox-date-and-time.php',
			'admin/includes/class-event-explorer-remote-save-action.php',
			'admin/includes/class-event-explorer-remote-service.php',
			'admin/includes/class-event-explorer-remote-post.php',
			'admin/includes/class-event-explorer-remote-categories.php',
			'admin/includes/class-event-explorer-remote-multimedia.php',
			'admin/includes/class-event-explorer-wpbakery-sound-carousel-element.php',
		];

		foreach ($dependencies as $dependency) {
			require_once plugin_dir_path(dirname(__FILE__)) . $dependency;
		}
	}

	public function init()
	{
		new Events_Explorer_Post_Type($this->plugin_name, $this->version);
		new Events_Explorer_Taxonomy_Location($this->plugin_name, $this->version);
		new Events_Explorer_Metabox_Details($this->plugin_name, $this->version);
		new Events_Explorer_Metabox_Date_And_Time($this->plugin_name, $this->version);
		new Event_Explorer_Remote_Save_Action();
		new Events_Explorer_WPBakery_Sound_Carousel_Element($this->plugin_name, $this->version);
	}

	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/event-explorer-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/event-explorer-admin.js', array('jquery'), $this->version, false);
	}
}
