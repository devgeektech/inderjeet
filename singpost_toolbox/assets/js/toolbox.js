(function (Drupal, $, drupalSettings) {

    Drupal.behaviors.singpostToolbox = {
        attach: function (context) {

            var calculate_mail = $(context).find('form.calculate-mail');

            var calculate_package = $(context).find('form.calculate-package');

            var calculate_postage = $(context).find('form.calculate-postage');

            var calculate_oversea = $(context).find('form.frontend-calculate-overseas');

            var fpc_street_form = $(context).find('form.frontend-find-postal-code-street');

            var fpc_landmark_form = $(context).find('form.frontend-find-postal-code-landmark');

            var fpc_pobox_form = $(context).find('form.frontend-find-postal-code-pobox');

            var tnt = $(context).find('.frontend-tnt');

            var redirect = $(context).find('form.frontend-redirect-redeliver');

            var locate_us = $(context).find('form.frontend-locate-us');

            //Begin validate for Calculate Postage Module
            $.validator.addMethod('minStrict', function (value, elem, param) {
                return value > param;
            });

            calculate_mail.each(function () {
                var that = $(this);
                var modal = '#recaptcha-modal-cmail';
                var resp = 'g-recaptcha-response-cmail';

                $(this).validate({
                    rules: {
                        weight: {
                            required: true,
                            number: true,
                            minStrict: 0
                        },
                    },
                    messages: {
                        weight: {
                            required: "Please enter weight.",
                            number: "Weight must be numeric and greater than 0.",
                            minStrict: "Weight must be numeric and greater than 0."
                        }
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal, resp, recaptchaCPNode);
                    }
                });
            });

            calculate_package.each(function () {
              var that = $(this);
              var modal = '#recaptcha-modal-cpackage';
              var resp = 'g-recaptcha-response-cpackage';

              var calculatePackageValidator = $(this).validate({
                rules: {
                  weight: {
                    required: true
                  },
                  dimension: {
                    required: true
                  }
                },
                messages: {
                  weight: {
                    required: "Please enter weight."
                  },
                  dimension: {
                    required: "Please select dimension."
                  }
                }
              });
              calculatePackageValidator.settings.submitHandler = function (form, event) {
                checkCaptcha(that, form, modal, resp, recaptchaCPNode2);
              }
            });

            calculate_postage.each(function () {
                var that = $(this);
                var modal = '#recaptcha-modal-cpackage';
                var resp = 'g-recaptcha-response-cpackage';

                var calculatePostageValidator = $(this).validate({
                    rules: {
                        weight: {
                            required: true,
                            number: true,
                            minStrict: 0
                        },
                        dimension: {
                            required: true
                        }
                    },
                    messages: {
                        weight: {
                            required: "Please enter weight.",
                            number: "Weight must be a positive number.",
                            minStrict: "Weight must be a positive number."
                          },
                        dimension: {
                            required: "Please select dimension."
                        }
                    }
                });
                calculatePostageValidator.settings.submitHandler = function (form, event) {
                    checkCaptcha(that, form, modal, resp, recaptchaCPNode2);
                }
            });

            calculate_oversea.each(function () {
              var that = $(this);
              var modal = '#recaptcha-modal-coversea';
              var resp = 'g-recaptcha-response-coversea';

              var calculateOverseaValidator = $(this).validate({
                rules: {
                  country: {
                    required: true
                  },
                  weight: {
                    required: true,
                    number: true,
                    minStrict: 0
                  },
                },
                messages: {
                  country: {
                    required: "Please select country.",
                  },
                  weight: {
                    required: "Please enter weight.",
                    number: "Weight must be a positive number.",
                    minStrict: "Weight must be a positive number."
                  }
                }
              });
              calculateOverseaValidator.settings.submitHandler = function (form, event) {
                checkCaptcha(that, form, modal, resp, recaptchaCPNode3);
              }
            })
            //End validate for Calculate Postage Module

            //Begin validate for Find Postal Code Module
            fpc_street_form.each(function () {
                var that = $(this);
                var modal_id = '#recaptcha-modal-fpc-street';
                var modal_resp = 'g-recaptcha-response-fpc-street';

                $(this).validate({
                    rules: {
                        building_no: "required",
                        street_name: {
                            required: true,
                            minlength: 2
                        },
                    },
                    messages: {
                        building_no: "Please enter Building/Block/House No.",
                        street_name: {
                            required: "Please enter Street Name.",
                            minlength: "Please input more than 2 characters."
                        },
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal_id, modal_resp, recaptchaFpcNode);
                    }
                });
            });

            fpc_landmark_form.each(function () {
                var that = $(this);
                var modal_id = '#recaptcha-modal-fpc-landmark';
                var modal_resp = 'g-recaptcha-response-fpc-landmark';

                $(this).validate({
                    rules: {
                        major_building: {
                            required: true,
                            minlength: 2
                        },
                    },
                    messages: {
                        major_building: {
                            required: "Please enter Major Building/ Estate Name.",
                            minlength: "Please input more than 2 characters."
                        },
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal_id, modal_resp, recaptchaFpcNode2);
                    }
                });
            });

            fpc_pobox_form.each(function () {
                var that = $(this);
                var modal_id = '#recaptcha-modal-fpc-pobox';
                var modal_resp = 'g-recaptcha-response-fpc-pobox';

                $(this).validate({
                    rules: {
                        po_box_type: "required",
                        delivery_no: "required",
                        post_office: {
                            required: true,
                            minlength: 2
                        },
                    },
                    messages: {
                        po_box_type: "Please choose Type.",
                        delivery_no: "Please enter PO Box No.",
                        post_office: {
                            required: "Please enter Post Office.",
                            minlength: "Please input more than 2 characters."
                        },
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal_id, modal_resp, recaptchaFpcNode3);
                    }
                });
            });

            fpc_pobox_form.find('select.po-box-type').on('change', function () {
                changeMessage($(this), $('input.delivery-no'));
            });

            fpc_pobox_form.find('select.po-box-type-side').on('change', function () {
                changeMessage($(this), $('input.delivery-no-side'))
            });

            function changeMessage(elem, delivery_elem) {
                var message,
                    type = elem.val();

                if (type == 'B') {
                    message = "Please enter Locked Bag Service No.";
                    delivery_elem.attr('placeholder', 'Locked Bag Service No.');
                }
                else {
                    message = "Please enter PO Box No.";
                    delivery_elem.attr('placeholder', 'PO Box No.');
                }

                delivery_elem.rules('add', {
                    messages: {
                        required: message
                    }
                });

                delivery_elem.valid();
            }

            //End validate for Find Postal Code Module

            //Begin validate for Redirect and Redeliver Module
            $.validator.addMethod("validateInput", function (value, element, regexp) {
                var re = new RegExp(regexp);
                return this.optional(element) || re.test(value);
            });

            redirect.each(function () {
                var that = $(this);
                var modal = '#recaptcha-modal-redirect';
                var resp = 'g-recaptcha-response-redirect';

                $(this).validate({
                    rules: {
                        item_number: {
                            required: true,
                            validateInput: /^(\w{9,15}|(\w{21}))$/
                        },
                        phone_number: {
                            validateInput: /^\+?([0-9])([- 0-9])*(\d+)$/
                        },
                        email: {
                            validateInput: /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/
                        }
                    },
                    messages: {
                        item_number: {
                            required: "Please enter your article/item number.",
                            validateInput: "Please enter your article/item number."
                        },
                        phone_number: {
                            validateInput: "Please enter a valid Contact No. following the format: +65 12345678"
                        },
                        email: {
                            validateInput: "Please enter a valid email address following the format: abc@example.com"
                        }
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal, resp, recaptchaRRNode);
                    }
                });
            });
            //End Validate for Redirect and Redeliver Module

            //Begin validate for Track and Trace Module
            $.validator.addMethod("TrackNosRules", function (value, element) {
                value = value.replace(/\n/g, ',');
                var items = value.trim().split(",");
                return (items.length <= 10);
            }, "You have exceed the maximum limit of 10 track number per request.");

            $.validator.addMethod("LengthNos", function (value, element) {
                value = value.replace(/\n/g, ',');
                var items = value.trim().split(",");
                var count = 0;
                $.each(items, function (index, val) {
                    if (!((val.length >= 9 && val.length <= 15) || val.length == 21)) {
                        count++;
                    }
                });
                return (count == 0);
            }, "Invalid tracking numbers. Please try to enter again.");

            tnt.each(function () {
                var that = $(this);
                var modal = '#recaptcha-modal-track-and-trace';
                var resp = 'g-recaptcha-response-track-and-trace';
                $(this).validate({
                    rules: {
                        tracking_numbers: {
                            required: true,
                            LengthNos: true,
                            TrackNosRules: true
                        }
                    },
                    messages: {
                        tracking_numbers: {
                            required: 'Tracking number field is required.'
                        }
                    },
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal, resp, recaptchaTnt);
                    }
                });
            });
            // End validate for Track and Trace Module

            // Begin validate for Locate Us Module
            locate_us.each(function () {
                var that = $(this);
                var modal = '#recaptcha-modal-locate-us';
                var resp = 'g_recaptcha_response_locate_us';

                that.validate({
                    submitHandler: function (form) {
                        checkCaptcha(that, form, modal, resp, recaptchaLocateNode);
                    }
                });
            });

            // End validate for Locate Us Module

            function checkCaptcha(element, form, captcha_modal, captcha_resp, resp_id) {
                var response = grecaptcha.getResponse(resp_id);

                if (!response && element.find(captcha_modal).length) {
                    element.attr('data-recaptcha-submit', 'true');
                    $(captcha_modal).appendTo("body").modal('show');
                }
                else {
                    $(captcha_modal).appendTo("body").modal('hide');
                    element.find('[name="' + captcha_resp + '"]').text(response);
                    form.submit();
                }
            }
        }
    };

    $.ajax({
        method: "GET",
        url: drupalSettings.path.baseUrl + "protection-flag",
        dataType: 'json',
        success: function (resp) {
            if (resp.find_postal_code_frontend_form_street != 5) {
                $('form.frontend-find-postal-code-street').find('[name="g-recaptcha-response-fpc-street"]').remove();
                $('form.frontend-find-postal-code-street').find('.g-recaptcha').remove();
                $('form.frontend-find-postal-code-street').find('#recaptcha-modal-fpc-street').remove();
            }

            if (resp.find_postal_code_frontend_form_landmark != 5) {
                $('form.frontend-find-postal-code-landmark').find('[name="g-recaptcha-response-fpc-landmark"]').remove();
                $('form.frontend-find-postal-code-landmark').find('.g-recaptcha').remove();
                $('form.frontend-find-postal-code-landmark').find('#recaptcha-modal-fpc-landmark').remove();
            }

            if (resp.find_postal_code_frontend_form_pobox != 5) {
                $('form.frontend-find-postal-code-pobox').find('[name="g-recaptcha-response-fpc-pobox"]').remove();
                $('form.frontend-find-postal-code-pobox').find('.g-recaptcha').remove();
                $('form.frontend-find-postal-code-pobox').find('#recaptcha-modal-fpc-pobox').remove();
            }

            if (resp.calculate_mail_frontend_form != 5) {
                $('form.calculate-mail').find('[name="g-recaptcha-response-cmail"]').remove();
                $('form.calculate-mail').find('.g-recaptcha').remove();
                $('form.calculate-mail').find('#recaptcha-modal-cmail').remove();
            }

            if (resp.calculate_package_frontend_form != 5) {
                $('form.calculate-package').find('[name="g-recaptcha-response-cpackage"]').remove();
                $('form.calculate-package').find('.g-recaptcha').remove();
                $('form.calculate-package').find('#recaptcha-modal-cpackage').remove();
            }

            if (resp.frontend_calculate_by_overseas_form != 5) {
                $('form.frontend-calculate-overseas').find('[name="g-recaptcha-response-coversea"]').remove();
                $('form.frontend-calculate-overseas').find('.g-recaptcha').remove();
                $('form.frontend-calculate-overseas').find('#recaptcha-modal-coversea').remove();
            }

            if (resp.frontend_redirect_redeliver_form != 5) {
                $('form.frontend-redirect-redeliver').find('[name="g-recaptcha-response-redirect"]').remove();
                $('form.frontend-redirect-redeliver').find('.g-recaptcha').remove();
                $('form.frontend-redirect-redeliver').find('#recaptcha-modal-redirect').remove();
            }

            if (resp.frontend_locate_us_form != 5) {
                $('form.frontend-locate-us').find('[name="g_recaptcha_response_locate_us"]').remove();
                $('form.frontend-locate-us').find('.g-recaptcha').remove();
                $('form.frontend-locate-us').find('#recaptcha-modal-locate-us').remove();
            }

            if (resp.track_and_trace_frontend_form != 5) {
                $('form.frontend-tnt').find('[name="g-recaptcha-response-track-and-trace"]').remove();
                $('form.frontend-tnt').find('.g-recaptcha').remove();
                $('form.frontend-tnt').find('#recaptcha-modal-track-and-trace').remove();
            }
        },
        error: function () {
        }
    })

    var sideToolbox = $('.side-toolbox');

    if (sideToolbox.length) {
        sideToolbox.on('mouseenter', function () {
            sideToolbox.removeClass('closed').addClass('opened');
        });

        sideToolbox.on('mouseleave', function () {
            sideToolbox.removeClass('opened');
        });

        var $accordion = sideToolbox.find('.accordion');
        var $openButton = sideToolbox.find('.open-button');
        var $mobileUp = sideToolbox.find('.mobile-up');

        if ($accordion.length) {
            $accordion.on('show.bs.collapse', function (e) {
                $(e.target).siblings('.card-header').addClass('active');
            });

            $accordion.on('shown.bs.collapse', function (e) {
                $(this).parents('.side-toolbox').addClass('active');
            });

            $accordion.on('hide.bs.collapse', function (e) {
                $(e.target).siblings('.card-header').removeClass('active');
            });

            $accordion.on('hidden.bs.collapse', function (e) {
                $(this).parents('.side-toolbox').removeClass('active');
            });
        }

        if ($openButton.length) {
            $openButton.on('click', function () {
                var $parent = $(this).parents('.side-toolbox');
                if ($parent.hasClass('opened')) {
                    $parent.removeClass('opened');
                }

                if ($accordion.length) {
                    $accordion.find('.collapse').collapse('hide');
                }

                $parent.removeClass('active').addClass('closed');
            });
        }

        if ($mobileUp.length) {
            $mobileUp.on('click', function () {
                $mobileUp.parents('.collapse').collapse('hide');
            });
        }
    }

    if ($('.toolbox-results').length) {
        var $toolbox_results = $('.toolbox-results');
        $('html,body').animate({
            scrollTop: $toolbox_results.offset().top
        }, 500);
    }
})(Drupal, jQuery, drupalSettings);

