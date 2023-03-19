/**
 * @file
 * A JavaScript file for the theme.
 */

(function ($, Drupal) {


  // @authors custom stuff that happens for every screen size
  $(function() {
    $('.block-region-detect-block .close-btn').html('');
    $('.block-region-detect-block .close-btn').append('<span class="close-icon"></span>');

    // Add any media helpers wrappers to help with responsive video
    // $('.node-page iframe').each(function() {$(this).wrap('<div class="media-embed">');});
    // $('.node-page object').each(function() {$(this).wrap('<div class="media-embed">');});
    // $('.node-page embed').each(function() {$(this).wrap('<div class="media-embed">');});
  });

  $(function() {


    $(window).resize(); // Fake a resize event to fire above listener for table overflow

    // @ui variables ===================
    // var $btnMainNav = $('.btn-menu');
    // var $btnSearch = $('.btn-search');

    // @breakpoint legend ==============================================
    // breakpoint variables located in /sass/_settings/_breakpoints.scss

    // $small-screen: 464px
    // $small-screen-max: 607px

    // $narrow-screen: 608px
    // $narrow-screen-max: 767px

    // $weird-screen: 768px
    // $weird-screen-max: 895px

    // $medium-screen: 896px
    // $medium-screen-max: 991px

    // $standard-screen: 992px
    // $standard-screen-max: 1187px

    // $wide-screen: 1188px
    // $wide-screen-max: 1407px

    // $super-wide-screen: 1408px

    // simple state manager functions

    ssm.addState({
      id: 'small-screen',
      query: '(min-width: 464px)',
      onEnter: function() {
        // console.log('enter breakpoint');
      },
      onLeave: function() {
        // console.log('exit breakpoint');
      }
    });
  });

  // Force a resize event cuz Ian says so
  $(window).on('load', function(){
    $(window).resize();
    // Nav for ipad

  });

  $(window).on('resize', function(){
    var istablet = (/ipad|android|android 3.0|xoom|sch-i800|playbook|tablet|kindle/i.test(navigator.userAgent.toLowerCase()));
    if (istablet) {
      $('.responsive-menu-toggle').addClass('force-mobile');
      $('.block-responsive-menu-horizontal-menu').addClass('force-mobile');
    }
    else {
      $('.responsive-menu-toggle').removeClass('force-mobile');
      $('.block-responsive-menu-horizontal-menu').removeClass('force-mobile');
    }
  });

})(jQuery, Drupal);
