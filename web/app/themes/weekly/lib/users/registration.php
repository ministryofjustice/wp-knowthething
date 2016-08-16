<?php

namespace Roots\Sage\Users\Registration;

use WP_Error;

/**
 * Return an array of valid domain names for user registration.
 *
 * @return array
 */
function valid_email_domains() {
  return array(
    'justice.gsi.gov.uk',
    'digital.justice.gov.uk',
    'official.justice.gov.uk',
    'justice.gov.uk',
  );
}

/**
 * Add fields to registration form
 */
function register_form() {
  $first_name = (!empty($_POST['first_name'])) ? trim($_POST['first_name']) : '';
  $last_name = (!empty($_POST['last_name'])) ? trim($_POST['last_name']) : '';
  ?>
  <p>
    <label for="first_name"><?php _e('First Name'); ?><br />
      <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr(wp_unslash($first_name)); ?>" /></label>
  </p>
  <p>
    <label for="last_name"><?php _e('Last Name'); ?><br />
      <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr(wp_unslash($last_name)); ?>" /></label>
  </p>
  <?php
}
add_action('register_form', __NAMESPACE__ . '\\register_form');

/**
 * Validate fields
 *
 * @param WP_Error $errors
 * @param string $sanitized_user_login
 * @param string $user_email
 * @return WP_Error Updated WP_Error object
 */
function registration_errors(WP_Error $errors, $sanitized_user_login, $user_email) {
  // Ensure email address belongs to a valid domain
  if (!email_domain_is_valid($_POST['user_email'])) {
    $errors->add('email_domain_error', __('<strong>ERROR</strong>: You must register with a MOJ email address.', 'mydomain'));
  }

  // First Name cannot be empty
  if (empty($_POST['first_name']) || !empty($_POST['first_name']) && trim($_POST['first_name']) == '') {
    $errors->add('first_name_error', __('<strong>ERROR</strong>: Please enter your first name.', 'mydomain'));
  }

  // Last Name cannot be empty
  if (empty($_POST['last_name']) || !empty($_POST['last_name']) && trim($_POST['last_name']) == '') {
    $errors->add('last_name_error', __('<strong>ERROR</strong>: Please enter your last name.', 'mydomain'));
  }

  // Remove username-related error messages
  if (isset($errors->errors['empty_username'])) {
    unset($errors->errors['empty_username']);
  }

  if (isset($errors->errors['username_exists'])) {
    unset($errors->errors['username_exists']);
  }

  // Make the 'email exists' error message more helpful.
  if (isset($errors->errors['email_exists'])) {
    $message = '<strong>ERROR</strong>: It looks like you’ve already registered with this email address.<br/><br/>';
    $message .= 'Please <a href="' . esc_url(wp_login_url()) .'">log in</a> instead.';

    $errors->errors['email_exists'][0] = __($message);
  }

  return $errors;
}
add_filter('registration_errors', __NAMESPACE__ . '\\registration_errors', 10, 3);

/**
 * Test if the supplied email address belongs to a valid domain.
 * To change the list of valid domains, see valid_email_domains()
 *
 * @param string $email
 * @return bool
 */
function email_domain_is_valid($email) {
  $valid_domains = valid_email_domains();

  foreach ($valid_domains as $domain) {
    $regex = '/\@' . str_replace('.', '\.', $domain) . '$/i';
    if (preg_match($regex, $email)) {
      return true;
    }
  }

  return false;
}

/**
 * Save fields to new user
 *
 * @param int @user_id
 */
function user_register($user_id) {
  if (!empty($_POST['first_name'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    wp_update_user([
      'ID' => $user_id,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'nickname' => $first_name,
      'display_name' => $first_name . ' ' . $last_name,
    ]);
  }
}
add_action('user_register', __NAMESPACE__ . '\\user_register');

function set_username_to_email() {
  if (isset($_POST['user_login_is_email']) && isset($_POST['user_email']) && !empty($_POST['user_email'])) {
    $_POST['user_login'] = sanitize_user($_POST['user_email'], true);
    unset($_POST['user_login_is_email']);
  }
}
add_action('login_form_register', __NAMESPACE__ . '\\set_username_to_email');

/**
 * Custom scripts and styles for login pages
 */
function login_head() {
  ?>
  <script>
    jQuery(document).ready(function($) {
      var register_form = $('#registerform');

      // Form inputs
      var email_in = $('#user_email'),
          first_name_in = $('#first_name'),
          last_name_in = $('#last_name');

      /**
       * Helper method to capitalize the first character of a string.
       *
       * @param string string
       * @returns string
       */
      var capitalize = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      };

      /**
       * Extract first and last name from an email address.
       *
       * @param email string
       * @returns object
       */
      var names_from_email = function(email) {
        var matches = email.match(/^([a-z]+)\.([a-z]+).*\@/i);
        if (!matches || matches.length !== 3) {
          return false;
        }
        else {
          return {
            first: capitalize(matches[1]),
            last: capitalize(matches[2])
          };
        }
      };

      /**
       * Attempt to auto-populate the first and last name fields
       * using the email address.
       */
      var autofill = function() {
        if (first_name_in.val() !== '' || last_name_in.val() !== '') {
          return;
        }

        var names = names_from_email(email_in.val());
        if (names) {
          first_name_in.val(names.first);
          last_name_in.val(names.last);
        }
      };

      if (register_form.length > 0) {
        email_in.on('blur', autofill);
      }
    });
  </script>
  <?php
}
add_action('login_head', __NAMESPACE__ . '\\login_head');
