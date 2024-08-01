<?php

// admin/class-event-explorer-remote-save-action.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_Explorer_Remote_Save_Action
{
    public function __construct()
    {
        error_log('Event_Explorer_Remote_Save_Action');
        add_action('save_post', array($this, 'save'), 10, 3);
    }

    public function save($post_id, $post, $update)
    {
        error_log('Event_Explorer_Remote_Save_Action::save');
        $remote = new Event_Explorer_Remote_Post($post);

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if ($post->post_type !== 'event') return;

        if ($update) {
            error_log('update: ' . $post->post_type . ' ' . $post->ID);
            $remote->update_post();
        } else {
            error_log('create: ' . $post->post_type . ' ' . $post->ID);
            $remote->publish_post();
        }
    }
}
