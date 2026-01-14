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


// countries selector
// Array of countries with ISO codes and names
const countries = [
    { code: "PK", name: "PKR" }, // Pakistan
    { code: "US", name: "USA" }, // United States
    { code: "CA", name: "CA" }, // Canada
    { code: "GB", name: "GB" }, // United Kingdom
    { code: "AU", name: "AU" }, // Australia
    { code: "FR", name: "FR" }, // France
    { code: "DE", name: "DE" }, // Germany
    { code: "IN", name: "IN" }, // India
    { code: "JP", name: "JP" }, // Japan
    { code: "CN", name: "CN" }  // China
    ];

    const dropdownToggle = document.getElementById("dropdownToggle");
    const dropdownMenu = document.getElementById("dropdownMenu");
    const selectedCountry = document.querySelector(".selected-country");

    // Populate dropdown menu
    countries.forEach((country) => {
    const item = document.createElement("div");
    item.className = "dropdown-item";
    item.innerHTML = `
        <span class="flag-icon flag-icon-${country.code.toLowerCase()}"></span>
        ${country.name}
    `;
    item.onclick = () => {
        selectedCountry.innerHTML = `
        <span class="flag-icon flag-icon-${country.code.toLowerCase()}"></span>
        ${country.name}
        `;
        dropdownMenu.classList.remove("active");
    };
    dropdownMenu.appendChild(item);
    });

    // Toggle dropdown menu
    dropdownToggle.onclick = () => {
    dropdownMenu.classList.toggle("active");
    };

    // Close dropdown if clicked outside
    document.addEventListener("click", (event) => {
    if (!event.target.closest(".dropdown")) {
        dropdownMenu.classList.remove("active");
    }
    });





    // adult
// Toggle the main dropdown
document.getElementById('dropdownToggle1').addEventListener('click', function() {
    const menu = document.getElementById('dropdownMenu1');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });
  
  document.getElementById('dropdownToggle2').addEventListener('click', function() {
    const menu = document.getElementById('dropdownMenu2');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });
  
  // Toggle the nested dropdown
  document.querySelectorAll('.dropdown-item > span').forEach(option => {
    option.addEventListener('click', function() {
      const nestedMenu = this.nextElementSibling;
      nestedMenu.style.display = nestedMenu.style.display === 'block' ? 'none' : 'block';
    });
  });
  
  // Handle selection from both dropdowns
  document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function(event) {
      if (!event.target.classList.contains('dropdown-item')) return;
      const selectedValue = this.textContent.trim();
      document.querySelector('.selected-country').textContent = selectedValue;
      document.querySelectorAll('.dropdown-menu').forEach(menu => menu.style.display = 'none');
    });
  });
  document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-menu2');
    dropdowns.forEach(menu => {
      if (!menu.contains(event.target) && !menu.previousElementSibling.contains(event.target)) {
        menu.style.display = 'none';
      }
    });
  });
  document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-menu1');
    dropdowns.forEach(menu => {
      if (!menu.contains(event.target) && !menu.previousElementSibling.contains(event.target)) {
        menu.style.display = 'none';
      }
    });
  });


