<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Config;

add_filter('show_admin_bar', '__return_false');

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Config\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

/**
 * [force_login description]
 * @return [type] [description]
 */
function force_login() {
  global $wp;
  $parts = explode("/", $wp->request);
  if($parts[0] != 'auth' && $wp->request != 'callback') {
    is_user_logged_in() || auth_redirect();
  }
}
add_action( 'parse_request', __NAMESPACE__ . '\\force_login', 1 );

/**
 * Filter media library items to those belonging to the current user,
 * if the user cannot edit others' posts.
 *
 * @param $wp_query
 * @return mixed
 */
function limit_media_library_to_own_items($wp_query) {
  if (is_admin() && $wp_query->get('post_type') == 'attachment' && !current_user_can('edit_others_posts')) {
      $wp_query->set('author', get_current_user_id());
  }
  return $wp_query;
}
add_filter('parse_query', __NAMESPACE__ . '\\limit_media_library_to_own_items');

/**
 * Hide "Back to Know The Thing"
 * @return void
 */
function hide_backtoblog() {
  ?>
  <style>
    #backtoblog {
      display: none;
    }
  </style>
  <?php
}
add_action('login_head',  __NAMESPACE__ . '\\hide_backtoblog');

/**
 * Configure 'Force Strong Passwords' plugin to only enforce
 * strong passwords for users with the returned capabilities.
 *
 * @param array $caps
 * @return array
 */
function fsp_caps_check($caps) {
  return array(
    'update_core',
  );
}
add_filter('slt_fsp_caps_check', __NAMESPACE__ . '\\fsp_caps_check');

/**
 * Redirect users to the frontend after login, unless a redirect URL
 * was specified.
 */
function login_redirect($redirect_to, $requested_redirect_to, $user) {
  if (get_class($user) == 'WP_User') {
    if (strpos($requested_redirect_to, '/wp-admin/') !== false) {
      $redirect_to = get_home_url();
    }
  }
  return $redirect_to;
}
add_filter('login_redirect', __NAMESPACE__ . '\\login_redirect', 10, 3);

/**
 * [image_upload description]
 * @return [type] [description]
 */
function image_upload() {
  if(empty(getimagesize($_FILES['file']['tmp_name']))) {
      header('HTTP/1.1 503 Service Unavailable');
      die();
  } else {
    $attachment_id = media_handle_upload( 'file', 0 );
    if ( !is_wp_error( $attachment_id ) ) {
      $image = wp_get_attachment_image_src( $attachment_id, 'large' );
      echo $image[0];
    }
  }
  die();
}
add_action('wp_ajax_image_upload', __NAMESPACE__ . '\\image_upload');
add_action('wp_ajax_nopriv_image_upload', __NAMESPACE__ . '\\image_upload');

