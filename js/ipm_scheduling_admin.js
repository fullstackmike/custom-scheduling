(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.IPMSchedulingAdmin = {
    attach: function (context, settings) {
      $('.ipm_scheduling_form_element').hide();
      var current_value =  $('.field--name-field-scheduling-data').val();
      $('.form_'+current_value).show();
      $('.field--name-field-scheduling-data').change(function (e) {
        var current_value = $(this).val();
        $('.ipm_scheduling_form_element').hide();
        $('.form_'+current_value).show();
      });
    }
  }
})(jQuery, Drupal);
