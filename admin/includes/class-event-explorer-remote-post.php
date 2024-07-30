<?php

// admin/class-event-explorer-remote-post.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Post
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

        if ($update) {
            $this->update_post($post);
        } else {
            $this->publish_post($post);
        }
    }

    public function publish_post($post)
    {
        $auth = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if (!$token) {
            return 'Failed to retrieve token';
        }

        $response = wp_remote_post($auth['api_url'] . '/wp-json/wp/v2/events', array(
            'body' => json_encode(Event_Explorer_Remote_Service::get_post_data($post)),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        update_post_meta($post->ID, 'remote_post_id', $data['id']);
        update_post_meta($post->ID, 'remote_post_link', $data['link']);

        return $data;
    }

    public function update_post($post)
    {
        $remote_post_id = get_post_meta($post->ID, 'remote_post_id', true);
        if (!$remote_post_id) return;

        $auth = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if (!$token) {
            return 'Failed to retrieve token';
        }

        $response = wp_remote_post($auth['api_url'] . '/wp-json/wp/v2/events/' . $remote_post_id, array(
            'method' => 'PUT',
            'body' => json_encode(Event_Explorer_Remote_Service::get_post_data($post)),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return $data;
    }
}