function submit_form() {
  $output = $_POST;
  $nonce = wp_verify_nonce($_POST['ajax-nonce'], 'submit-nonce');
  if($nonce != 1 && $nonce != 2) {
    header('HTTP/1.1 503 Service Unavailable');
    die();
  }

  if ( !current_user_can('edit_posts') ) {
    header('HTTP/1.1 503 Service Unavailable');
    die();
  }

  if(empty($output['title']) || empty($output['content'])) {
    header('HTTP/1.1 503 Service Unavailable');
    die();
  }

  $output['content'] = htmlspecialchars_decode($output['content']);
  $output['content'] = strip_tags($output['content'],"<p><span><ul><li><ol><a><br/><br><img>");
  $output['content'] = str_replace('<span style=\"line-height: 1.42857143;\">', '<span>', $output['content']);
  $pattern = "#(?<=<p>|<p><span>)\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))(?=(</p>|<br></p>|</span><br></p>|</span></p>))#";

  preg_match_all($pattern, $output['content'], $matches);

  $youtube = '~
    ^(?:https?://)?              # Optional protocol
     (?:www\.)?                  # Optional subdomain
     (?:youtube\.com|youtu\.be)  # Mandatory domain name
     /watch\?v=([^&]+)           # URI with video id as capture group 1
     ~x';

  $vimeo = '~
    ^(?:https?://)?              # Optional protocol
     (?:www\.)?                  # Optional subdomain
     (?:vimeo\.com)              # Mandatory domain name
     /([^&]+)                    # URI with video id as capture group 1
     ~x';

  $notags = strip_tags(str_replace(" ", "", $output['content']));

  if(preg_match($youtube, $notags, $video_matches) != 0) {
    $video = $video_matches[0];
  } elseif(preg_match($youtube, $notags, $video_matches) != 0) {
    $video = $video_matches[0];
  }

  foreach($matches[0] as $match) {
    if(preg_match($youtube, $match, $video_matches) != 0) {
      $video = $video_matches[0];
      break;
    } elseif(preg_match($vimeo, $match, $video_matches) != 0) {
      $video = $video_matches[0];
      break;
    }
  }

  $output['content'] = preg_replace_callback($pattern, __NAMESPACE__ . '\\embed_convert', $output['content']);
  $post = [
    'post_title' => $output['title'],
    'post_content' => $output['content'],
    'post_status' => 'publish',
    'post_author' => get_current_user_id(),
    'filter' => true
  ];
  remove_all_filters("content_save_pre");
  $post_id = wp_insert_post($post);

  $post = get_post($post_id);
  preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $post->post_content, $matches);

  if(!empty($matches[1])) {
    foreach($matches[1] as $index => $match) {
      $parse = parse_url($match);
      if($parse['host'] == $_SERVER['HTTP_HOST']) {
        $image = get_image_id($match);
        if(!empty($image)) {
          $args = array(
            'ID' => $image,
            'post_parent' => $post_id
          );
          wp_update_post( $args );
          if($index == 0) {
            add_post_meta($post_id, '_thumbnail_id', $image);
          }
        }
      }
    }
  }

  if(isset($video) && !empty($video)) {
    update_post_meta( $post_id, 'video', $video);
  }
  echo get_permalink( $post_id );
  die();
}
add_action('wp_ajax_submit_form', __NAMESPACE__ . '\\submit_form');
add_action('wp_ajax_nopriv_submit_form', __NAMESPACE__ . '\\submit_form');



function wrap_embed_with_div($html, $url, $attr) {
     return '<div class="embed-responsive embed-responsive-16by9">' . $html . '</div>';
}
add_filter('embed_oembed_html', __NAMESPACE__ . '\\wrap_embed_with_div', 10, 3);


function embed_convert($matches) {
  if(!empty(wp_oembed_get($matches[0]))) {
    return '<div class="embed-responsive embed-responsive-16by9">' . wp_oembed_get($matches[0]) . '</div>';
  } else {
    return $matches[0];
  }
}


function get_image_id($image_url) {
  global $wpdb;
  $image_url = preg_replace('/-\d{2,4}x\d{2,4}/i', '', $image_url);
  $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
  if(!empty($attachment[0])) {
    return $attachment[0];
  }
}


function remove_width_attribute( $html ) {
   $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
   return $html;
}
add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\\remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', __NAMESPACE__ . '\\remove_width_attribute', 10 );


function no_mo_dashboard() {
  $url = parse_url(admin_url( ));
  if (!current_user_can('manage_options') && $_SERVER['DOING_AJAX'] != $url['path'] . 'admin-ajax.php') {
  wp_redirect(home_url()); exit;
  }
}
//add_action('admin_init', __NAMESPACE__ . '\\no_mo_dashboard');

function get_post_archive_months() {
  global $wpdb;

  $query = "SELECT MONTH(post_date) AS month,
                   YEAR(post_date) AS year,
                   COUNT(*) AS post_count
            FROM {$wpdb->posts}
            WHERE post_type = 'post'
            AND post_status = 'publish'
            GROUP BY MONTH(post_date), YEAR(post_date)
            ORDER BY post_date DESC";
  $months = $wpdb->get_results($query);

  $years = array();
  foreach ($months as $month) {
    if (!isset($years[$month->year])) {
      $years[$month->year] = array();
    }

    $years[$month->year][] = $month;
  }

  return $years;
}

/**
 * Show all posts at once for archive and search results pages.
 * Sets posts_per_page to -1.
 *
 * @param $query
 */
function show_all_posts($query) {
  if ($query->is_main_query() && !is_admin() && ($query->is_archive() || $query->is_search()) ) {
    $query->set('posts_per_page', '-1');
  }
}
add_action('pre_get_posts', __NAMESPACE__ . '\\show_all_posts');

function nicer_archive_title($title) {
  if (is_month()) {
    $title = get_the_date(_x('F Y', 'monthly archives date format'));
  }

  if (is_year()) {
    $title = get_the_date(_x('Y', 'yearly archives date format'));
  }

  return $title;
}
add_filter('get_the_archive_title', __NAMESPACE__ . '\\nicer_archive_title');
