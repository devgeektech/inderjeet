(function ($) {
    "use strict";

    var $datepicker = $('.calendar'),
        $dateonly = $('.calendar-only'),
        $datepickerStart = $('.calendar.start-date'),
        $datepickerEnd = $('.calendar.end-date'),
        $datepickerYear = $('.calendar.year-only');

    if ($datepickerYear.length) {
        initDatePickerYear();
    }

    if ($datepickerStart.length && $datepickerEnd.length) {
        initDatetimeRangePicker();
    }

    if ($datepicker.length) {
        initDatePicker();
    }

    if ($dateonly.length) {
        initDateOnlyPicker();
    }

    function initDatePicker() {
        $datepicker.datetimepicker({
            autoclose: true,
            todayBtn: true,
            format: 'dd-mm-yyyy hh:ii',
        });
    }

    function initDateOnlyPicker() {
        $dateonly.datetimepicker({
            autoclose: true,
            todayBtn: true,
            pickTime: false,
            minView: 2,
            format: 'dd/mm/yyyy'
        });
    }

    function initDatePickerYear() {
        $datepickerYear.datetimepicker({
            format: "yyyy",
            startView: 'decade',
            minView: 'decade',
            viewSelect: 'decade',
            autoclose: true,
        });
    }

    function initDatetimeRangePicker() {
        //datetime range picker
        $datepickerEnd.datetimepicker({
            autoclose: true,
            todayBtn: true,
            format: 'dd-mm-yyyy hh:ii',
            setStartDate: $datepickerStart.val()
        });

        $datepickerStart.datetimepicker({
            autoclose: true,
            todayBtn: true,
            format: 'dd-mm-yyyy hh:ii'
        }).on('changeDate', function (ev) {
            var start_date = ev.date.getFullYear() + '-' + (ev.date.getMonth() + 1) + '-' + ev.date.getDate() + ' ' + ev.date.getHours() + ':' + ev.date.getMinutes();
            var end_date = $(this).parents('form').find('.calendar.end-date');
            end_date.val('');
            end_date.datetimepicker('update');
            end_date.datetimepicker('setStartDate', start_date);
            end_date.datetimepicker('setInitialDate', start_date);
            end_date.datetimepicker('show');
        });
    }
})(jQuery);