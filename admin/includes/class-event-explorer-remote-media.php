<?php

// admin/includes/class-event-explorer-remote-media.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Media
{
    public int      $post_id;
    public string   $endpoint;
    public array    $headers;
    public string   $token;
    public string   $source;
    public string   $boundary;

    public function __construct(int $post_id, string $token, string $source)
    {
        $this->post_id = $post_id;
        $this->source = $source;
        $this->token = $token;
        $this->boundary = wp_generate_password(24);
        $this->endpoint = $source . '/wp-json/wp/v2/media'; // Конечная точка для медиафайлов
        $this->headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'multipart/form-data; boundary=' . $this->boundary,
        ];
    }

    public function upload(string $file_path)
    {
        error_log($file_path);

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Upload image
        $tmp = download_url($file_path);
        if (is_wp_error($tmp)) return 'Failed to download image';

        $file_array = [
            'name'     => basename($file_path),
            'tmp_name' => $tmp,
        ];

        // Read the file into a string
        $file_content = file_get_contents($file_array['tmp_name']);
        if ($file_content === false) {
            @unlink($file_array['tmp_name']);
            error_log('Failed to read downloaded image');
            return 'Failed to read downloaded image';
        }

        // Create the body of the request
        $boundary = $this->boundary;
        $body = "--$boundary\r\n";
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . $file_array['name'] . "\"\r\n";
        $body .= 'Content-Type: ' . mime_content_type($file_array['tmp_name']) . "\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= "--$boundary--\r\n";

        $response = wp_remote_post($this->endpoint, array(
            'body' => $body,
            'headers' => $this->headers,
        ));

        @unlink($file_array['tmp_name']); // Удаляем временный файл

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // error_log('Response: ' . $data['id']);
        if (isset($data['id'])) :
            // error_log('Response image uploaded: ' . $data['id']);
            return $data['id'];
        endif;

        error_log('Failed to upload image: ' . $body);

        return false;
    }
}
