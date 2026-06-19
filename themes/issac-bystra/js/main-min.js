/*
███    ██  █████  ██    ██ ██████   █████  ██████
████   ██ ██   ██ ██    ██ ██   ██ ██   ██ ██   ██
██ ██  ██ ███████ ██    ██ ██████  ███████ ██████
██  ██ ██ ██   ██  ██  ██  ██   ██ ██   ██ ██   ██
██   ████ ██   ██   ████   ██████  ██   ██ ██   ██


██████  ██████   ██████  ██████  ██████   ██████  ██     ██ ███    ██
██   ██ ██   ██ ██    ██ ██   ██ ██   ██ ██    ██ ██     ██ ████   ██
██   ██ ██████  ██    ██ ██████  ██   ██ ██    ██ ██  █  ██ ██ ██  ██
██   ██ ██   ██ ██    ██ ██      ██   ██ ██    ██ ██ ███ ██ ██  ██ ██
██████  ██   ██  ██████  ██      ██████   ██████   ███ ███  ██   ████

Navbar dropdown behavior:
- Desktop: hover to open dropdown, click navigates to parent page
- Mobile: first tap opens dropdown, second tap navigates to parent page
*/
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var dropdowns = document.querySelectorAll('.navbar-nav .dropdown');
        var breakpoint = 1200; // Bootstrap xl breakpoint (matches navbar-expand-xl)

        // Desktop: Hover behavior
        dropdowns.forEach(function (dropdown) {
            dropdown.addEventListener('mouseenter', function () {
                if (window.innerWidth >= breakpoint) {
                    var menu = this.querySelector('.dropdown-menu');
                    if (menu) menu.classList.add('show');
                }
            });
            dropdown.addEventListener('mouseleave', function () {
                if (window.innerWidth >= breakpoint) {
                    var menu = this.querySelector('.dropdown-menu');
                    if (menu) menu.classList.remove('show');
                }
            });
        });

        // Mobile: Two-tap behavior (first tap opens, second tap navigates)
        document.querySelectorAll('.navbar-nav .dropdown > .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < breakpoint) {
                    var dropdown = this.closest('.dropdown');
                    var menu = dropdown.querySelector('.dropdown-menu');

                    // If dropdown is NOT already open, open it and prevent navigation
                    if (!menu.classList.contains('show')) {
                        e.preventDefault();
                        // Close other open dropdowns first
                        document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(function (openMenu) {
                            if (openMenu !== menu) {
                                openMenu.classList.remove('show');
                            }
                        });
                        menu.classList.add('show');
                    }
                    // If already open, the click proceeds normally (navigates to parent page)
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.navbar-nav .dropdown')) {
                document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(function (menu) {
                    menu.classList.remove('show');
                });
            }
        });

        // Close all dropdowns when mobile navbar collapse is hidden
        var mainMenuMobile = document.getElementById('main-menu-mobile');
        if (mainMenuMobile) {
            mainMenuMobile.addEventListener('hide.bs.collapse', function () {
                document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(function (menu) {
                    menu.classList.remove('show');
                });
            });
        }
    });
})();


