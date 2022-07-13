jQuery(function ($) {
    'use strict';

    var track_area_node = $("#tracking-numbers-node");

    $(".frontend-tnt .recent-queries .rec-item").click(function () {
        var track_id = track_area_node.val();
        var line = track_id.split(/\r|\r\n|\n/);
        var count = line.length;
        if (count < 20) {
            var value = $(this).html();

            if (track_id != '') {
                value = '\n' + value;
            }

            track_area_node.val(track_id + value);
            $(this).remove();
        }
    });

    var clipboard = new ClipboardJS('.btn-copy-result');

    clipboard.on('success', function (e) {
        alert("The tracking result table has been copied to clipboard");
    });

    clipboard.on('error', function (e) {
        alert("Please try again");
    });

    if(jQuery('#trackTraceModal').length > 0){
        jQuery('#trackTraceModal').modal('show');
    }

    jQuery(document).ready(function() {
        jQuery('.sgp-track-trace__error').insertAfter('#edit-field-wrapper');
    });

    /* Validation Track & Trace */

    function track_and_trace_validation(get_val){
        var value = get_val;
        if(value){
            var value = jQuery.trim(value).replace(/\s+/g, ", ");
            var items = value.trim().split(",");
            var count = 0;
            var error_msg = '';
            jQuery.each(items, function (index, val) {
                var trimed_val = val.trim();
                if (!((trimed_val.length >= 9 && trimed_val.length <= 15) || trimed_val.length == 21)) {
                    count++;
                    error_msg = '<div id="tracking-numbers-node-error" class="error">Invalid tracking numbers. Please try to enter again.</div>';
                }
            });
            if(items.length > 20){
                count++;
                error_msg = '<div id="tracking-numbers-node-error" class="error">You have exceed the maximum limit of 20 track number per request.</div>';
            }
            if(count == 0){
                jQuery('#tracking-numbers-node-error').remove();
                jQuery('.frontend-tnt.node-form button[type="submit"]').prop('disabled', false);
            }
            else{
                jQuery('#tracking-numbers-node-error').remove();
                jQuery(error_msg).insertAfter( "#tracking-numbers-node" );
                jQuery('.frontend-tnt.node-form button[type="submit"]').prop('disabled', true);
            }
        }
        else{
            jQuery('#tracking-numbers-node-error').remove();
        }
    }

    jQuery(document).ready(function(){
        jQuery('#tracking-numbers-node').blur(function() {
            track_and_trace_validation(this.value);
        });
        
        jQuery('.frontend-tnt.node-form button[type="submit"]').on('click', function () {
            var get_val = jQuery('#tracking-numbers-node').val();
            track_and_trace_validation(get_val);
        });
    });
});