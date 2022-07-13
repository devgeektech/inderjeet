(function ($, Drupal, undefined) {
    Drupal.behaviors.delayed_submit = {
      attach: function (context, settings) {
        //var weight = $('#calculate_package_frontend_form_node select[name="weight"]').val();
        //var dimension = $('#calculate_package_frontend_form_node select[name="dimension"]').val();
        
          var weight = $('#calculate_postage_frontend_form_node select[name="package_weight"]').val();
          var dimension = $('#calculate_postage_frontend_form_node select[name="dimension"]').val();
        $('.ajax-calculate-express-form #express-dimension').val(dimension);
        $('.ajax-calculate-express-form #express-weight').val(weight);
        $('input.delayed-input-submit').each(function () {
          var $self = $(this);
          var timeout = null;
          var delay = $self.data('delay') || 1000;
          var triggerEvent = $self.data('event') || "end_typing";

          $self.off('keyup').on('keyup', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                  $self.trigger(triggerEvent);
            }, delay);
          });
        });
        if ($('form.toolbox-form.calculate-postage').length) {
          $('form.toolbox-form.calculate-postage .calculate-postage-location').on('change', function() {
            if ($(this).val() == 'local') {
              $('form.toolbox-form.calculate-postage .calculate-postage-country').val('SG');
              $("form.toolbox-form.calculate-postage .calculate-postage-country option[value='SG']").attr('selected','selected');
            }
            else{
              $("form.toolbox-form.calculate-postage .calculate-postage-country option[value='SG']").removeAttr('selected');
              $("form.toolbox-form.calculate-postage .calculate-postage-country option[value='AF']").attr('selected','selected');
              $("form.toolbox-form.calculate-postage .calculate-postage-country option[value='SG']").hide();
            }
          });
        }
      }
    };
})(jQuery, Drupal);

jQuery( document ).ready(function() {
  location_select();
});

if (jQuery(".sgp-form-radio input").is(":checked")) {
  jQuery(this).addClass("active");
}

jQuery('#calculate_postage_frontend_form_node .sgp-form-radio').click(function() {
    jQuery('#calculate_postage_frontend_form_node .sgp-form-radio').removeClass("active");
    jQuery(this).addClass("active");                   
});

jQuery('#calculate_postage_frontend_form_side .sgp-form-radio').click(function() {
  jQuery('#calculate_postage_frontend_form_side .sgp-form-radio').removeClass("active");
  jQuery(this).addClass("active");                   
});

jQuery(function() {
  var $radioButtons = jQuery('input[type="radio"].custm_weight_radio');
      $radioButtons.each(function() {
        if (jQuery(this).is(":checked")){
          jQuery(this).parent().parent().toggleClass('active', this.checked);
        }
      });
});

function location_select(){
  var selected_location = jQuery('form.toolbox-form.calculate-postage .calculate-postage-location').val();
  if (selected_location == 'overseas'){
    jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='SG']").removeAttr('selected');
    jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='AF']").attr('selected','selected');
    jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='SG']").hide();
      
  }
  else{
      jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='SG']").show();
      jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='AF']").removeAttr('selected');
      jQuery("form.toolbox-form.calculate-postage .calculate-postage-location option[value='SG']").attr('selected','selected');
  }
}

jQuery('#calculate_postage_frontend_form_side select.sgp-select').on('change', function() {
  add_custom_class_on_api_home();
});

jQuery( document ).ready(function() {
  add_custom_class_on_api_home();
});

function add_custom_class_on_api_home(){
  var calculate_val = jQuery('#calculate_postage_frontend_form_side select.calculate-postage-country :selected').val();
  var pastage_val = jQuery('#calculate_postage_frontend_form_side select.calculate-postage-package-type :selected').val();
  if(calculate_val == 'SG' && pastage_val == 'package'){
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-3');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-2');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').addClass('width-4');
    //jQuery('#calculate_postage_frontend_form_side .form-type-textfield').addClass('width-4');
  }
  else if(calculate_val == 'SG' && pastage_val == 'mail'){
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-4');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-2');
    jQuery('#calculate_postage_frontend_form_side .form-type-textfield').removeClass('width-2');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').addClass('width-3');
    jQuery('#calculate_postage_frontend_form_side .form-type-textfield').addClass('width-3');
  }
  else if(calculate_val != 'SG'){
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-3');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').removeClass('width-4');
    jQuery('#calculate_postage_frontend_form_side .form-type-textfield').removeClass('width-3');
    jQuery('#calculate_postage_frontend_form_side .form-type-select').addClass('width-2');
    jQuery('#calculate_postage_frontend_form_side .form-type-textfield').addClass('width-2');
  }
}


if (jQuery(".path-calculate-postage .sgp-calc-postage__tiles-outer").length > 0) {
    jQuery('#progress-1').addClass('active');
    jQuery('#progress-2').addClass('active');
}

jQuery('.sgp-calc-postage__tiles--services .sgp-tile.sgp-tile--parcel input').on('click', function() {
  jQuery('.sgp-calc-postage__tiles-outer #continue-btn').addClass('active');
  var sending_to = jQuery('#calculate_postage_frontend_form_node select.calculate-postage-country :selected').text();
  var input_value = jQuery('input[name="calculate_service"]:checked').val();
  var service_name = jQuery('#'+input_value + ' #service_name-js').text();
  var working_days = jQuery('#'+input_value + ' #cal_ap_working_js').text();
  var tracking_val = jQuery('#'+input_value + ' #tracking_js').attr('track-data');
  var price_val = jQuery('#'+input_value + ' .sgp-pdp__price-current').text();

  if(tracking_val == 'yes'){
    var tracking_text = 'Tracking';
  }
  else{
    var tracking_text = 'No Tracking';
  }

  if(sending_to != "Singapore"){
    var package_val_js = 'Package';
    jQuery('#package-val-js').text(package_val_js);
  }

  if(working_days > 1){
    var working_text = 'days';
  }
  else if(working_days == 1){
    var working_text = 'day';
  }
  else{
    var working_text = 'days';
  }
  //jQuery('.sgp-calc-postage__result').show();
  var restul_api_data = service_name+' ('+ tracking_text +')<br />'+ working_days +' working '+working_text;
  jQuery('#sending_to_js').text(sending_to);
  jQuery('#result_cal_api_js').html(restul_api_data);
  jQuery('#result_price_api_js').text(price_val);
  
  jQuery('#edit-result-sec').removeClass('active');
  jQuery('#edit-result-sec').addClass('active');
});