(function ($) {
    // Create a global array to track all open infowindows
    var openInfoWindows = [];
    // Variable to store the currently selected village ID
    var selectedVillageId = null;

    // Initialize the map
    function initMap($el) {
        // Find marker elements within map element
        var $markers = $el.find('.marker');

        // Create map options
        var mapArgs = {
            zoom: $el.data('zoom') || 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        // Create map
        var map = new google.maps.Map($el[0], mapArgs);

        // Add markers
        map.markers = [];
        $markers.each(function () {
            initMarker($(this), map);
        });

        // Center the map
        centerMap(map);

        // Store the map instance on the element for later access
        $el.data('map', map);

        return map;
    }

    // Function to close all open infowindows
    function closeAllInfoWindows() {
        for (var i = 0; i < openInfoWindows.length; i++) {
            openInfoWindows[i].close();
        }
        openInfoWindows = [];
    }

    // Create a marker for each map element
    function initMarker($marker, map) {
        var lat = $marker.data('lat');
        var lng = $marker.data('lng');
        var latLng = {
            lat: parseFloat(lat),
            lng: parseFloat(lng)
        };
        var villageId = $marker.data('village-id'); // Get the village ID from data attribute

        // Create marker instance
        var marker = new google.maps.Marker({
            position: latLng,
            map: map
        });

        map.markers.push(marker);

        // If marker has content, add an info window
        if ($marker.html()) {
            var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
            });

            // Store the infowindow with the marker for later access
            marker.infowindow = infowindow;

            marker.addListener('click', function () {
                // Close all open infowindows first
                closeAllInfoWindows();

                // Then open this infowindow
                infowindow.open(map, marker);

                // Add this infowindow to our tracking array
                openInfoWindows.push(infowindow);

                // Filter village list by ID
                if (villageId) {
                    filterVillageById(villageId);
                }
            });

            // Auto-open the info window if this is a display village
            if ($('body').hasClass('single-display_village')) {
                infowindow.open(map, marker);
                // Add this infowindow to our tracking array
                openInfoWindows.push(infowindow);
            }
        }
    }

    // Center the map to include all markers
    function centerMap(map) {
        var bounds = new google.maps.LatLngBounds();
        map.markers.forEach(function (marker) {
            bounds.extend(marker.position);
        });
        if (map.markers.length == 1) {
            map.setCenter(bounds.getCenter());
            map.setZoom(map.zoom);
        } else {
            map.fitBounds(bounds);
        }
    }

    // Filter villages by ID
    function filterVillageById(id) {
        // Store the selected village ID
        selectedVillageId = id;

        // Hide all villages in the loop
        $('.village-card').hide();

        // Show only the selected village
        $('.village-card[data-village-id="' + id + '"]').show();

        // Show the reset button
        $('#show-all-villages').fadeIn();
    }

    // Function to reset the filter and show all villages
    function showAllVillages() {
        // Clear selected ID
        selectedVillageId = null;

        // Show all villages
        $('.village-card').show();

        // Hide the reset button if we're showing all
        $('#show-all-villages').fadeOut();

        // Close any open info windows
        closeAllInfoWindows();
    }

    // Initialize maps on page load
    $(document).ready(function () {
        $('.acf-map').each(function () {
            initMap($(this));
        });

        // Add click handler for "Show All Villages" button
        $(document).on('click', '#show-all-villages', function (e) {
            e.preventDefault();
            showAllVillages();
        });

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Expose functions globally
    window.mapFunctions = {
        showAllVillages: showAllVillages
    };
})(jQuery);







/*
 █████   ██████  ██████  ██████  ██████  ██████  ██  ██████  ███    ██
██   ██ ██      ██      ██    ██ ██   ██ ██   ██ ██ ██    ██ ████   ██
███████ ██      ██      ██    ██ ██████  ██   ██ ██ ██    ██ ██ ██  ██
██   ██ ██      ██      ██    ██ ██   ██ ██   ██ ██ ██    ██ ██  ██ ██
██   ██  ██████  ██████  ██████  ██   ██ ██████  ██  ██████  ██   ████


*/
// Toggle plus minus icon on show hide of collapse element
$(".collapse").on('show.bs.collapse', function () {
    $(this).prev(".accordion-header").find(".plusMinus").addClass("rotate");
    $(this).prev(".accordion-header").find(".plusMinusSub").addClass("rotate");
}).on('hide.bs.collapse', function () {
    $(this).prev(".accordion-header").find(".plusMinus").removeClass("rotate");
    $(this).prev(".accordion-header").find(".plusMinusSub").removeClass("rotate");
});


/*
███████ ██     ██ ██ ██████  ███████ ██████
██      ██     ██ ██ ██   ██ ██      ██   ██
███████ ██  █  ██ ██ ██████  █████   ██████
     ██ ██ ███ ██ ██ ██      ██      ██   ██
███████  ███ ███  ██ ██      ███████ ██   ██


*/

jQuery(document).ready(function () {
    if (typeof Swiper !== 'undefined') {

        /*
        ██   ██ ███████ ██████   ██████      ███████ ██      ██ ██████  ███████ ██████
        ██   ██ ██      ██   ██ ██    ██     ██      ██      ██ ██   ██ ██      ██   ██
        ███████ █████   ██████  ██    ██     ███████ ██      ██ ██   ██ █████   ██████
        ██   ██ ██      ██   ██ ██    ██          ██ ██      ██ ██   ██ ██      ██   ██
        ██   ██ ███████ ██   ██  ██████      ███████ ███████ ██ ██████  ███████ ██   ██

        Hero slider with fade effect, autoplay, and SVG decorations
        Uses .swiper-hero class to avoid conflicts with generic .swiper
        */
        document.querySelectorAll('.swiper-hero').forEach(function (heroElement) {
            var heroSlider = new Swiper(heroElement, {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 800,
                pagination: {
                    el: heroElement.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                navigation: {
                    nextEl: heroElement.querySelector('.swiper-button-next'),
                    prevEl: heroElement.querySelector('.swiper-button-prev'),
                },
                keyboard: {
                    enabled: true,
                    onlyInViewport: true,
                },
                observer: true,
                observeParents: true,
            });
        });

        // Nav hero slider with slide transition + autoplay
        document.querySelectorAll('.slider-nav-hero').forEach(function (sliderElement) {
            var navHeroSlider = new Swiper(sliderElement, {
                loop: true,
                autoplay: {
                    delay: 5000,
                    reverseDirection: true,
                    disableOnInteraction: false,
                },
                speed: 800,
                keyboard: {
                    enabled: true,
                    onlyInViewport: true,
                },
                observer: true,
                observeParents: true,
                preloadImages: true,
                watchSlidesProgress: true,
            });
        });

        /*
        ███████ ██      ██ ██████  ███████ ██████      ████████  █████  ██████  ███████
        ██      ██      ██ ██   ██ ██      ██   ██        ██    ██   ██ ██   ██ ██
        ███████ ██      ██ ██   ██ █████   ██████         ██    ███████ ██████  ███████
             ██ ██      ██ ██   ██ ██      ██   ██        ██    ██   ██ ██   ██      ██
        ███████ ███████ ██ ██████  ███████ ██   ██        ██    ██   ██ ██████  ███████

        Swiper Thumbs – pill nav + content slider with GSAP entrance animation
        */
        document.querySelectorAll('.slider-tabs').forEach(function (block) {
            var navEl = block.querySelector('.swiper-slider-tabs-nav');
            var contentEl = block.querySelector('.swiper-slider-tabs-content');
            if (!navEl || !contentEl) return;

            // GSAP slide entrance animation
            function animateSlide(slideEl) {
                if (typeof gsap === 'undefined' || !slideEl) return;
                var items = slideEl.querySelectorAll('[data-anim]');
                if (!items.length) return;
                gsap.fromTo(items,
                    { autoAlpha: 0, y: 12 },
                    { autoAlpha: 1, y: 0, duration: 0.4, stagger: 0.05 }
                );
            }

            // Nav Swiper (pills)
            var navSwiper = new Swiper(navEl, {
                slidesPerView: 'auto',
                spaceBetween: 16,
                freeMode: true,
                watchSlidesProgress: true,
                slideToClickedSlide: true,
                keyboard: { enabled: true, onlyInViewport: true },
                breakpoints: {
                    1024: {
                        allowTouchMove: false,
                        freeMode: false
                    }
                }
            });

            // Content Swiper (panels)
            var contentSwiper = new Swiper(contentEl, {
                autoHeight: true,
                spaceBetween: 24,
                keyboard: { enabled: true, onlyInViewport: true },
                thumbs: { swiper: navSwiper }
            });

            // Keep aria attributes in sync
            function updateA11y(activeIndex) {
                var pills = block.querySelectorAll('.slider-tabs__pill');
                var panels = block.querySelectorAll('.slider-tabs__panel');
                pills.forEach(function (pill, i) {
                    var isActive = (i === activeIndex);
                    pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    pill.setAttribute('tabindex', isActive ? '0' : '-1');
                    pill.classList.toggle('slider-tabs__pill--active', isActive);
                });
                panels.forEach(function (panel, i) {
                    if (i === activeIndex) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                });
            }

            // Pill click → slide to content
            block.querySelectorAll('.slider-tabs__pill').forEach(function (pill, idx) {
                pill.addEventListener('click', function () {
                    contentSwiper.slideTo(idx);
                });
            });

            // Keyboard arrow navigation between pills
            block.querySelectorAll('.slider-tabs__pill').forEach(function (pill, idx, pills) {
                pill.addEventListener('keydown', function (e) {
                    var newIdx = null;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        newIdx = (idx + 1) % pills.length;
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        newIdx = (idx - 1 + pills.length) % pills.length;
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        newIdx = 0;
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        newIdx = pills.length - 1;
                    }
                    if (newIdx !== null) {
                        contentSwiper.slideTo(newIdx);
                        pills[newIdx].focus();
                    }
                });
            });

            // Update on slide change
            contentSwiper.on('slideChangeTransitionEnd', function () {
                updateA11y(contentSwiper.activeIndex);
                animateSlide(contentSwiper.slides[contentSwiper.activeIndex]);
            });

            // Initial state
            updateA11y(0);
            animateSlide(contentSwiper.slides[0]);
        });


        /*
         ██████  ███████ ███    ██ ███████ ██████  ██  ██████     ███████ ██      ██ ██████  ███████ ██████
        ██       ██      ████   ██ ██      ██   ██ ██ ██          ██      ██      ██ ██   ██ ██      ██   ██
        ██   ███ █████   ██ ██  ██ █████   ██████  ██ ██          ███████ ██      ██ ██   ██ █████   ██████
        ██    ██ ██      ██  ██ ██ ██      ██   ██ ██ ██               ██ ██      ██ ██   ██ ██      ██   ██
         ██████  ███████ ██   ████ ███████ ██   ██ ██  ██████     ███████ ███████ ██ ██████  ███████ ██   ██
        */
        // Initialize the generic swiper (excludes hero and slider-tabs to avoid double init)
        var swiper = new Swiper('.swiper:not(.swiper-hero):not(.slider-nav-hero):not(.swiper-slider-tabs-nav):not(.swiper-slider-tabs-content):not(.swiper-testimonials)', {
            loop: true,
            // Uncomment the next line if you want autoplay
            // autoplay: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },
            observer: true,
            observeParents: true,
        });

        // Initialize the testimonials swiper
        document.querySelectorAll('.swiper-testimonials').forEach(function (el) {
            var wrapper = el.querySelector('.swiper-wrapper');
            var slides = wrapper ? Array.from(wrapper.querySelectorAll('.swiper-slide')) : [];
            var slideCount = slides.length;

            // Swiper 11 won't loop when slides <= slidesPerView (3 on desktop), so autoplay stops.
            // Clone slides until we have enough for loop mode (2× the largest slidesPerView).
            var minSlidesForLoop = 6;

            if (slideCount && slideCount < minSlidesForLoop) {
                for (var i = slideCount; i < minSlidesForLoop; i++) {
                    wrapper.appendChild(slides[i % slideCount].cloneNode(true));
                }
            }

            new Swiper(el, {
                slidesPerView: 1,
                spaceBetween: 24,
                loop: true,
                preventClicks: false,
                preventClicksPropagation: false,
                noSwipingSelector: 'a, button',
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: el.closest('.testimonials-slider-wrap').querySelector('.swiper-button-next'),
                    prevEl: el.closest('.testimonials-slider-wrap').querySelector('.swiper-button-prev'),
                },
                breakpoints: {
                    768: {
                        slidesPerView: 1,
                        spaceBetween: 20,
                    },
                    992: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    1200: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                },
            });
        });

    } else {
        console.error('Swiper is not defined.');
    }
});






