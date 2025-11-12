/*JQuery code for testimonial slider*/

/*jQuery(document).ready(function ($) {
    $('.ts-slider').slick({
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        adaptiveHeight: true,
    });
}); */

jQuery(document).ready(function ($) {
    $('.ts-slider').slick({
        slidesToShow: 3,        // ✅ Show 3 testimonials at a time
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        dots: true,
        arrows: false,
        infinite: true,
        responsive: [
            {
                breakpoint: 1024, // For tablets or medium screens
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 768, // For mobile devices
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
});

