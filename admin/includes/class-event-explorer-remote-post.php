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
    public string $token;
    public string $source;

    public function __construct($post, $token, $source)
    {
        $this->post = $post;
        $this->source = $source;
        $this->endpoint = $source . '/wp-json/wp/v2/events';
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

        $serialized_data = get_post_meta($this->post->ID, 'remote_post_id', true);
        $data_id = unserialize($serialized_data);
        $data_id[$this->source] = $data['id'];

        update_post_meta($this->post->ID, 'remote_post_id', serialize($data_id));
        update_post_meta($this->post->ID, 'remote_post_link', $data['link']);

        return $data;
    }

    public function update_post()
    {
        $serialized_data = get_post_meta($this->post->ID, 'remote_post_id', true);
        $data = unserialize($serialized_data);

        $remote_post_id = (isset($data[$this->source])) ? $data[$this->source] : false;

        if ($remote_post_id === false) :
            error_log('Failed to get remote post ID: ' . $this->source);
            return;
        endif;

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