/*
████████  ██████   ██████   ██████  ██      ███████
   ██    ██    ██ ██       ██       ██      ██
   ██    ██    ██ ██   ███ ██   ███ ██      █████
   ██    ██    ██ ██    ██ ██    ██ ██      ██
   ██     ██████   ██████   ██████  ███████ ███████


███    ███ ███████ ███    ██ ██    ██
████  ████ ██      ████   ██ ██    ██
██ ████ ██ █████   ██ ██  ██ ██    ██
██  ██  ██ ██      ██  ██ ██ ██    ██
██      ██ ███████ ██   ████  ██████


*/

/* Initiate Hamburger icon animation */
jQuery(document).ready(function () {

    jQuery('.first-button').on('click', function () {

        jQuery('.animated-icon1').toggleClass('open');
    });
    jQuery('.second-button').on('click', function () {

        jQuery('.animated-icon2').toggleClass('open');
    });
    jQuery('.third-button').on('click', function () {

        jQuery('.animated-icon3').toggleClass('open');
    });
});


/*
███    ███  █████  ████████  ██████ ██   ██ ██   ██ ███████ ██  ██████  ██   ██ ████████
████  ████ ██   ██    ██    ██      ██   ██ ██   ██ ██      ██ ██       ██   ██    ██
██ ████ ██ ███████    ██    ██      ███████ ███████ █████   ██ ██   ███ ███████    ██
██  ██  ██ ██   ██    ██    ██      ██   ██ ██   ██ ██      ██ ██    ██ ██   ██    ██
██      ██ ██   ██    ██     ██████ ██   ██ ██   ██ ███████ ██  ██████  ██   ██    ██


*/
jQuery(document).ready(function ($) {
    // $() will work as an alias for jQuery() inside of this function

    $(document).ready(function () {
        $('.fosforos').matchHeight();
        $('.cerillas').matchHeight();
    })

});


