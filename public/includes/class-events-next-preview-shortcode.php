<?php

class Events_Explorer_Next_Preview_Shortcode
{
    public string $location = 'local';

    public function __construct()
    {
        add_shortcode('events_explorer_next_preview', [$this, 'handler']);
    }

    public function arguments()
    {
        $current_date = date('Y-m-d');

        return [
            'post_type' => 'event',
            'posts_per_page' => 1,
            'meta_key' => 'date_start',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => 'date_start',
                    'value' => $current_date,
                    'compare' => '>=',
                    'type' => 'DATE'
                ],
            ],
        ];
    }


    public function events()
    {
        $events = [];
        $query = new WP_Query($this->arguments());

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $location = get_the_terms(get_the_ID(), 'events-location');
                $location_name = isset($location[0]) ? $location[0]->name : '';

                $event = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'content' => get_the_content(),
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
        $events = $this->events();
        return $this->render($events);
    }

    public function render($events): string
    {
        ob_start(); ?>

        <div class="container">
            <div class="row">
                <div class="event-explorer-next-event">
                    <?php foreach ($events as $event) : ?>
                        <?php echo $this->display_event($event); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }

    public function display_event($event)
    {
        ob_start(); ?>
        <div class="image-wrapper">
            <img src="<?php echo esc_url($event['featured_image']); ?>" alt="">
        </div>
        <div class="content-wrapper">
            <h3><?php echo esc_html($event['meta']['next_preview_title'][0]); ?></h3>
            <p><?php echo esc_html($event['meta']['next_preview_description'][0] ?? ''); ?></p>
            <ul>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                    </svg>
                    <?php echo $this->display_date($event); ?>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                    <?php echo $this->display_time($event); ?>
                </li>
            </ul>
            <a href="<?php echo $event['permalink']; ?>" class="button button-secondary">More</a>
        </div>
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
