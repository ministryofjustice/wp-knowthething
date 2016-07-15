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