/*
 ██████   ██████   ██████   ██████  ██      ███████     ███    ███  █████  ██████
██       ██    ██ ██    ██ ██       ██      ██          ████  ████ ██   ██ ██   ██
██   ███ ██    ██ ██    ██ ██   ███ ██      █████       ██ ████ ██ ███████ ██████
██    ██ ██    ██ ██    ██ ██    ██ ██      ██          ██  ██  ██ ██   ██ ██
 ██████   ██████   ██████   ██████  ███████ ███████     ██      ██ ██   ██ ██


██       █████  ███████ ██    ██     ██       ██████   █████  ██████
██      ██   ██    ███   ██  ██      ██      ██    ██ ██   ██ ██   ██
██      ███████   ███     ████       ██      ██    ██ ███████ ██   ██
██      ██   ██  ███       ██        ██      ██    ██ ██   ██ ██   ██
███████ ██   ██ ███████    ██        ███████  ██████  ██   ██ ██████


*/

/* document.addEventListener("DOMContentLoaded", function () {
    // Select the map placeholder element
    var mapPlaceholder = document.getElementById('map-placeholder');

    // Create an Intersection Observer to observe when the map placeholder enters the viewport
    var observer = new IntersectionObserver(function (entries) {
        // If the placeholder is in the viewport, load the iframe and stop observing
        if (entries[0].isIntersecting) {
            loadMapIframe();
            observer.disconnect();
        }
    });

    // Start observing the map placeholder
    observer.observe(mapPlaceholder);
});

function loadMapIframe() {
    // Get the iframe code from the localized data
    var iframeCode = acfData.iframe_code;

    // Parse the iframe code to extract the src attribute
    var parser = new DOMParser();
    var doc = parser.parseFromString(iframeCode, 'text/html');
    var iframeSrc = doc.querySelector('iframe').getAttribute('src');

    // Create a new iframe element
    var iframe = document.createElement('iframe');
    iframe.src = iframeSrc;
    iframe.width = "100%";
    iframe.height = "100%";
    iframe.style.border = "0";
    iframe.allowFullscreen = "";
    iframe.loading = "lazy";
    iframe.referrerPolicy = "no-referrer-when-downgrade";

    // Replace the map placeholder with the new iframe
    document.getElementById('map-placeholder').replaceWith(iframe);
} */










/*
███████ ██      ██ ██████  ███████ ██████
██      ██      ██ ██   ██ ██      ██   ██
███████ ██      ██ ██   ██ █████   ██████
     ██ ██      ██ ██   ██ ██      ██   ██
███████ ███████ ██ ██████  ███████ ██   ██


██      ██  ██████  ██   ██ ████████  ██████   █████  ██      ██      ███████ ██████  ██    ██
██      ██ ██       ██   ██    ██    ██       ██   ██ ██      ██      ██      ██   ██  ██  ██
██      ██ ██   ███ ███████    ██    ██   ███ ███████ ██      ██      █████   ██████    ████
██      ██ ██    ██ ██   ██    ██    ██    ██ ██   ██ ██      ██      ██      ██   ██    ██
███████ ██  ██████  ██   ██    ██     ██████  ██   ██ ███████ ███████ ███████ ██   ██    ██


*/


/*
 ██        ███████ ██      ██  ██████ ██   ██
███        ██      ██      ██ ██      ██  ██
 ██        ███████ ██      ██ ██      █████
 ██             ██ ██      ██ ██      ██  ██
 ██ ██     ███████ ███████ ██  ██████ ██   ██


*/


