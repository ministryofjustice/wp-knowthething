<?php

namespace Roots\Sage\Users\Registration;

use WP_Error;

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

  return $errors;
}
add_filter('registration_errors', __NAMESPACE__ . '\\registration_errors', 10, 3);

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
    $_POST['user_login'] = $_POST['user_email'];
    unset($_POST['user_login_is_email']);
  }
}
add_action('login_form_register', __NAMESPACE__ . '\\set_username_to_email');
