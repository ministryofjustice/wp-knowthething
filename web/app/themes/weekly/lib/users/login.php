<?php

namespace Roots\Sage\Users\Login;

/**
 * Custom scripts and styles for login pages
 */
function login_head() {
  ?>
  <style>
    /* Hide the 'Username' field on the registration form */
    #registerform #user_login {
      display: none;
    }
  </style>

  <script>
    jQuery(document).ready(function($) {
      var user_login = $('#user_login');
      if (user_login.length > 0 && user_login.attr('type') == 'text') {
        user_login.attr('type', 'email');
      }

      var register_form = $('#registerform');
      if (register_form.length > 0) {
        $('#user_login').parents('p').remove();
        register_form.prepend('<input type="hidden" value="1" name="user_login_is_email" />');
      }
    });
  </script>
  <?php
}
add_action('login_head', __NAMESPACE__ . '\\login_head');

/**
 * Add jQuery to login pages
 */
function enqueue_jquery() {
  wp_enqueue_script('jquery');
}
add_action('login_enqueue_scripts', __NAMESPACE__ . '\\enqueue_jquery');

/**
 * Rename form fields
 * This is done to replace references to 'username' with 'email address'.
 */
function login_form() {
  add_filter('gettext', __NAMESPACE__ . '\\login_form_gettext', 20, 3);
}
add_action('login_form_login', __NAMESPACE__ . '\\login_form');
add_action('login_form_register', __NAMESPACE__ . '\\login_form');
add_action('login_form_lostpassword', __NAMESPACE__ . '\\login_form');
add_action('login_form_retrievepassword', __NAMESPACE__ . '\\login_form');

function login_form_gettext($translated_text, $text, $domain) {
  switch ($translated_text) {
    case 'Email':
      $translated_text = 'Email Address';
      break;

    case 'Username or Email':
      $translated_text = 'Email Address';
      break;

    case '<strong>ERROR</strong>: Enter a username or email address.':
      $translated_text = '<strong>ERROR</strong>: Enter an email address.';
      break;
  }

  return $translated_text;
}
