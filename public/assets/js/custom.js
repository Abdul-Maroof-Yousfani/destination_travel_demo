$(document).ready(function() {
    $("li:first-child").addClass("first");
    $("li:last-child").addClass("last");
    
    $('[href="#"]').attr("href", "javascript:;");
    $('.menu-Bar').click(function() {
        $(this).toggleClass('open');
        $('.menuWrap').toggleClass('open');
        $('body').toggleClass('ovr-hiddn');
        $('body').toggleClass('overflw');
    });

   $('.index-slider').slick({
        dots: true,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [
        {
            breakpoint: 825,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false,
                arrows:false
            }
        },
        ]
    });


    $('.m-silder').slick({
        dots: false,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 7,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [
        {
            breakpoint: 425,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
                infinite: true,
                dots: false,
                arrows:false
            }
        },
        ]
    });

            $('.product-slid').slick({
        dots: false,
        arrows: false,
        infinite: true,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [
        {
            breakpoint: 825,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false,
                arrows:false
            }
        },
        ]
    });

        $('.client-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        responsive: [
        {
            breakpoint: 825,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false,
                arrows:false
            }
        },
        ]
    });

    $('.event-slider').slick({
        dots: false,
        arrows: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 2000,
        centerMode: true,
        responsive: [
        {
            breakpoint: 825,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false,
                arrows:false
                
            }
        },
        ]
    });


// counter javascript start


$(window).scroll(function() {
    $('.count').each(function () {
        var elemTop = $(this).offset().top;
        var elemBottom = elemTop + $(this).outerHeight();
        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();

        if (elemBottom > scrollTop && elemTop < scrollTop + windowHeight) {
            if (!$(this).data('animated')) {
                $(this).prop('Counter',0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 4000,
                    easing: 'swing',
                    step: function (now) {
                        $(this).text(Math.ceil(now));
                    }
                });
                $(this).data('animated', true);
            }
        } else {
            $(this).data('animated', false);
        }
    });
});

// counter javascript end

// counter javascript end


    $('ul.faq-ul li.active div').slideDown();
    $('ul.faq-ul li h4').click(function() {
        $('ul.faq-ul li').removeClass('active');
        $(this).parent('li').addClass('active');
        $('ul.faq-ul li div').slideUp();
        $(this).parent('li').find('div').slideDown();
    });
    
        $('.faq-ul>li').click(function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
        });
    
        $('.fancybox-media').fancybox({
            openEffect: 'none',
            closeEffect: 'none',
            helpers: {
                media: {}
            }
        });

    $('.searchBtn').click(function() {
        $('.searchWrap').addClass('active');
        $('.overlay').fadeIn('active');
        $('.searchWrap input').focus();
        $('.searchWrap input').focusout(function(e) {
            $(this).parents().removeClass('active');
            $('.overlay').fadeOut('active');
            $('body').removeClass('ovr-hiddn');

        });
    });

});


$(window).on('load', function() {
    var currentUrl = window.location.href.substr(window.location.href.lastIndexOf("/") + 1);
    $('ul.menu li a').each(function() {
        var hrefVal = $(this).attr('href');
        if (hrefVal == currentUrl) {
            $(this).removeClass('active');
            $(this).closest('li').addClass('active')
            $('ul.menu li.first').removeClass('active');
        }
    })

});

// tabing

     $('[data-targetit]').on('click', function(e) {
  $(this).addClass('current');
  $(this).siblings().removeClass('current');
  var target = $(this).data('targetit');
  $('.' + target).siblings('[class^="box-"]').hide();
  $('.' + target).fadeIn();
});


     // sticky header

     $(window).scroll(function() {
    if ($(this).scrollTop() > 500){  
        $('').addClass("box-visable");
    }
    else{
        $('').removeClass("box-visable");
    }
});


