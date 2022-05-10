$(document).ready(function() {
    $(".customer-logos").slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 1500,
        arrows: false,
        dots: false,
        pauseOnHover: false,
        responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 4,
                },
            },
            {
                breakpoint: 520,
                settings: {
                    slidesToShow: 3,
                },
            },
        ],
    });

    $(".slider-bg").slick({
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        arrows: false,
        dots: false,
        pauseOnHover: false,

    })
});