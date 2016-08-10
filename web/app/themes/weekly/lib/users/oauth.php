<?php
/**
 * Configuration for Google oAuth login.
 * Login functionality is provided by WordPress Social Login plugin.
 * http://miled.github.io/wordpress-social-login/
 */

namespace Roots\Sage\Users\OAuth;

/**
 * Alter the provider scope.
 * The default scope for Google requires too many permissions.
 *
 * @param string $provider_scope
 * @param string $provider
 * @return string
 */
function alter_provider_scope($provider_scope, $provider) {
  if (strtolower($provider) == 'google') {
    $provider_scope = 'profile email';
  }

  return $provider_scope;
}
add_filter('wsl_hook_alter_provider_scope', __NAMESPACE__ . '\\alter_provider_scope', 10, 2);

/**
 * Alter the provider configuration.
 *
 * @param array $config
 * @param string $provider
 * @return array
 */
function alter_provider_config($config, $provider) {
  if (strtolower($provider) == 'google') {
    $config['access_type'] = 'online';
  }

  return $config;
}
add_filter('wsl_hook_alter_provider_config', __NAMESPACE__ . '\\alter_provider_config', 10, 2);

function alter_provider_icon_markup($provider_id, $provider_name, $authenticate_url) {
  ?>
  <a rel="nofollow" href="<?php echo $authenticate_url; ?>" data-provider="<?php echo $provider_id ?>" class="oauth-btn oauth-btn-<?php echo strtolower($provider_id); ?>">
    Log in with <?php echo $provider_name; ?>
  </a>
  <?php
}
add_filter('wsl_render_auth_widget_alter_provider_icon_markup', __NAMESPACE__ . '\\alter_provider_icon_markup', 10, 3);

function add_social_login_buttons($message) {
  global $action;

  if ($action == 'login' || $action == 'register') {
    $social_login = do_shortcode('[wordpress_social_login caption=""]');
    return $message . $social_login;
  }
  else {
    return $message;
  }
}
add_filter('login_message', __NAMESPACE__ . '\\add_social_login_buttons');
