<?php

// admin/class-event-explorer-remote-categories.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Categories
{
    public function get_remote_locations($post)
    {
        $categoriesArray = [];
        $auth = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if (!$token) {
            return 'Failed to retrieve token';
        }

        $response = wp_remote_get($auth['api_url'] . '/wp-json/wp/v2/events-location', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_wp_error($data)) {
            foreach ($data as $category) {
                $categoriesArray[$category['id']] = $category['name'];
            }
        }

        return $categoriesArray;
    }

    public static function create_remote_location($post, array $locations)
    {
        // Получение токена
        $auth = Event_Explorer_Remote_Service::authorize($post);
        $token = Event_Explorer_Remote_Service::get_token($auth['api_url'], $auth['username'], $auth['password']);

        if (!$token) {
            return 'Failed to retrieve token';
        }

        foreach ($locations as $location) :
            error_log('Creating location: ' . $location);
            $response = wp_remote_post($auth['api_url'] . '/wp-json/wp/v2/events-location', array(
                'body' => json_encode(array('name' => $location)),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ),
            ));

            if (is_wp_error($response)) {
                return $response->get_error_message();
            }
        endforeach;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }
}