// slider additional js for tabbing
          $("[data-targetit]").on("click", function (e) {
        $(".test").slick("setPosition");
    });

    const countriesDropdown6 = [
        { code: "PK", name: "PKR" },
        { code: "US", name: "USD" },
        { code: "GB", name: "GBP" },
        { code: "EU", name: "EUR" }
    ];
    
    // Options for dropdown4
    const countriesDropdown4 = [
        { code: "IN", name: "INR" },
        { code: "AU", name: "AUD" },
        { code: "CA", name: "CAD" },
        { code: "JP", name: "JPY" }
    ];
    
    // function setupDropdown(dropdownId, countries) {
    //     const dropdownToggle = document.getElementById(`dropdownToggle${dropdownId}`);
    //     const dropdownMenu = document.getElementById(`dropdownMenu${dropdownId}`);
    //     const selectedCountry = document.getElementById(`selectedCountry${dropdownId}`);
    
    //     if (!dropdownToggle || !dropdownMenu || !selectedCountry) {
    //         console.error(`Dropdown ${dropdownId} elements not found!`);
    //         return;
    //     }
    
    //     // Populate dropdown menu
    //     dropdownMenu.innerHTML = ""; // پہلے سے موجود آپشنز ہٹانے کے لیے
    //     countries.forEach((country) => {
    //         const item = document.createElement("div");
    //         item.className = "dropdown-item";
    //         item.innerHTML = `
    //             <span class="flag-icon flag-icon-${country.code.toLowerCase()}"></span>
    //             ${country.name}
    //         `;
    //         item.onclick = (event) => {
    //             event.stopPropagation();
    //             selectedCountry.innerHTML = `
    //                 <span class="flag-icon flag-icon-${country.code.toLowerCase()}"></span>
    //                 ${country.name}
    //             `;
    //             dropdownMenu.classList.add("active"); // مینو کو کھلا رکھنے کے لیے
    //         };
    //         dropdownMenu.appendChild(item);
    //     });
    
    //     // Toggle dropdown menu
    //     dropdownToggle.onclick = (event) => {
    //         event.stopPropagation();
    //         dropdownMenu.classList.toggle("active");
    //     };
    
    //     // Close dropdown when clicking outside
    //     document.addEventListener("click", (event) => {
    //         if (!event.target.closest(`#dropdown${dropdownId}`)) {
    //             dropdownMenu.classList.remove("active");
    //         }
    //     });
    // }
    
    // Initialize each dropdown separately
    // setupDropdown(6, countriesDropdown6);
    // setupDropdown(4, countriesDropdown4);




    // adult
    // document.getElementById('dropdownToggle1').addEventListener('click', function(event) {
    //     event.stopPropagation(); // Prevent immediate closing
    //     const menu = document.getElementById('dropdownMenu1');
    //     menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    // });
    
    // document.addEventListener('click', function(event) {
    //     const menu = document.getElementById('dropdownMenu1');
    //     const toggle = document.getElementById('dropdownToggle1');
    //     if (!menu.contains(event.target) && !toggle.contains(event.target)) {
    //         menu.style.display = 'none';
    //     }
    // });
    
    // document.querySelectorAll('.increment, .decrement').forEach(button => {
    //     button.addEventListener('click', function(event) {
    //         event.stopPropagation(); // Prevent dropdown from closing when clicking buttons
    //         const parent = this.parentElement;
    //         const countSpan = parent.querySelector('.count');
    //         let count = parseInt(countSpan.textContent);
    //         const isIncrement = this.classList.contains('increment');
            
    //         if (isIncrement && count >= 9) {
    //             showError("Maximum limit of 9 reached for " + parent.id.charAt(0).toUpperCase() + parent.id.slice(1) + ".");
    //             return;
    //         }
            
    //         let adults = parseInt(document.querySelector('#adults .count').textContent);
    //         let infants = parseInt(document.querySelector('#infants .count').textContent);
            
    //         if (parent.id === 'infants' && isIncrement && infants >= adults) {
    //             showError("Number of Infants cannot be more than the number of Adults.");
    //             return;
    //         }
            
    //         count = isIncrement ? count + 1 : Math.max(count - 1, 0);
    //         countSpan.textContent = count;
    //         hideError();
    //     });
    // });
    
    function showError(message) {
        const errorElement = document.getElementById('error-message');
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    function hideError() {
        document.getElementById('error-message').style.display = 'none';
    }
    // $('#flightModal').modal({
    //     backdrop: 'static', // Prevents closing on outside click
    //     keyboard: false
    // });
    
    // ============================================Aliiiiiiiiiiiiiiiiiii=====================================================
    window.csrfToken = "{{ csrf_token() }}";

    const _loader = action => {
        if (action === 'show') {
            $("body").append(`<div id="loader"><div id="loaderChild"></div></div>`);
        } else {
            $('#loader').remove();
        }
    };

    $(document).on('click', '.copyBtn', function () {
        let text = $(this).prev('.copyText').text();
        if (text) {
            navigator.clipboard.writeText(text).then(function () {
                _alert('Copied: ' + text);
            });
        }
    });

    $('.timeIn12Hr').each(function() {
        const original = $(this).text().trim();
        const converted = convertTo12Hour(original);
        $(this).text(converted);
    });

    function convertTo12Hour(time) {
        const [hour, minute] = time.split(':').map(Number);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const h = hour % 12 || 12;
        return `${h}:${minute.toString().padStart(2, '0')} ${ampm}`;
    }

    function formatCurrency(amount) {
        const numericAmount = Number(amount);
        
        if (isNaN(numericAmount)) {
            return 'NaN';
        }
        
        return numericAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDateTime(datetimeStr) {  // Outputs: "16 May 2025 9:30PM"
        const date = new Date(datetimeStr);

        const day = date.getDate();
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const month = monthNames[date.getMonth()];
        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // Convert 0 to 12

        return `${day} ${month} ${year} ${hours}:${minutes}${ampm}`;
    }