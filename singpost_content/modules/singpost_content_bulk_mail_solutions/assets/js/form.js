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
    Drupal.behaviors.bulkMailSolutions = {
        attach: function (context) {
            if (typeof drupalSettings.singpost_content_bulk_mail_solutions !== 'undefined') {
                const webform_id = drupalSettings.singpost_content_bulk_mail_solutions.webform_id;
                const $form = $('#bulk_mail_solutions');
                const $submit = $form.find('#edit-actions-submit');
                const $submit_btn_text = $submit.html();
                const $serviceType = $form.find('#edit-service-type');
                const $volume = $form.find('#edit-volume');
                const $weight = $form.find('#edit-weight');

                $(context).find($submit).once('bulk-mail-calculate').each(function () {
                    $form.on('submit change', context, function () {
                        if( $serviceType.val() == 'O'){
                            $volume.rules('add', {
                                min: 500,
                                messages: {
                                    min: 'Minimum volume for Bulk Mail Rates is 500 pieces.'
                                }
                            });
                        }
                        $weight.rules('add', {
                            max: $serviceType.val() == 'L' ? 5000 : 2000,
                            messages: {
                                max: $serviceType.val() == 'L' ? 'Please refer to Speedpost Islandwide rates for weight above 5 kg.' : 'Maximum weight per piece is 2000 grams.'
                            }
                        });
                    });

                    $form.on('submit', context, function (e) {
                        e.preventDefault();

                        if ($form.valid() === true) {
                            $.ajax({
                                type: "POST",
                                url: '/ajax/bulk-mail-solutions/calculate',
                                dataType: 'json',
                                data: $form.serialize(),
                                beforeSend: function () {
                                    $submit.prop('disabled', true);
                                    $submit.text('Please wait...');
                                },
                                complete: function () {
                                    $submit.prop('disabled', false);
                                    $submit.html($submit_btn_text);
                                },
                                success: function (res) {
                                    $('#bulk-mail-result').html(res);
                                    const $help = $('#bulk-mail-result').find('.webform-element-help');

                                    if ($help.length) {
                                        var options = $.extend({
                                            content: $help.attr('data-webform-help'),
                                            items: '[data-webform-help]'
                                        }, Drupal.webform.elementHelpIcon.options);

                                        $help.tooltip(options)
                                            .on('click', function (event) {
                                                event.preventDefault();
                                            }).on('keydown', function (event) {
                                            if (event.keyCode === $.ui.keyCode.ESCAPE) {
                                                event.stopPropagation();
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                });
            }
        }
    };
})(jQuery, Drupal, drupalSettings);
jQuery("#bulk_mail_solutions select#edit-service-type[name='service_type']").find('option').get(0).remove();
