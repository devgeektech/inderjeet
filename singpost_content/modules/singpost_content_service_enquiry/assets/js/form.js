/**
 * @file
 * Attaches behaviors for the Service Enquiry module.
 */
(function ($, Drupal, drupalSettings) {
    'use strict';

    /**
     * Attaches jQuery validate behavior to forms.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *  Attaches the outline behavior to the right context.
     */
    Drupal.behaviors.serviceEnquiry = {
        attach: function (context) {
            if (typeof drupalSettings.singpost_content_service_enquiry !== 'undefined') {
                const webform_id = drupalSettings.singpost_content_service_enquiry.webform_id;
                const $form = $('#' + webform_id);
                const $category = $form.find('#edit-category');
                const $subCategory = $form.find('#edit-sub-category');

                $(context).find($category).once('service-enquiry-category').each(function () {
                    $(this).on('change', context, function () {
                        if ($(this).val()) {
                            $.ajax({
                                type: "POST",
                                url: '/ajax/service-enquiry/sub-categories',
                                dataType: 'json',
                                data: $form.serialize(),
                                beforeSend: function() {
                                    $category.prop('disabled', true);
                                },
                                complete: function(){
                                    $category.prop('disabled', false);
                                },
                                success: function (res) {
                                    $subCategory.find('option').remove();
                                    $.each(res, function (key, value) {
                                        $subCategory.append(new Option(value, key));
                                    });
                                }
                            });
                        }
                    });
                });
            }
        }
    };
})(jQuery, Drupal, drupalSettings);
