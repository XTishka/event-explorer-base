<?php

// admin/class-event-explorer-remote-post.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Post
{
    public  object  $post;
    private string  $endpoint;
    private array   $headers;

    public function __construct($post)
    {
        $this->post = $post;

        $auth  = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if ($token) :
            $this->endpoint = $auth['api_url'] . '/wp-json/wp/v2/events';
            $this->headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];
        else :
            error_log('Failed to retrieve token');
        endif;
    }

    public function publish_post()
    {
        $response = wp_remote_post($this->endpoint, [
            'body'    => json_encode(Event_Explorer_Remote_Service::get_post_data($this->post)),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        update_post_meta($this->post->ID, 'remote_post_id', $data['id']);
        update_post_meta($this->post->ID, 'remote_post_link', $data['link']);
        update_post_meta($this->post->ID, 'remote_featured_image_id', $data['featured_media']);
        update_post_meta($this->post->ID, 'local_featured_image', get_post_thumbnail_id($this->post->ID));

        return $data;
    }

    public function update_post()
    {
        $remote_post_id = get_post_meta($this->post->ID, 'remote_post_id', true);
        if (!$remote_post_id) return;

        $response = wp_remote_post($this->endpoint . '/' . $remote_post_id, [
            'method'  => 'PUT',
            'body'    => json_encode(Event_Explorer_Remote_Service::get_post_data($this->post)),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        update_post_meta($this->post->ID, 'remote_post_id', $data['id']);
        update_post_meta($this->post->ID, 'remote_post_link', $data['link']);
        update_post_meta($this->post->ID, 'local_featured_image', get_post_thumbnail_id($this->post->ID));
        update_post_meta($this->post->ID, 'remote_featured_image', $data['featured_media']);

        return $data;
    }

    public function get_remote_post($remote_post_id): array|bool
    {
        $response = wp_remote_get($this->endpoint . '/' . $remote_post_id, [
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) return $data;

        error_log('Failed to retrieve remote post');
        return false;
    }
}
