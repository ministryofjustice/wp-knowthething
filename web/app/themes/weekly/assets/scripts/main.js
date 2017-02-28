/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * Google CDN, Latest jQuery
 * To use the default WordPress version of jQuery, go to lib/config.php and
 * remove or comment out: add_theme_support('jquery-cdn');
 * ======================================================================== */

(function($) {

  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var Sage = {
    // All pages
    'common': {
      init: function() {
        // JavaScript to be fired on all pages
      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    },
    // Home page
    'home': {
      init: function() {
        // JavaScript to be fired on the home page
      },
      finalize: function() {
        // JavaScript to be fired on the home page, after the init JS
      }
    },
    // About us page, note the change from about-us to about_us.
    'about_us': {
      init: function() {
        // JavaScript to be fired on the about us page
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = Sage;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      UTIL.fire('common');

      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
        UTIL.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      UTIL.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.

jQuery(document).ready(function($) {

  var ajaxurl = typeof SageJS !== 'undefined' ? SageJS.ajaxurl : "nothing";

  var uploadImage = function(file, editor) {
    data = new FormData();
    data.append("file", file);
    data.append('action', 'image_upload');
    $.ajax({
      data: data,
      type: "POST",
      url: ajaxurl,
      cache: false,
      contentType: false,
      processData: false,
      success: function(url) {
        $(editor).summernote('editor.insertImage', url);
      }
    });
  };

  $('#summernote').summernote({
    height: 300,
    toolbar: [
      ['style', ['bold', 'italic', 'underline']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['insert', ['picture', 'link', 'video']], // Fix video
    ],
    onImageUpload: function(files) {
      for (i = 0; i < files.length; i++) {
        uploadImage(files[i], this);
      }
    }
  });

  $('.modal-submit').click(function() {
    if($('.add-modal .entry-form:visible').hasClass('standard')) {
      $('textarea[name="content"]').html($('#summernote').code());
    }
    $('.add-modal .entry-form .data_form').submit();
  });

  $('.add-modal .entry-form form').submit(function( event ) {
    $('.modal-submit').prop("disabled", true);
    $('.modal-submit').html('Submitting...');
    $('#error').hide();
    var form = $(this)[0];
    var fd = new FormData(form);
    fd.append('action', 'submit_form');

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: fd,
      processData: false,
      contentType: false,
      success: function(data) {
        //console.log(data);
        window.location.href = data;
      },
      error: function(data) {
        $('#error').show();
        $('.modal-submit').prop("disabled", false);
        $('.modal-submit').html('Submit');
      }
    });

    event.preventDefault();
  });

});
