<?php

class Events_Explorer_Api {
    private $site_url;
    private $username;
    private $password;
    private $token;

    public function __construct($site_url, $username, $password) {
        $this->site_url = rtrim($site_url, '/');
        $this->username = $username;
        $this->password = $password;
    }

    private function sendRequest($url, $method, $data = null, $headers = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return null;
        }

        curl_close($ch);

        if ($http_code < 200 || $http_code >= 300) {
            echo 'HTTP error code: ' . $http_code . "\n";
            echo 'Response: ' . $result . "\n";
            return null;
        }

        return json_decode($result);
    }

    public function getToken() {
        if (!$this->token) {
            $url = $this->site_url . '/wp-json/jwt-auth/v1/token';
            $data = array(
                'username' => $this->username,
                'password' => $this->password
            );

            $response = $this->sendRequest($url, 'POST', $data);

            if (isset($response->token)) {
                $this->token = $response->token;
            } else {
                echo 'Error getting token: ' . (isset($response->message) ? $response->message : 'Unknown error');
                return null;
            }
        }
        return $this->token;
    }

    private function slugify($text) {
        // replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function createCategoryIfNotExists($category_name) {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        // Check if category exists
        $url = $this->site_url . '/wp-json/wp/v2/categories?slug=' . urlencode($this->slugify($category_name));
        $headers = array(
            'Authorization: Bearer ' . $token,
        );
        $response = $this->sendRequest($url, 'GET', null, $headers);

        if (!empty($response)) {
            // Category exists, return its ID
            return $response[0]->id;
        } else {
            // Category does not exist, create it
            $url = $this->site_url . '/wp-json/wp/v2/categories';
            $data = array(
                'name' => $category_name,
                'slug' => $this->slugify($category_name)
            );
            $response = $this->sendRequest($url, 'POST', $data, $headers);

            if (isset($response->id)) {
                return $response->id;
            } else {
                echo 'Error creating category: ' . (isset($response->message) ? $response->message : 'Unknown error');
                return null;
            }
        }
    }

    public function createPost($title, $content, $post_type = 'posts', $meta = [], $category_name = '') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        // Ensure category exists and get its ID
        $category_id = $this->createCategoryIfNotExists($category_name);
        if (!$category_id) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type;

        // Ensure meta fields are correctly formatted for custom fields
        $data = array(
            'title' => $title,
            'content' => $content,
            'status' => 'publish',
            'meta' => (object) $meta,
            'categories' => [$category_id]
        );

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'POST', $data, $headers);

        if (isset($response->id)) {
            return $response;
        } else {
            echo 'Error creating post: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }

    public function updatePost($post_id, $title, $content, $post_type = 'posts', $meta = [], $category_name = '') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        // Ensure category exists and get its ID
        $category_id = $this->createCategoryIfNotExists($category_name);
        if (!$category_id) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type . '/' . $post_id;

        // Ensure meta fields are correctly formatted for custom fields
        $data = array(
            'title' => $title,
            'content' => $content,
            'meta' => (object) $meta,
            'categories' => [$category_id]
        );

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'POST', $data, $headers);

        if (isset($response->id)) {
            return $response;
        } else {
            echo 'Error updating post: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }

    public function trashPost($post_id, $post_type = 'posts') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type . '/' . $post_id;

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'DELETE', null, $headers);

        if (isset($response->id)) {
            return $response;
        } else {
            echo 'Error moving post to trash: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }

    public function restorePost($post_id, $post_type = 'posts') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type . '/' . $post_id;
        $data = array(
            'status' => 'publish'
        );

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'POST', $data, $headers);

        if (isset($response->id)) {
            return $response;
        } else {
            echo 'Error restoring post: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }

    public function deletePostPermanently($post_id, $post_type = 'posts') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type . '/' . $post_id . '?force=true';

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'DELETE', null, $headers);

        if (isset($response->deleted) && $response->deleted === true) {
            return $response;
        } else {
            echo 'Error permanently deleting post: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }

    public function getPostsList($post_type = 'posts') {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $url = $this->site_url . '/wp-json/wp/v2/' . $post_type;

        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $response = $this->sendRequest($url, 'GET', null, $headers);

        if ($response && is_array($response)) {
            $posts_list = [];
            foreach ($response as $post) {
                if (isset($post->id) && isset($post->title->rendered)) {
                    $posts_list[] = [
                        'id' => $post->id,
                        'title' => $post->title->rendered
                    ];
                }
            }
            return $posts_list;
        } else {
            echo 'Error retrieving posts list: ' . (isset($response->message) ? $response->message : 'Unknown error');
            return null;
        }
    }
}

// Initialization and usage
$site_url = 'http://test.xtf.com.ua/';
$username = 'event-manager';
$password = 'password';
$title = 'Post Title :: Fix location 1';
$content = 'Post Content :: Fix location';
$post_type = 'event'; // Specify your post type

$api = new Events_Explorer_Api($site_url, $username, $password);

// Meta data
$dates = [
    'start_date' => (new DateTime('next Monday'))->format('Y-m-d'),
    'end_date' => (new DateTime('next Sunday'))->format('Y-m-d')
];
$meta = [
    'events-location' => 'local',
    'event_subtitle' => 'Subtitle',
    'date_start' => $dates['start_date'],
    'date_end' => $dates['end_date']
];
$category_name = 'local'; // Category name to be ensured

// Create post
$response = $api->createPost($title, $content, $post_type, $meta, $category_name);
if ($response) {
    echo 'Post created successfully: ' . $response->id . "\n";
} else {
    echo 'Error creating post' . "\n";
}

// // Update post
// $post_id = $response->id;
// $new_title = 'Updated Post Title';
// $new_content = 'Updated Post Content';
// $update_response = $api->updatePost($post_id, $new_title, $new_content, $post_type, $meta, $category_name);
// if ($update_response) {
//     echo 'Post updated successfully: ' . $update_response->id . "\n";
// } else {
//     echo 'Error updating post' . "\n";
// }

// // Trash post
// $trash_response = $api->trashPost($post_id, $post_type);
// if ($trash_response) {
//     echo 'Post moved to trash successfully: ' . $trash_response->id . "\n";
// } else {
//     echo 'Error moving post to trash' . "\n";
// }

// // Restore post from trash
// $restore_response = $api->restorePost($post_id, $post_type);
// if ($restore_response) {
//     echo 'Post restored successfully: ' . $restore_response->id . "\n";
// } else {
//     echo 'Error restoring post' . "\n";
// }

// // Permanently delete post
// $delete_response = $api->deletePostPermanently($post_id, $post_type);
// if ($delete_response) {
//     echo 'Post permanently deleted successfully: ' . $delete_response->previous->id . "\n";
// } else {
//     echo 'Error permanently deleting post' . "\n";
// }

// Get posts list
$posts_list = $api->getPostsList($post_type);
if ($posts_list) {
    echo "Posts list:\n";
    foreach ($posts_list as $post) {
        echo 'ID: ' . $post['id'] . ', Title: ' . $post['title'] . "\n";
    }
} else {
    echo 'Error retrieving posts list' . "\n";
}
?>
