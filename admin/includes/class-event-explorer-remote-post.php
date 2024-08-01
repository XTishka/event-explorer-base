<?php

// admin/class-event-explorer-remote-post.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Post
{
    public object $post;
    public string $endpoint;
    public array $headers;

    public function __construct($post)
    {
        $auth = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if (!$token) {
            error_log('Failed to get token');
            return;
        }

        $this->post = $post;
        $this->endpoint = $auth['api_url'] . '/wp-json/wp/v2/events';
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    public function publish_post()
    {
        $response = wp_remote_post($this->endpoint, [
            'body' => json_encode(Event_Explorer_Remote_Service::get_post_data($this->post)),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        update_post_meta($this->post->ID, 'remote_post_id', $data['id']);
        update_post_meta($this->post->ID, 'remote_post_link', $data['link']);

        return $data;
    }

    public function update_post()
    {
        $remote_post_id = get_post_meta($this->post->ID, 'remote_post_id', true);
        if (!$remote_post_id) return;

        $response = wp_remote_post($this->endpoint . '/' . $remote_post_id, [
            'method' => 'PUT',
            'body' => json_encode(Event_Explorer_Remote_Service::get_post_data($this->post)),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return $data;
    }
}
