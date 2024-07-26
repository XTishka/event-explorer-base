<?php

// public/class-event-explorer-public.php

class Event_Explorer_Public
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

	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/event-explorer-public.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
		wp_enqueue_style($this->plugin_name . 'slick-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');
	}

	public function enqueue_scripts()
	{
		$events = new Events_Explorer_Shortcode();
		$total_posts = $events->totalPosts();
		$total_pages = $events->totalPages();

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/event-explorer-public.js', array('jquery'), $this->version, false);
		wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);
		wp_enqueue_script($this->plugin_name . '-slick-init', plugin_dir_url(__FILE__) . 'js/slick-init.js', array('slick-js'), '', true);

		wp_localize_script($this->plugin_name, 'wpApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'quantity' => intval(get_option('quantity'))
		));
		wp_localize_script($this->plugin_name, 'getEvents', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));
		wp_localize_script($this->plugin_name, 'totalPosts', array(
			'total_posts' => $total_posts,
			'total_pages' => $total_pages,
		));
	}

	public function init()
	{
		new Events_Explorer_Shortcode();
		new Events_Explorer_Next_Preview_Shortcode();
	}

	private function load_dependencies()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/includes/class-events-explorer-shortcode.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/includes/class-events-next-preview-shortcode.php';
	}
}
