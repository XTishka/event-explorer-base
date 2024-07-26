<?php

// includes/class-event-explorer.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer
{

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->version = defined('EVENT_EXPLORER_VERSION') ? EVENT_EXPLORER_VERSION : '1.0.0';
        $this->plugin_name = 'event-explorer-client';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-event-explorer-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-event-explorer-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-event-explorer-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-event-explorer-public.php';

        $this->loader = new Event_Explorer_Loader();
    }

    private function set_locale()
    {
        $plugin_i18n = new Event_Explorer_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new Event_Explorer_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks()
    {
        $plugin_public = new Event_Explorer_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function run()
    {
        $this->loader->run();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_loader()
    {
        return $this->loader;
    }

    public function get_version()
    {
        return $this->version;
    }
}
