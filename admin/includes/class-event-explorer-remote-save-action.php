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
                $categoriesData  = $this->synch_categories($post->ID, $token, $location['source']);
                $categoriesArray = $this->get_remote_categories($categoriesData);

                $post_data      = Event_Explorer_Remote_Service::get_post_data($post, $categoriesArray);
                $remote         = new Event_Explorer_Remote_Post($post->ID, $post_data, $token, $location['source']);
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

    private function synch_categories(int $post_id, string $token, string $source): array
    {
        $remoteCategories = new Event_Explorer_Remote_Categories($post_id, $token, $source);

        $local  = Event_Explorer_Remote_Service::get_local_locations($post_id);
        $remote = $remoteCategories->get_locations();

        $local_values  = array_values($local);
        $remote_values = array_values($remote);
        $difference    = array_diff($local_values, $remote_values);

        if (!empty($difference)) :1
            $remoteCategories->create_location($difference);
            $this->synch_categories($post_id, $token, $source);
        endif;

        return [
            'local'         => $local,
            'remote'        => $remote,
            'local_values'  => $local_values,
            'remote_values' => $remote_values,
        ];
    }

    public function get_remote_categories($categories): array
    {
        $matching_values = array_intersect($categories['remote_values'], $categories['local_values']);
        $remote_ids = [];

        foreach ($categories['remote'] as $id => $value) :
            if (in_array($value, $matching_values)) $remote_ids[] = $id;
        endforeach;

        return $remote_ids;
    }
}
