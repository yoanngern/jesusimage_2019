// @codekit-prepend "vendor/jquery-2.2.2.js"
// @codekit-append "vendor/jquery.slides.js"


$(document).ready(function () {

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

        $('body').addClass("mobile");

    }

    $('#burger').click(function () {

        $('body header#masthead .global').toggleClass('open_nav');

    });


    $('body header#masthead > .global ul.menu li').each(function () {

        $('ul.sub-menu', this).addClass('hidden');

    }).mouseenter(function () {

        $('ul.sub-menu', this).addClass('active');

        $('ul.sub-menu', this).removeClass('hidden');

    }).mouseleave(function () {

        $('ul.sub-menu', this).removeClass('active').addClass('hidden');

    });

});

