<?php

// admin/includes/class-event-explorer-wpbakery-sound-carousel-element.php

class Events_Explorer_WPBakery_Sound_Carousel_Element
{
    private $plugin_name;
	private $version;

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
        
        add_action('vc_before_init', array($this, 'sound_carousel'));
        add_shortcode('sound_carousel', array($this, 'sound_carousel_function'));
    }

    // Метод, регистрирующий кастомный элемент в WPBakery
    public function sound_carousel()
    {
        vc_map(array(
            "name" => __("Sound Carousel", $this->plugin_name),
            "base" => "sound_carousel",
            "class" => "",
            "category" => __("Content", $this->plugin_name),
            "params" => array(
                // Раздел Sound Carousel
                array(
                    "type" => "textfield",
                    "heading" => __("Sound Carousel Title", $this->plugin_name),
                    "param_name" => "sound_carousel_title",
                    "description" => __("Enter the title of the sound carousel.", $this->plugin_name)
                ),

                // Раздел Slides
                array(
                    "type" => "param_group",
                    "heading" => __("Slides", $this->plugin_name),
                    "param_name" => "slides",
                    "params" => array(
                        array(
                            "type" => "textfield",
                            "heading" => __("Slide Title", $this->plugin_name),
                            "param_name" => "slide_title",
                            "description" => __("Enter the title of the slide.", $this->plugin_name)
                        ),
                        array(
                            "type" => "attach_image",
                            "heading" => __("Slide Image", $this->plugin_name),
                            "param_name" => "slide_image",
                            "description" => __("Select the image for the slide.", $this->plugin_name)
                        ),
                        array(
                            "type" => "textarea",
                            "heading" => __("Slide Description", $this->plugin_name),
                            "param_name" => "slide_description",
                            "description" => __("Enter the description of the slide.", $this->plugin_name)
                        ),
                        array(
                            "type" => "textfield",
                            "heading" => __("Slide Audio Title", $this->plugin_name),
                            "param_name" => "slide_audio_title",
                            "description" => __("Enter the audio title.", $this->plugin_name)
                        ),
                        array(
                            "type" => "textfield",
                            "heading" => __("Slide Audio URL", $this->plugin_name),
                            "param_name" => "slide_audio_url",
                            "description" => __("Enter the audio URL.", $this->plugin_name)
                        ),
                    ),
                    "description" => __("Add slides to the carousel.", $this->plugin_name)
                ),
            )
        ));
    }

    public function sound_carousel_function($atts)
    {
        $atts = shortcode_atts(array(
            'sound_carousel_title' => '',
            'sound_carousel_description' => '',
            'slides' => '',
            'sound_carousel_title_styles' => '',
            'sound_carousel_description_styles' => '',
            'slide_title_styles' => '',
        ), $atts);

        $slides = vc_param_group_parse_atts($atts['slides']);

        ob_start();
?>
        <div class="container">
            <div class="row">
                <div class="sound-carousel">
                    <?php if (!empty($slides)) : ?>
                        <div class="carousel-wrapper" style="display: flex;">
                            <div class="carousel-titles" style="flex: 1;">
                                <?php if (!empty($atts['sound_carousel_title'])) : ?>
                                    <h2 class="sound-carousel-title" style="<?php echo esc_attr($atts['sound_carousel_title_styles']); ?>">
                                        <?php echo esc_html($atts['sound_carousel_title']); ?>
                                    </h2>
                                <?php endif; ?>

                                <div class="carousel-description-wrapper">
                                    <?php foreach ($slides as $index => $slide) : ?>
                                        <?php $slide_description = isset($slide['slide_description']) ? esc_html($slide['slide_description']) : ''; ?>
                                        <p class="slide-description" data-slide="<?php echo $index; ?>" <?php echo $index === 0 ? '' : ' style="display: none;"'; ?>>
                                            <?php echo $slide_description; ?>
                                        </p>
                                    <?php endforeach; ?>
                                </div>

                                <div class="carousel-titles-list">
                                    <?php foreach ($slides as $index => $slide) : ?>
                                        <?php
                                        $slide_title = isset($slide['slide_title']) ? esc_html($slide['slide_title']) : '';
                                        $slide_audio_title = isset($slide['slide_audio_title']) ? esc_html($slide['slide_audio_title']) : '';
                                        $slide_audio_url = isset($slide['slide_audio_url']) ? esc_url($slide['slide_audio_url']) : '';
                                        ?>
                                        <div class="carousel-title<?php echo $index === 0 ? ' active' : ''; ?>" data-slide-index="<?php echo $index; ?>" style="<?php echo esc_attr($atts['slide_title_styles']); ?>">
                                            <?php echo $slide_title; ?>
                                            <?php if ($slide_audio_title && $slide_audio_url) : ?>
                                                <div class="audio-title" data-audio-url="<?php echo $slide_audio_url; ?>" <?php echo $index === 0 ? '' : ' style="display: none;"'; ?>>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                                                    </svg>
                                                    <?php echo $slide_audio_title; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="carousel-images-wrapper">
                                <div class="carousel-images" style="flex: 2; position: relative;">
                                    <?php foreach ($slides as $slide) : ?>
                                        <?php
                                        $slide_image = isset($slide['slide_image']) ? wp_get_attachment_image($slide['slide_image'], 'full') : '';
                                        $slide_audio_url = isset($slide['slide_audio_url']) ? esc_url($slide['slide_audio_url']) : '';
                                        ?>
                                        <div class="carousel-slide">
                                            <?php echo $slide_image; ?>
                                            <div class="play-icon" data-audio-url="<?php echo $slide_audio_url; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                                                </svg>
                                                <?php $slide_description = isset($slide['slide_description']) ? esc_html($slide['slide_description']) : ''; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="carousel-subnavigation">
                                    <ul>
                                        <?php foreach ($slides as $index => $slide) : ?>
                                            <li class="carousel-title <?php if ($index === 0) echo 'active'; ?>" data-slide-index="<?php echo $index; ?>" <?php echo $index === 0 ? '' : ' style="border-top: 1px solid #737373;"'; ?>>
                                                <span class="index"><?php echo $index + 1; ?></span>
                                                <span class="title">
                                                    <?php echo $slide['slide_title']; ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <audio id="audio-player" style="display:none;"></audio>
            </div>
        </div>

<?php
        return ob_get_clean();
    }
}
