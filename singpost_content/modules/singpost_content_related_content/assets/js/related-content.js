jQuery(function ($) {
    'use strict';

    $('.related-content .row').slick({
        dots: false,
        arrows: false,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        mobileFirst: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: 'unslick'
            }
        ]
    });

    $(window).on('resize', function () {
        $('.related-content .row').slick('resize');
    });
});