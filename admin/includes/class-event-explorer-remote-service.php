<?php

// admin/class-event-explorer-remote-service.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Service
{

    public static function get_token($api_url, $username, $password)
    {
        $response = wp_remote_post($api_url . '/wp-json/jwt-auth/v1/token', array(
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password,
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['token'])) {
            return $data['token'];
        }

        return null;
    }

    public static function authorize($post): array
    {
        $terms = wp_get_post_terms($post->ID, 'events-location');
        $locations_with_meta = array();

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $source = get_term_meta($term->term_id, 'source', true);
                $username = get_term_meta($term->term_id, 'username', true);
                $password = get_term_meta($term->term_id, 'password', true);

                if (!empty($source) && !empty($username) && !empty($password)) :
                    $locations_with_meta[$term->name] = array(
                        'slug'     => $term->slug,
                        'source'   => $source,
                        'username' => $username,
                        'password' => $password,
                    );
                endif;
            }
        }
        return $locations_with_meta;
    }

    public static function get_post_data($post)
    {
        $meta = get_post_meta($post->ID);
        $get_meta_value = function ($key) use ($meta) {
            return isset($meta[$key][0]) ? $meta[$key][0] : '';
        };
        // $categories = new Event_Explorer_Remote_Categories();

        return array(
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => 'publish',
            'meta' => array(
                'event_subtitle'            => $get_meta_value('event_subtitle'),
                'next_preview_title'        => $get_meta_value('next_preview_title'),
                'next_preview_description'  => $get_meta_value('next_preview_description'),
                'date_start'                => $get_meta_value('date_start'),
                'time_start'                => $get_meta_value('time_start'),
                'date_end'                  => $get_meta_value('date_end'),
                'time_end'                  => $get_meta_value('time_end'),
            ),
            // 'events-location' => $categories->get_remote_category($post),
        );
    }
}
