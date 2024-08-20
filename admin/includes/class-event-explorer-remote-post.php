<?php

// admin/class-event-explorer-remote-post.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Post
{
    public int      $post_id;
    public string   $endpoint;
    public array    $headers;
    public string   $token;
    public string   $source;

    public function __construct($post_id, $token, $source)
    {
        $this->post_id = $post_id;
        $this->source = $source;
        $this->endpoint = $source . '/wp-json/wp/v2/events';
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    public function get_post($post_id)
    {
        $response = wp_remote_get($this->endpoint . '/' . $post_id, [
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // if (isset($data['id'])) error_log('Retrieved remote post: ' . print_r($data, true));
        if (isset($data['id'])) return $data;

        error_log('Failed to retrieve remote post');
        return false;
    }

    public function publish_post($post_data)
    {
        $response = wp_remote_post($this->endpoint, [
            'body' => json_encode($post_data),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $this->update_local_meta($data);

        return $data;
    }

    public function update_post($post_data)
    {
        $serialized_data = get_post_meta($this->post_id, 'remote_post_id', true);
        $data = unserialize($serialized_data);

        $remote_post_id = (isset($data[$this->source])) ? $data[$this->source] : false;

        if ($remote_post_id === false) :
            error_log('Failed to get remote post ID: ' . $this->source);
            return;
        endif;

        $response = wp_remote_post($this->endpoint . '/' . $remote_post_id, [
            'method' => 'PUT',
            'body' => json_encode($post_data),
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $this->update_local_meta($data);

        return $data;
    }

    private function update_local_meta($data)
    {
        $serialized_data = get_post_meta($this->post_id, 'remote_post_id', true);
        $data_id = unserialize($serialized_data);
        $data_id[$this->source] = $data['id'];

        update_post_meta($this->post_id, 'remote_post_id', serialize($data_id));
        update_post_meta($this->post_id, 'remote_post_link', $data['link']);
        update_post_meta($this->post_id, 'remote_featured_image_id', $data['featured_media']);
        update_post_meta($this->post_id, 'local_featured_image', get_post_thumbnail_id($this->post_id));
    }
}
