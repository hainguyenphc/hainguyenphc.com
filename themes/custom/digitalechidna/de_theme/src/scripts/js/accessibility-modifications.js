/**
 * @file
 * Accessibility Modifications
 */

(function ($, Drupal) {

  // Add aria-label to chosen dropdown search input
  Drupal.behaviors.ChosenAccessibility = {
    attach: function(context, settings) {
      $('body').on('chosen:ready', function(evt, params) {
        $('.js-form-item.js-form-type-select', context).each(function(index, element) {
          var chosenLabel = $(this).find('label').text();
          $(this).find('.chosen-container-multi input.chosen-search-input').attr('aria-label', chosenLabel);
          $(this).find('.chosen-container-single input.chosen-search-input').attr('aria-label', chosenLabel);
          $(this).find('select.form-select').attr('aria-label', chosenLabel);
        });
      });
    }
  }

  $(function() {


    // ===== If Details Summary is focusable then don't allow focus of the polyfilled anchor child

    $('details summary').focus(function() {
      $(this).find('a').attr('tabIndex', '-1');
    });

    // Replace <none> alt tag with "" since it is decorative
    $('img[alt="<none>"]').each(function(){
      $(this).prop("alt", "");
    });
  });
})(jQuery, Drupal);