function redeliveryRedirect(tracknumber) {
    var rr_side_form = jQuery('form.frontend-redirect-redeliver.side-form');
    var input = rr_side_form.find('#rr-item-number-side');

    if (rr_side_form.length) {
        input.val(tracknumber);
    }
}

jQuery("#street-side-form").on('change', function() {
    find_postal_code_tab(this.value);
});
jQuery("#landmark-side-form").on('change', function() {
    find_postal_code_tab(this.value);
});
jQuery("#pobox-side-form").on('change', function() {
    find_postal_code_tab(this.value);
});
jQuery( document ).ready(function() {
    var selected_fpc_tab = jQuery('.fpc-side #tab-content .active.show :selected').val();
    find_postal_code_tab(selected_fpc_tab);
});

function find_postal_code_tab(get_slt_val){
    if(get_slt_val == 'street'){
        jQuery('.fpc-side #tab-content .tab-pane').removeClass('active show');
        jQuery('#fpc-street-side').addClass('active show');
    }
    else if (get_slt_val == 'landmark') {
        jQuery('.fpc-side #tab-content .tab-pane').removeClass('active show');
        jQuery('#fpc-landmark-side').addClass('active show');
    } else {
        jQuery('.fpc-side #tab-content .tab-pane').removeClass('active show');
        jQuery('#fpc-pobox-side').addClass('active show');
    }
}

jQuery( document ).ready(function() {
    jQuery('.track-trace-sec__mob-track #tracking-numbers-side').attr('placeholder', 'Eg: ER10098275SG');
    jQuery('.track-trace-sec__mob-track .track-trace-sec__mob-search-sec').on('click', function () {
        jQuery('.track-trace-sec__mob-track #track-and-trace-frontend-form').submit();
    });
});

jQuery(document).ready(function () {    
    
    jQuery(".calculate-postage .sgp-input-text[name='weight']").keypress(function (e) {    

        var charCode = (e.which) ? e.which : event.keyCode    

        if (String.fromCharCode(charCode).match(/[^0-9]/g)) {
            return false;
        }                         

    });
    jQuery(".span-wrapper").wrapInner("<span></span>");

});  