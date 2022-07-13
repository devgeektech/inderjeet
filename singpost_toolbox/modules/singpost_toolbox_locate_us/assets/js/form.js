jQuery(function ($) {
    $('.locate-us-type-box').click(function () {
        $('.locate-us-type-box').removeClass('active');
        $(this).addClass('active');
    });

    if ($('.form-radio').is(':checked')) {
        $('.form-radio:checked').parents('.locate-us-type-input').find('.locate-us-type-box').addClass('active');
    }

    if ($('.locate-us-type-box').hasClass('active')) {
        $('.locate-us-type-box.active').parents('.locate-us-type-input').find('.form-radio').attr('checked', true);
    }

    $('#go').click(function () {
        var input = $('#origin-input').val();
        if (input.length < 0) {
            $('.collapse').removeClass('show');
            $('.btn-accordion').attr('aria-expanded', 'false');
        }
    });

    var $offset = $('#locate-us');
    /*$('html,body').animate({
        scrollTop: $offset.offset().top
    }, 500);*/ //commeneted-30-05-2022

    function isUpper(str) {
        return !/[a-z]/.test(str) && /[A-Z]/.test(str);
    }

    function splitCapitalCharacter(data) {
        if (data === undefined) {
            return;
        }
        var result = data[0];
        for (i = 1; i < data.length; i++) {
            var char = data[i];
            var prechar = data[i - 1];

            if (!isUpper(prechar) && isUpper(char)) {
                result += " " + char;
            }
            else {
                result += char;
            }
        }

        return result;
    }

    $('.list-location .type').html(splitCapitalCharacter($('.list-location .type').html()));
    if ( $('.sgp-locate-us__sec-1').hasClass('after-result') ) {
        $('#step-0').css('display','none');
    }

    jQuery( ".sgp-locate-result__note span" ).each(function( index ) {
        var distacePost = jQuery( this ).text();
        distacePost =  distacePost / 1000;
        jQuery( this ).text(distacePost);
    });
    jQuery(document).ready(function() {
        jQuery('#cur-loc-icon').insertAfter('.frontend-locate-us.node-form #edit-keyword');
    });
    jQuery('.sgp-locate-us__load-more').click(function() {
        jQuery('#map').toggleClass('active');
        jQuery('#map2').toggleClass('active');
    });
});