$(document).ready(function ($) {
    $('.slickGallery').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 0,
        speed: 7000,
        cssEase: 'linear',
        arrows: false,
        draggable: true,
        infinite: true,
        pauseOnFocus: false,
        pauseOnHover: false,
        swipe: false,
        touchMove: false,
        responsive: [{
            breakpoint: 1600,
            settings: {
                slidesToShow: 5,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 1366,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 3.0,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
});






/*
██████     ██      ██  ██████  ██   ██ ████████  ██████   █████  ██      ██      ███████ ██████  ██    ██
     ██    ██      ██ ██       ██   ██    ██    ██       ██   ██ ██      ██      ██      ██   ██  ██  ██
 █████     ██      ██ ██   ███ ███████    ██    ██   ███ ███████ ██      ██      █████   ██████    ████
██         ██      ██ ██    ██ ██   ██    ██    ██    ██ ██   ██ ██      ██      ██      ██   ██    ██
███████ ██ ███████ ██  ██████  ██   ██    ██     ██████  ██   ██ ███████ ███████ ███████ ██   ██    ██


*/
document.querySelectorAll('[id^="gallery-container-slider-"]').forEach(function (container) {
    lightGallery(container, {
        thumbnail: true,
        speed: 500,
        preload: 4,
        showCloseIcon: true,
        download: false,
        mode: 'lg-fade',
        plugins: [lgZoom, lgThumbnail, lgAutoplay],
        mobileSettings: {
            controls: false,
            showCloseIcon: true,
            download: false
        }
    });
});




/*
███    ███  █████  ██████   ██████  ██    ██ ███████ ███████
████  ████ ██   ██ ██   ██ ██    ██ ██    ██ ██      ██
██ ████ ██ ███████ ██████  ██    ██ ██    ██ █████   █████
██  ██  ██ ██   ██ ██   ██ ██ ▄▄ ██ ██    ██ ██      ██
██      ██ ██   ██ ██   ██  ██████   ██████  ███████ ███████
                               ▀▀
ticker marquee
*/

function initPresentationMarquees() {
    if (typeof gsap === 'undefined') return;

    const marquees = document.querySelectorAll('.presentation-marquee__track');

    marquees.forEach(track => {
        const marquee = track.closest('.presentation-marquee');
        const groups = track.querySelectorAll('.presentation-marquee__group');

        if (!marquee || groups.length < 2 || marquee.classList.contains('is-gsap-animated')) return;

        // Keep each duplicated group wider than the visible marquee. This prevents
        // gaps when the ticker text is short or the viewport is wide.
        let clonePasses = 0;
        while (groups[0].offsetWidth < marquee.offsetWidth * 1.2 && clonePasses < 10) {
            groups.forEach(group => {
                Array.from(group.children).forEach(item => {
                    group.appendChild(item.cloneNode(true));
                });
            });

            clonePasses++;
        }

        marquee.classList.add('is-gsap-animated');
        gsap.set(track, { xPercent: 0 });

        // Base marquee loop:
        // - xPercent: -50 moves the track by one duplicated group, then repeats.
        // - duration controls the normal loop speed. Increase it for a slower
        //   marquee; decrease it for a faster marquee.
        // - ease: 'none' keeps the marquee moving at a constant linear speed.
        const marqueeTween = gsap.to(track, {
            xPercent: -50,
            duration: 72,
            ease: 'none',
            repeat: -1
        });

        const items = track.querySelectorAll('.presentation-marquee__item');

        marquee.addEventListener('mouseenter', function () {
            // Hover slow-down:
            // timeScale changes the playback rate of the existing infinite tween.
            // 1 = normal speed, 0.5 = half speed, 0.25 = quarter speed, 2 = double speed.
            // The duration here is only how long GSAP takes to ease from the current
            // playback rate to the new timeScale; it is not the marquee loop duration.
            gsap.to(marqueeTween, {
                timeScale: 0.25,
                duration: 0.45,
                ease: 'power2.out'
            });

            // Hover distortion:
            // skewX controls the angled text distortion. Use a larger negative or
            // positive value for a stronger lean, or 0 to remove the angle.
            // yPercent nudges each item vertically as part of the hover effect.
            // stagger offsets each item's distortion timing for a ripple feel.
            gsap.to(items, {
                skewX: -8,
                yPercent: -4,
                duration: 0.45,
                ease: 'power2.out',
                stagger: 0.015
            });
        });

        marquee.addEventListener('mouseleave', function () {
            // Return the marquee to its original playback rate. Again, duration is
            // the transition time into timeScale: 1, not the loop speed itself.
            gsap.to(marqueeTween, {
                timeScale: 1,
                duration: 0.65,
                ease: 'power2.out'
            });

            // Reset the hover distortion. The elastic ease gives the text a small
            // spring-back; use 'power2.out' for a cleaner, less bouncy reset.
            gsap.to(items, {
                skewX: 0,
                yPercent: 0,
                duration: 0.65,
                ease: 'elastic.out(1, 0.6)',
                stagger: 0.01
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(initPresentationMarquees);
    } else {
        initPresentationMarquees();
    }
});


/*
 ██████  ███████  █████  ██████
██       ██      ██   ██ ██   ██
██   ███ ███████ ███████ ██████
██    ██      ██ ██   ██ ██
 ██████  ███████ ██   ██ ██


███████ ██   ██      ██████  █████  ██████  ██████
██       ██ ██      ██      ██   ██ ██   ██ ██   ██
█████     ███       ██      ███████ ██████  ██   ██
██       ██ ██      ██      ██   ██ ██   ██ ██   ██
██      ██   ██      ██████ ██   ██ ██   ██ ██████


*/
document.addEventListener('DOMContentLoaded', function () {
    // GSAP hover effect initialization
    const cards = document.querySelectorAll('.card-fx');
    cards.forEach(card => {
        const tl = gsap.timeline({
            paused: true
        });
        tl.to(card, {
            duration: 0.5,
            rotationY: 10,
            rotationX: -10,
            scale: 1.05,
            ease: "power1.out"
        });

        // Event listeners for hover effect
        card.addEventListener('mouseenter', () => tl.play());
        card.addEventListener('mouseleave', () => tl.reverse());

        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            gsap.to(card, {
                duration: 0.5,
                rotationY: x * 20,
                rotationX: -y * 20,
                ease: "power1.out"
            });
        });
    });
});


/*
████████ ███████  █████  ███    ███       ██       ███████ ███████ ██████  ██    ██ ██  ██████ ███████ ███████
   ██    ██      ██   ██ ████  ████      ██ ██     ██      ██      ██   ██ ██    ██ ██ ██      ██      ██
   ██    █████   ███████ ██ ████ ██       ███      ███████ █████   ██████  ██    ██ ██ ██      █████   ███████
   ██    ██      ██   ██ ██  ██  ██      ██ ██          ██ ██      ██   ██  ██  ██  ██ ██      ██           ██
   ██    ███████ ██   ██ ██      ██     ██   ██    ███████ ███████ ██   ██   ████   ██  ██████ ███████ ███████

 ██████  █████  ██████  ██████      ██   ██  ██████  ██    ██ ███████ ██████
██      ██   ██ ██   ██ ██   ██     ██   ██ ██    ██ ██    ██ ██      ██   ██
██      ███████ ██████  ██   ██     ███████ ██    ██ ██    ██ █████   ██████
██      ██   ██ ██   ██ ██   ██     ██   ██ ██    ██  ██  ██  ██      ██   ██
 ██████ ██   ██ ██   ██ ██████      ██   ██  ██████    ████   ███████ ██   ██

Card image hover effect for Team and Services blocks
- Image scales down (0.96) on hover to avoid border clipping
- Subtle saturation and brightness boost for visual "pop"
- Smooth easing with GSAP power2.out
- Works on: .team-card-wrapper, .service-card-wrapper
*/
document.addEventListener('DOMContentLoaded', function () {
    // Check if GSAP is available
    if (typeof gsap === 'undefined') return;

    // Card hover effect - applies to both Team and Services cards
    // Select all card wrappers that should have the hover effect
    const cardWrappers = document.querySelectorAll('.team-card-wrapper, .service-card-wrapper');

    cardWrappers.forEach(card => {
        // Find the image inside the card
        const image = card.querySelector('.img-cards');
        if (!image) return;

        // Create a paused GSAP timeline for the hover animation
        const hoverTl = gsap.timeline({ paused: true });

        // Define the hover animation:
        // - scale: 0.96 - shrink slightly to avoid border clipping
        // - saturate: 1.1 - boost color saturation
        // - brightness: 1.03 - slight brightness increase
        hoverTl.to(image, {
            duration: 0.4,
            scale: 0.96,
            filter: 'saturate(1.1) brightness(1.03)',
            ease: "power2.out"
        });

        // Mouse enter: play the animation forward
        card.addEventListener('mouseenter', () => {
            hoverTl.play();
        });

        // Mouse leave: reverse the animation back to original state
        card.addEventListener('mouseleave', () => {
            hoverTl.reverse();
        });
    });
});


/*
 ██████  ██████  ███    ██ ████████ ███████ ███    ██ ████████     ██████   █████  ███████ ██  ██████
██      ██    ██ ████   ██    ██    ██      ████   ██    ██        ██   ██ ██   ██ ██      ██ ██
██      ██    ██ ██ ██  ██    ██    █████   ██ ██  ██    ██        ██████  ███████ ███████ ██ ██
██      ██    ██ ██  ██ ██    ██    ██      ██  ██ ██    ██        ██   ██ ██   ██      ██ ██ ██
 ██████  ██████  ██   ████    ██    ███████ ██   ████    ██        ██████  ██   ██ ███████ ██  ██████

Animated image mask for Content Basic blocks
*/
document.addEventListener('DOMContentLoaded', function () {
    var masks = document.querySelectorAll('.js-content-basic-mask');

    if (!masks.length) return;

    if (!('IntersectionObserver' in window)) {
        masks.forEach(function (mask) {
            mask.classList.add('is-visible');
        });

        return;
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion) {
        masks.forEach(function (mask) {
            mask.classList.add('is-visible');
        });

        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            entry.target.classList.toggle('is-visible', entry.isIntersecting);
        });
    }, {
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.2
    });

    masks.forEach(function (mask) {
        mask.classList.add('content-basic__masked-svg--ready');
        observer.observe(mask);
    });
});



/*
███████ ██      ██ ██████  ███████ ██████
██      ██      ██ ██   ██ ██      ██   ██
███████ ██      ██ ██   ██ █████   ██████
     ██ ██      ██ ██   ██ ██      ██   ██
███████ ███████ ██ ██████  ███████ ██   ██


 ██████  █████  ██████  ██████  ███████
██       ██   ██ ██   ██ ██   ██ ██
██   ███ ███████ ██████  ██   ██ ███████
██    ██ ██   ██ ██   ██ ██   ██      ██
 ██████ ██   ██ ██   ██ ██████  ███████


*/


/* jQuery(document).ready(function ($) {
    $('.slider-cards').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 6,
        slidesToScroll: 1,

        autoplay: true, // Enable auto-scrolling
        autoplaySpeed: 3000, // Set the speed of auto-scrolling (in milliseconds)
        cssEase: 'linear',
        arrows: true,
        centerMode: true,
        infinite: true,
        pauseOnHover: true,
        swipe: false,
        touchMove: false,

        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                infinite: true,
                dots: true
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
        ]
    });
}); */


jQuery(document).ready(function ($) {
    var $sliderDoctors = $('.slider-doctors');
    var totalDoctorSlides = $sliderDoctors.children().length;

    $sliderDoctors.slick({
        dots: false,
        infinite: true,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 1,
        gap: '200px',

        autoplay: totalDoctorSlides > 3,
        autoplaySpeed: 3000,
        cssEase: 'linear',
        arrows: true,
        pauseOnHover: true,
        swipe: true,
        touchMove: true,

        responsive: [
            {
                breakpoint: 2560,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    autoplay: totalDoctorSlides > 2,
                    dots: true
                }
            },
            {
                breakpoint: 2048,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    autoplay: totalDoctorSlides > 2,
                    dots: true
                }
            },
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: totalDoctorSlides > 2,
                    dots: true
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: totalDoctorSlides > 2,
                    dots: true
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: totalDoctorSlides > 1,
                    dots: true
                }
            }
        ]
    });
});

