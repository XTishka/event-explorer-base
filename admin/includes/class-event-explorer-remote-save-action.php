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
                $remote         = new Event_Explorer_Remote_Post($post->ID, $token, $location['source']);
                $remote_post_id = $this->get_remote_post_id($post, $location['source']);

                if ($remote_post_id === false) :
                    $post_data['featured_media'] = $this->upload_media($post->ID, $token, $location['source']);
                    $remote->publish_post($post_data);
                else :
                    $post_data['featured_media'] = $this->update_media($remote, $post->ID, $remote_post_id, $token, $location['source']);
                    $remote          = new Event_Explorer_Remote_Post($post->ID, $post_data, $token, $location['source']);
                    $remote->update_post($post_data);
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

        if (!empty($difference)) :
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

    private function get_remote_categories($categories): array
    {
        $matching_values = array_intersect($categories['remote_values'], $categories['local_values']);
        $remote_ids = [];

        foreach ($categories['remote'] as $id => $value) :
            if (in_array($value, $matching_values)) $remote_ids[] = $id;
        endforeach;

        return $remote_ids;
    }

    private function upload_media($local_post_id, $token, $source)
    {
        $local_featured_image = get_post_thumbnail_id($local_post_id);
        $featured_image_url = wp_get_attachment_image_src($local_featured_image, 'full');
        $media = new Event_Explorer_Remote_Media($local_post_id, $token, $source);
        $uploaded_featured_image_id = $media->upload($featured_image_url[0]);
        return $uploaded_featured_image_id;
    }

    private function update_media(Event_Explorer_Remote_Post $remote, $local_post_id, $remote_post_id, $token, $source)
    {
        $remote_post = $remote->get_post($remote_post_id);
        $local_featured_image = get_post_thumbnail_id($local_post_id);
        $remote_local_featured_image = $remote_post['meta']['local_featured_image'];
        $remote_featured_image = $remote_post['meta']['remote_featured_image'];

        if ($local_featured_image !== $remote_local_featured_image) :
            return $this->upload_media($local_post_id, $token, $source);
        endif;

        return $remote_featured_image;
    }
}
