jQuery(document).ready(function($) {
    // Initialize slick slider
    $('.carousel-images').slick({
        infinite: true,
        centerMode: true,
        centerPadding: '30px',
        adaptiveHeight: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: false,
        arrows: true,
        autoplay: false,
        autoplaySpeed: 3000,
    });

    // Add click event to slide titles for navigation and accordion functionality
    $('.carousel-title').on('click', function() {
        var slideIndex = $(this).data('slide-index');
        var audioTitle = $(this).find('.audio-title');
        
        // Toggle visibility of the audio title
        if (audioTitle.is(':visible')) {
            audioTitle.slideUp();
        } else {
            $('.audio-title').slideUp(); // Hide all other audio titles
            audioTitle.slideDown(); // Show the clicked audio title
        }

        $('.slide-description').hide();
        $('.slide-description[data-slide="' + slideIndex + '"]').show();

        // Navigate to the corresponding slide image
        $('.carousel-images').slick('slickGoTo', slideIndex);
        
        // Update active class for carousel titles
        $('.carousel-title').removeClass('active');
        $(this).addClass('active');
        
        // Update border color in subnavigation
        $('.carousel-subnavigation li').css('border-top', '1px solid #737373');
        $('.carousel-subnavigation li').eq(slideIndex).css('border-top', '1px solid #f8d747');
    });
    
    // Function to handle audio play/pause
    function handleAudioPlayPause(audioUrl) {
        var audioPlayer = $('#audio-player')[0];

        if (audioPlayer.src === audioUrl) {
            if (audioPlayer.paused) {
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
        } else {
            audioPlayer.src = audioUrl;
            audioPlayer.play();
        }
    }

    // Add click event to audio titles for playing/pausing audio
    $('.audio-title').on('click', function(e) {
        e.stopPropagation(); // Prevent triggering the parent click event
        var audioUrl = $(this).data('audio-url');
        handleAudioPlayPause(audioUrl);
    });

    // Add click event to play icons for playing/pausing audio
    $('.play-icon').on('click', function(e) {
        e.stopPropagation(); // Prevent triggering the parent click event
        var audioUrl = $(this).data('audio-url');
        handleAudioPlayPause(audioUrl);
    });
});
