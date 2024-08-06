<?php

// admin/class-event-explorer-remote-save-action.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Save_Action
{
    public function __construct()
    {
        add_action('save_post', array($this, 'save'), 10, 3);
    }

    public function save($post_id, $post, $update)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if ($post->post_type !== 'event') return;

        $locations = Event_Explorer_Remote_Service::authorize($post);

        foreach ($locations as $location) {
            $token = Event_Explorer_Remote_Service::get_token($location['source'], $location['username'], $location['password']);

            if ($token) :
                $remote = new Event_Explorer_Remote_Post($post, $token, $location['source']);
                $remote_post_id = $this->get_remote_post_id($post, $location['source']);
                if ($remote_post_id === false) :
                    $remote->publish_post();
                else :
                    $remote->update_post();
                endif;
            else :
                error_log('Failed to get token: ' . $location['source']);
            endif;
        }
    }

    private function get_remote_post_id($post, $source)
    {
        $serialized_data = get_post_meta($post->ID, 'remote_post_id', true);
        $data            = unserialize($serialized_data);
        return (isset($data[$source])) ? $data[$source] : false;
    }

    public function synch_categories($post_id, $post, $update): void
    {
        $local = $this->get_local_locations($post);
        $remote = $this->get_remote_locations($post);

        $local_values = array_values($local);
        $remote_values = array_values($remote);
        $difference = array_diff($local_values, $remote_values);

        if (!empty($difference)) :
            error_log('create_remote_location');
            $this->create_remote_location($post, $difference);
            $this->get_remote_category($post);
        endif;
    }

    public function get_remote_category($post): array
    {
        $matching_values = array_intersect($remote_values, $local_values);
        $remote_ids = array();
        foreach ($remote as $id => $value) {
            if (in_array($value, $matching_values)) {
                $remote_ids[] = $id;
            }
        }

        return $remote_ids;
    }


    public function get_local_locations($post): array
    {
        $categoriesArray = [];
        $categories = wp_get_post_terms(
            $post->ID,
            'events-location',
            array('fields' => 'all')
        );
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $categoriesArray[$category->term_id] = $category->name;
            }
        }

        return $categoriesArray;
    }
}
