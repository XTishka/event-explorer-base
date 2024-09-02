<?php

class Events_Explorer_Shortcode
{
    public string $location = 'local';
    public int $pages = 0;

    public function __construct()
    {
        add_shortcode('events_explorer', [$this, 'handler']);
        add_action('wp_ajax_ajax_handler', [$this, 'ajax_handler']);
        add_action('wp_ajax_total_posts', [$this, 'total_posts']);
        add_action('wp_ajax_nopriv_ajax_handler', [$this, 'ajax_handler']);
    }

    public function arguments($page = 1, $quantity = 3)
    {
        $current_date = date('Y-m-d');

        return [
            'post_type' => 'event',
            'paged' => $page,
            'posts_per_page' => $quantity,
            'meta_key' => 'date_end',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => 'date_end',
                    'value' => $current_date,
                    'compare' => '>=',
                    'type' => 'DATE'
                ],
            ],
        ];
    }


    public function events($page = 1, $quantity = 3)
    {
        $events = [];
        $query = new WP_Query($this->arguments($page, $quantity));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $location = get_the_terms(get_the_ID(), 'events-location');
                $location_name = isset($location[0]) ? $location[0]->name : '';

                $event = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                    'meta' => get_post_meta(get_the_ID()),
                    'location' => $location_name,
                ];

                $events[] = $event;
            }
            wp_reset_postdata();
        }

        return $events;
    }

    public function handler($atts)
    {
        $atts = shortcode_atts(['location' => $this->location], $atts);
        $this->location = $atts['location'];
        $this->pages = $this->totalPages();
        $events = $this->events();
        return $this->render($events);
    }

    public function ajax_handler()
    {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 3;
        $this->location = isset($_POST['location']) ? $_POST['location'] : 'local';
        $events = $this->events($page, $quantity);

        if (empty($events)) {
            wp_send_json_error('No events found.');
        } else {
            wp_send_json_success($events);
        }
    }

    public function totalPosts(): int
    {
        return count($this->events(1, -1, $this->location));
    }

    public function totalPages(): int
    {
        return ceil($this->totalPosts() / 3);
    }

    public function render($events): string
    {
        ob_start(); ?>
        <div class="container">
            <div class="row">
                <div class="coming-soon-events" data-location="<?php echo $this->location ?>" data-pages="<?php echo $this->pages ?>">
                    <div class="header-block">
                        <div class="navigation">
                            <span class="previous_coming_events" style="display:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                            <?php if ($this->pages > 1) : ?>
                                <span class="next_coming_events">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>

                                <span class="all_events">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="events-list">
                        <?php foreach ($events as $event) : ?>
                            <?php echo $this->display_event($event); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }

    public function display_event($event)
    {
        ob_start(); ?>
        <a href="<?php echo esc_url($event['permalink']); ?>" class="event" data-event="<?php echo esc_attr($event['id']); ?>">
            <div class="image-wrapper">
                <img src="<?php echo esc_url($event['featured_image']); ?>" alt="">
            </div>
            <div class="content-wrapper">
                <h3><?php echo esc_html($event['title']); ?></h3>
                <div class="date details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo $this->display_date($event); ?></span>
                </div>
                <div class="time details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo $this->display_time($event); ?></span>
                </div>

                <?php if ($event['location']) : ?>
                    <div class="location details">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                        </svg>
                        <span><?php echo esc_html($event['location']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="event-hidden-subtitle" style="display:none;"><?php echo esc_html($event['meta']['_event_subtitle'][0] ?? ''); ?></div>
            <div class="event-hidden-content" style="display:none;"><?php echo esc_html($event['meta']['_event_content'][0] ?? ''); ?></div>
        </a>
<?php return ob_get_clean();
    }

    private function display_date($event)
    {
        $start_date = isset($event['meta']['date_start'][0]) ? strtotime($event['meta']['date_start'][0]) : null;
        $end_date = isset($event['meta']['date_end'][0]) ? strtotime($event['meta']['date_end'][0]) : null;

        if (!$start_date || !$end_date) {
            return __('No date found', 'events-api-plugin');
        }

        $start_day = date('d', $start_date);
        $start_month = date_i18n('M', $start_date);
        $start_year = date_i18n('Y', $start_date);

        $end_day = date('d', $end_date);
        $end_month = date_i18n('M', $end_date);
        $end_year = date_i18n('Y', $end_date);

        if ($start_year !== $end_year) {
            return sprintf(
                '%d. %s %d - %d. %s %d',
                $start_day,
                $start_month,
                $start_year,
                $end_day,
                $end_month,
                $end_year
            );
        }

        if ($start_month !== $end_month) {
            return sprintf(
                '%d. %s - %d. %s %d',
                $start_day,
                $start_month,
                $end_day,
                $end_month,
                $end_year
            );
        }

        if ($start_month === $end_month) {
            if ($start_day !== $end_day) {
                return sprintf(
                    '%d - %d. %s %d',
                    $start_day,
                    $end_day,
                    $end_month,
                    $end_year
                );
            }

            if ($start_day === $end_day) {
                return sprintf(
                    '%d. %s %d',
                    $start_day,
                    $end_month,
                    $end_year
                );
            }
        }

        return __('No date found', 'events-api-plugin');
    }

    private function display_time($event)
    {
        $start_time = isset($event['meta']['time_start'][0]) ? $event['meta']['time_start'][0] : '';
        $end_time = isset($event['meta']['time_end'][0]) ? $event['meta']['time_end'][0] : '';

        if (empty($start_time) && empty($end_time)) {
            return __('No time found', 'events-api-plugin');
        }

        if (!empty($start_time) && !empty($end_time)) {
            return sprintf('%s - %s', $start_time, $end_time);
        }

        return $start_time ? $start_time : $end_time;
    }
}
