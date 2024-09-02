<?php

// admin/includes/class-event-explorer-taxonomy-location.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Events_Explorer_Taxonomy_Location
{

    public function __construct()
    {
        add_action('init', array($this, 'register_taxonomy'), 0);
        add_action('init', array($this, 'create_default_terms'), 0);
        add_action('events-location_add_form_fields', array($this, 'add_taxonomy_fields'), 10, 2);
        add_action('events-location_edit_form_fields', array($this, 'edit_taxonomy_fields'), 10, 2);
        add_action('edited_events-location', array($this, 'save_taxonomy_fields'), 10, 2);
        add_action('create_events-location', array($this, 'save_taxonomy_fields'), 10, 2);
    }

    public function register_taxonomy()
    {
        $labels = array(
            'name' => _x('Locations', 'taxonomy general name', 'event-explorer'),
            'singular_name' => _x('Location', 'taxonomy singular name', 'event-explorer'),
            'search_items' => __('Search Locations', 'event-explorer'),
            'all_items' => __('All Locations', 'event-explorer'),
            'parent_item' => __('Parent Location', 'event-explorer'),
            'parent_item_colon' => __('Parent Location:', 'event-explorer'),
            'edit_item' => __('Edit Location', 'event-explorer'),
            'update_item' => __('Update Location', 'event-explorer'),
            'add_new_item' => __('Add New Location', 'event-explorer'),
            'new_item_name' => __('New Location Name', 'event-explorer'),
            'menu_name' => __('Location', 'event-explorer'),
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

    public function create_default_terms()
    {
        if (!term_exists('Local', 'events-location')) {
            wp_insert_term(
                'Local',
                'events-location',
                array(
                    'description' => 'Default local location',
                    'slug' => 'local',
                )
            );
        }
    }

    public function add_taxonomy_fields()
    {
?>
        <div class="form-field">
            <label for="term_meta[source]"><?php _e('Source', 'event-explorer'); ?></label>
            <input type="text" name="term_meta[source]" id="term_meta[source]" value="">
            <p class="description"><?php _e('Enter the source for this location', 'event-explorer'); ?></p>
        </div>
        <div class="form-field">
            <label for="term_meta[username]"><?php _e('Username', 'event-explorer'); ?></label>
            <input type="text" name="term_meta[username]" id="term_meta[username]" value="">
            <p class="description"><?php _e('Enter the username for this location', 'event-explorer'); ?></p>
        </div>
        <div class="form-field">
            <label for="term_meta[password]"><?php _e('Password', 'event-explorer'); ?></label>
            <input type="password" name="term_meta[password]" id="term_meta[password]" value="">
            <p class="description"><?php _e('Enter the password for this location', 'event-explorer'); ?></p>
        </div>
    <?php
    }

    public function edit_taxonomy_fields($term)
    {
        $term_meta = get_term_meta($term->term_id);
        $source = isset($term_meta['source'][0]) ? esc_attr($term_meta['source'][0]) : '';
        $username = isset($term_meta['username'][0]) ? esc_attr($term_meta['username'][0]) : '';
        $password = isset($term_meta['password'][0]) ? esc_attr($term_meta['password'][0]) : '';
    ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[source]"><?php _e('Source', 'event-explorer'); ?></label></th>
            <td>
                <input type="text" name="term_meta[source]" id="term_meta[source]" value="<?php echo $source; ?>">
                <p class="description"><?php _e('Enter the source for this location', 'event-explorer'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[username]"><?php _e('Username', 'event-explorer'); ?></label></th>
            <td>
                <input type="text" name="term_meta[username]" id="term_meta[username]" value="<?php echo $username; ?>">
                <p class="description"><?php _e('Enter the username for this location', 'event-explorer'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[password]"><?php _e('Password', 'event-explorer'); ?></label></th>
            <td>
                <input type="password" name="term_meta[password]" id="term_meta[password]" value="<?php echo $password; ?>">
                <p class="description"><?php _e('Enter the password for this location', 'event-explorer'); ?></p>
            </td>
        </tr>
<?php
    }

    public function save_taxonomy_fields($term_id)
    {
        if (isset($_POST['term_meta'])) {
            $term_meta = array();
            $term_meta['source'] = isset($_POST['term_meta']['source']) ? sanitize_text_field($_POST['term_meta']['source']) : '';
            $term_meta['username'] = isset($_POST['term_meta']['username']) ? sanitize_text_field($_POST['term_meta']['username']) : '';
            $term_meta['password'] = isset($_POST['term_meta']['password']) ? sanitize_text_field($_POST['term_meta']['password']) : '';

            foreach ($term_meta as $key => $value) {
                update_term_meta($term_id, $key, $value);
            }
        }
    }
}
