<?php

// admin/class-event-explorer-remote-categories.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Categories
{
    public int      $post_id;
    public string   $endpoint;
    public array    $headers;
    public string   $token;
    public string   $source;

    public function __construct(int $post_id, string $token, string $source)
    {
        $this->post_id = $post_id;
        $this->source = $source;
        $this->token = $token;
        $this->endpoint = $source . '/wp-json/wp/v2/events-location';
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    public function get_locations()
    {
        $categoriesArray = [];

        $response = wp_remote_get($this->endpoint, [
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) return $response->get_error_message();

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) return 'Invalid response data.';

        if (!empty($data)) {
            foreach ($data as $category) {
                if (isset($category['id']) && isset($category['name'])) {
                    $categoriesArray[$category['id']] = $category['name'];
                }
            }
        }

        return $categoriesArray;
    }


    public function create_location(array $locations)
    {
        foreach ($locations as $location) :
            error_log('Creating location: ' . $location);
            $response = wp_remote_post($this->endpoint, array(
                'body' => json_encode(array('name' => $location)),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ),
            ));

            if (is_wp_error($response)) return $response->get_error_message();
        endforeach;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }
}
