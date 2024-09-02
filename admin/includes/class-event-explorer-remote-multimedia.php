<?php

// admin/class-event-explorer-remote-multimedia.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Multimedia
{
    private object $post;
    private $auth;
    private $token;
    private $remote;
    private $endpoint;

    public function __construct($post)
    {
        $this->post     = $post;
        $this->auth     = Event_Explorer_Remote_Service::authorize($post);
        $this->token    = Event_Explorer_Remote_Service::get_token($this->auth['api_url'], $this->auth['username'], $this->auth['password']);
        $this->remote   = new Event_Explorer_Remote_Post($post);
        $this->endpoint = $this->auth['api_url'] . '/wp-json/wp/v2/media';
    }

    public function get_featured_image_id(): int
    {
        // TODO: duplicate image upload on featured image update

        $local_featured_image_id = get_post_thumbnail_id($this->post->ID);
        $remote_post_id = get_post_meta($this->post->ID, 'remote_post_id', true);
        $remote_post = $this->remote->get_remote_post($remote_post_id);

        if ($local_featured_image_id === $remote_post['meta']['local_featured_image']) :
            return $local_featured_image_id;
        else :
            $remote_featured_image_id = $this->upload_featured_image();
            return $remote_featured_image_id;
        endif;
    }

    public function upload_featured_image()
    {
        $featured_image = get_the_post_thumbnail_url($this->post->ID);

        if (!$this->token) return 'Failed to retrieve token';

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Upload image
        $tmp = download_url($featured_image);
        if (is_wp_error($tmp)) return 'Failed to download image';

        $file_array = [
            'name'     => basename($featured_image),
            'tmp_name' => $tmp,
        ];

        // Read the file into a string
        $file_content = file_get_contents($file_array['tmp_name']);
        if ($file_content === false) {
            @unlink($file_array['tmp_name']);
            return 'Failed to read downloaded image';
        }

        // Create the multipart/form-data headers
        $boundary = wp_generate_password(24);
        $headers = array(
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
        );

        // Create the body of the request
        $body = "--$boundary\r\n";
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . $file_array['name'] . "\"\r\n";
        $body .= 'Content-Type: ' . mime_content_type($file_array['tmp_name']) . "\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= "--$boundary--\r\n";

        $response = wp_remote_post($this->endpoint, array(
            'body' => $body,
            'headers' => $headers,
        ));

        @unlink($file_array['tmp_name']); // Удаляем временный файл

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) return $data['id'];

        return false;
    }
}