jQuery(document).ready(function ($) {
    // Reference the slider element
    var $slider = $('.slider-cards');
    var totalSlides = $slider.children().length; // Count the total number of slides

    $slider.slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 6,
        slidesToScroll: 1,

        autoplay: totalSlides > 6, // Enable autoplay only if total slides > slidesToShow
        autoplaySpeed: 3000, // Set the speed of auto-scrolling (in milliseconds)
        cssEase: 'linear',
        arrows: true,
        pauseOnHover: true,
        swipe: false,
        touchMove: false,

        responsive: [
            {
                breakpoint: 1320,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 5, // Recheck autoplay for this breakpoint
                    dots: true
                }
            },
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 4, // Recheck autoplay for this breakpoint
                    dots: true
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 3, // Recheck autoplay for this breakpoint
                    dots: true
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 2, // Recheck autoplay for this breakpoint
                    dots: true
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 2 // Recheck autoplay for this breakpoint
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: totalSlides > 1 // Recheck autoplay for this breakpoint
                }
            }
        ]
    });
});



/*
 ██████  ███████  █████  ██████
██       ██      ██   ██ ██   ██
██   ███ ███████ ███████ ██████
██    ██      ██ ██   ██ ██
 ██████  ███████ ██   ██ ██


██████   █████  ██████   █████  ██      ██       █████  ██   ██
██   ██ ██   ██ ██   ██ ██   ██ ██      ██      ██   ██  ██ ██
██████  ███████ ██████  ███████ ██      ██      ███████   ███
██      ██   ██ ██   ██ ██   ██ ██      ██      ██   ██  ██ ██
██      ██   ██ ██   ██ ██   ██ ███████ ███████ ██   ██ ██   ██


RESPONSIVE PARALLAX EFFECT
- Disabled on mobile devices (< 576px) for better performance
- Uses Intersection Observer for efficient viewport detection
- Automatically adjusts on window resize
- Supports multiple parallax containers on the same page
*/

