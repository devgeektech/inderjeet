(function (Drupal, $) {
    'use strict';

    Drupal.behaviors.singpostPackingMaterial = {
        attach: function (context, settings) {

            var form = $(context).find('form.pm-frontend-order-form');

            $.validator.addMethod(
                "validateContactNum",
                function (value, element, regexp) {
                    var re = new RegExp(regexp);
                    return this.optional(element) || re.test(value);
                },
                "Please input a valid Contact Number"
            );

            form.each(function () {
                $(this).validate({
                    rules: {
                        name: "required",
                        email: {
                            required: true,
                            email: true
                        },
                        block_number: {
                            required: true,
                            number: true
                        },
                        street_address: "required",
                        postal_code: "required",
                        account_number: "required",
                        contact_number: {
                            required: true,
                            validateContactNum: /^(?=(?!66666666|88888888|99999999|87654321))+((6|8|9)+(\d{7}))$/
                        },
                    },
                    messages: {
                        name: "Please input Name",
                        email: {
                            required: "Please input Email",
                            email: "Please input a valid email address"
                        },
                        block_number: {
                            required: "Please input Block/House Number",
                            number: "Please enter a valid block house number"
                        },
                        street_address: "Please input Street Address",
                        postal_code: "Please input Postal Code",
                        account_number: "SingPost Corporate Account Number is required. If you do not have one, please proceed to click <a href='https://shop.singpost.com/packaging-materials.html' target='_blank'> here</a> to make payment online",
                        contact_number: {
                            required: "Please input Contact Number"
                        },
                    }
                });
            });
        }
    };
})(Drupal, jQuery);
