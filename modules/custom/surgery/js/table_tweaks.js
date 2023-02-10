/** 
 * @file web/modules/custom/surgery/js/table_tweaks.js
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  
  Drupal.behaviors.show_more = {
    attach: function(context, settings) {
      const table_ids = drupalSettings.surgery.table_ids;
      if (Array.isArray(table_ids) && table_ids.length > 0) {
        table_ids.forEach(id => {
          $(`#${id}`).DataTable();
        });
      }
    }
  }
  
})(jQuery, Drupal, drupalSettings);