// Parallax effect implementation with responsive breakpoint support
document.addEventListener('DOMContentLoaded', function () {
    // Find all parallax containers on the page
    const parallaxContainers = document.querySelectorAll('.parallax-container');
    if (!parallaxContainers.length) return; // Exit gracefully if no parallax containers found

    /**
     * Check if the current viewport is below Bootstrap's sm breakpoint (576px)
     * Parallax effects are disabled on mobile devices for performance and UX reasons
     * @returns {boolean} True if viewport width is less than 576px
     */
    function isMobileDevice() {
        return window.innerWidth < 576;
    }

    // Initialize parallax effect for each container independently
    parallaxContainers.forEach(function (parallaxContainer) {
        // Cache DOM elements for performance
        const imageWrapper = parallaxContainer.querySelector('.parallax-image-wrapper');
        const parallaxImage = imageWrapper ? imageWrapper.querySelector('img') : null;
        const contentBox = parallaxContainer.querySelector('.content-box');

        // State variables for parallax calculations
        let initialScrollY = 0;        // Scroll position when element enters viewport
        let containerTop = 0;          // Container's top position relative to document
        let windowHeight = window.innerHeight; // Current viewport height
        let isEffectActive = false;    // Whether parallax effect is currently active
        let observer = null;           // Intersection Observer instance

        /**
         * Reset all parallax-related styles to their default values
         * Used when disabling parallax effect (e.g., on mobile devices)
         */
        function resetParallaxStyles() {
            if (parallaxImage) {
                parallaxImage.style.height = '';      // Remove custom height
                parallaxImage.style.top = '';         // Remove custom positioning
                parallaxImage.style.transform = '';   // Remove any transforms
            }
            if (contentBox) {
                contentBox.style.transform = '';      // Remove content transforms
            }
        }

        /**
         * Apply initial styles required for parallax effect
         * Makes the image larger to accommodate parallax movement
         */
        function applyParallaxStyles() {
            if (parallaxImage) {
                // Make image 20% taller to accommodate parallax movement
                parallaxImage.style.height = '120%';
                // Start position slightly up to prevent white space at bottom
                parallaxImage.style.top = '-10%';
            }
        }

        /**
         * Main scroll handler for parallax effect
         * Calculates and applies transform values based on scroll position
         * Only runs when effect is active and not on mobile devices
         */
        function onParallaxScroll() {
            // Skip if effect is inactive or on mobile device
            if (!isEffectActive || isMobileDevice()) return;

            // Calculate scroll distance since element became visible
            const currentScrollY = window.pageYOffset;
            const relativeScroll = currentScrollY - initialScrollY;

            // Calculate element's position relative to viewport
            const containerRect = parallaxContainer.getBoundingClientRect();
            const containerProgress = 1 - (containerRect.top / windowHeight);

            // Only apply effect when element is in or near the viewport
            // Extended range (1.3) ensures smooth transitions
            if (containerProgress >= 0 && containerProgress <= 1.3) {
                // Calculate transform values with different rates for depth effect
                // Lower multipliers create more subtle parallax movement
                const imageRate = relativeScroll * 0.06;    // Image moves slower (background)
                const contentRate = relativeScroll * 0.09;  // Content moves slightly faster

                // Apply transforms to create parallax effect
                if (parallaxImage) {
                    parallaxImage.style.transform = `translateY(${imageRate}px)`;
                }
                if (contentBox) {
                    contentBox.style.transform = `translateY(${contentRate}px)`;
                }
            }
        }

        /**
         * Intersection Observer callback function
         * Initializes or deactivates parallax effect based on element visibility
         * @param {IntersectionObserverEntry[]} entries - Array of observed elements
         */
        function initParallax(entries) {
            // Always skip parallax initialization on mobile devices
            if (isMobileDevice()) {
                resetParallaxStyles();
                isEffectActive = false;
                window.removeEventListener('scroll', onParallaxScroll);
                return;
            }

            const entry = entries[0];

            if (entry.isIntersecting) {
                // Element is entering the viewport - activate parallax
                initialScrollY = window.pageYOffset;
                containerTop = entry.boundingClientRect.top + initialScrollY;
                windowHeight = window.innerHeight;
                isEffectActive = true;

                // Apply parallax-specific styles
                applyParallaxStyles();

                // Reset transforms to starting position
                if (parallaxImage) parallaxImage.style.transform = 'translateY(0)';
                if (contentBox) contentBox.style.transform = 'translateY(0)';

                // Start listening for scroll events with passive flag for performance
                window.addEventListener('scroll', onParallaxScroll, { passive: true });
            } else {
                // Element has left the viewport - deactivate parallax
                isEffectActive = false;
                window.removeEventListener('scroll', onParallaxScroll);
            }
        }

        /**
         * Setup or destroy parallax effect based on current screen size
         * Called on initial load and window resize events
         */
        function setupParallax() {
            if (isMobileDevice()) {
                // Disable parallax on mobile devices
                if (observer) {
                    observer.disconnect();  // Stop observing
                }
                resetParallaxStyles();      // Remove all parallax styles
                isEffectActive = false;     // Deactivate effect
                window.removeEventListener('scroll', onParallaxScroll); // Remove scroll listener
            } else {
                // Enable parallax on desktop/tablet devices
                if (!observer) {
                    // Create new Intersection Observer if it doesn't exist
                    observer = new IntersectionObserver(initParallax, {
                        threshold: 0.3,              // Trigger when 30% of element is visible
                        rootMargin: "100px 0px"      // Start observing 100px before element enters viewport
                    });
                }
                observer.observe(parallaxContainer); // Start observing this container
            }
        }

        // Initialize parallax effect on page load
        setupParallax();

        /**
         * Handle window resize events
         * Recalculates dimensions and re-evaluates parallax setup based on new screen size
         * Uses passive flag for better performance
         */
        window.addEventListener('resize', function () {
            windowHeight = window.innerHeight;  // Update viewport height
            setupParallax();                    // Re-evaluate parallax based on new screen size
        }, { passive: true });
    });
});



