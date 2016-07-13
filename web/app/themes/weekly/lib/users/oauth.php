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
 * @param $provider_scope
 * @param $provider
 * @return string
 */
function alter_provider_scope($provider_scope, $provider) {
  if (strtolower($provider) == 'google') {
    $provider_scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';
  }

  return $provider_scope;
}
add_filter('wsl_hook_alter_provider_scope', __NAMESPACE__ . '\\alter_provider_scope', 10, 2);
