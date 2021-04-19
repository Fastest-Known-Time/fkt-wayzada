/**
 * FKT WayZada JS
 */

(function ($, Drupal) {

  Drupal.behaviors.fktWayzada = {
    attach: function (context, settings) {
      // Run WayZada JS
      submitInputAndSetup();
    },

  };

})(jQuery, Drupal);