/*
 ██████   ██████   ██████   ██████  ██      ███████     ███    ███  █████  ██████
██       ██    ██ ██    ██ ██       ██      ██          ████  ████ ██   ██ ██   ██
██   ███ ██    ██ ██    ██ ██   ███ ██      █████       ██ ████ ██ ███████ ██████
██    ██ ██    ██ ██    ██ ██    ██ ██      ██          ██  ██  ██ ██   ██ ██
 ██████   ██████   ██████   ██████  ███████ ███████     ██      ██ ██   ██ ██


██       █████  ███████ ██    ██     ██       ██████   █████  ██████
██      ██   ██    ███   ██  ██      ██      ██    ██ ██   ██ ██   ██
██      ███████   ███     ████       ██      ██    ██ ███████ ██   ██
██      ██   ██  ███       ██        ██      ██    ██ ██   ██ ██   ██
███████ ██   ██ ███████    ██        ███████  ██████  ██   ██ ██████


*/

document.addEventListener("DOMContentLoaded", function () {
    // Select the map placeholder element
    var mapPlaceholder = document.getElementById('map-placeholder');

    // Create an Intersection Observer to observe when the map placeholder enters the viewport
    var observer = new IntersectionObserver(function (entries) {
        // If the placeholder is in the viewport, load the iframe and stop observing
        if (entries[0].isIntersecting) {
            loadMapIframe();
            observer.disconnect();
        }
    });

    // Start observing the map placeholder
    observer.observe(mapPlaceholder);
});

function loadMapIframe() {
    // Get the iframe code from the localized data
    var iframeCode = acfData.iframe_code;

    // Parse the iframe code to extract the src attribute
    var parser = new DOMParser();
    var doc = parser.parseFromString(iframeCode, 'text/html');
    var iframeSrc = doc.querySelector('iframe').getAttribute('src');

    // Create a new iframe element
    var iframe = document.createElement('iframe');
    iframe.src = iframeSrc;
    iframe.width = "100%";
    iframe.height = "100%";
    iframe.style.border = "0";
    iframe.allowFullscreen = "";
    iframe.loading = "lazy";
    iframe.referrerPolicy = "no-referrer-when-downgrade";

    // Replace the map placeholder with the new iframe
    document.getElementById('map-placeholder').replaceWith(iframe);
}