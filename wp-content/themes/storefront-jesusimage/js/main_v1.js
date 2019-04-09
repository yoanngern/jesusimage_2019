// @codekit-prepend "vendor/jquery-2.2.2.js"
// @codekit-append "vendor/jquery.slides.js"

var isMobile = false;

$(document).ready(function () {

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

        $('body').addClass("mobile");

        isMobile = true;
    }

    $('body > header > .global li.menu-item-has-children > a').on('click', function (e) {


        if (!$(this).hasClass('click-on') && isMobile) {
            e.preventDefault();
        }

    });


    $('#burger').click(function () {

        $('body > header > .global').toggleClass('open_nav');
    });


    $('body > header > .global ul.menu > li').each(function () {

        $('ul.sub-menu', this).addClass('hidden');


    }).mouseenter(function () {

        if (!isMobile) {
            $('ul.sub-menu', this).addClass('active');

            $('ul.sub-menu', this).removeClass('hidden');
        }

    }).mouseleave(function () {
        if (!isMobile) {
            $('ul.sub-menu', this).removeClass('active').addClass('hidden');
        }

    }).on('click', function () {
        if (isMobile) {


            $('ul.sub-menu', this).addClass('active');

            $('body > header > .global ul.menu > li').each(function () {
                $('ul.sub-menu', this).addClass('hidden');
                $('> a', this).removeClass('click-on');
            });

            $('ul.sub-menu', this).removeClass('hidden');
            $('> a', this).addClass('click-on');
        }
    });

});

