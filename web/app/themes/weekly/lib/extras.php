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
 * [add_query_vars_filter description]
 * @param [type] $vars [description]
 */
function add_query_vars_filter($vars){
  $vars[] = "post_year";
  $vars[] = "post_week";
  return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\add_query_vars_filter');

/**
 * [custom_rewrite_basic description]
 * @return [type] [description]
 */
function custom_rewrite_basic() {
  add_rewrite_rule('^([0-9]+)/([0-9]+)/?', 'index.php?post_year=$matches[1]&post_week=$matches[2]', 'top');
}
add_action('init', __NAMESPACE__ . '\\custom_rewrite_basic');

/**
 * [get_week_posts description]
 * @param  [type] $week [description]
 * @param  [type] $year [description]
 * @return [type]       [description]
 */
function get_week_posts( $week = null, $year = null ) {
  if(empty($week) || empty($year)) {
    $week = date("W");
    $year = date("Y");
  }
  $dateFrom = $year . "W" . $week;
  $dateTo = $year . "W" . ($week+1);

  $args = [
    'post_type' => 'post',
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'posts_per_page' => -1,
    'date_query' => [
      [
        'after' => $dateFrom,
        'before' => $dateTo,
      ]
    ]
  ];
  $query = new \WP_Query($args);
  return $query;
}

/**
 * [test_input description]
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = esc_sql($data);
  return $data;
}

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
 * [disable description]
 * @return [type] [description]
 */
function disable() {
  if ( is_admin() ) {
    $userdata = wp_get_current_user();
    $user = new \WP_User($userdata->ID);
    if ( !empty( $user->roles ) && is_array( $user->roles ) && $user->roles[0] == 'administrator' )
      return true;
  }
  return false;
}
add_filter( 'show_password_fields', __NAMESPACE__ . '\\disable' );
add_filter( 'allow_password_reset', __NAMESPACE__ . '\\disable' );

/**
 * [hide_login_nav description]
 * @return [type] [description]
 */
function hide_login_nav()
{
    ?><style>#nav,#backtoblog,#loginform{display:none} </style><?php
}
//add_action( 'login_head',  __NAMESPACE__ . '\\hide_login_nav' );

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
  $pattern = "#(?<=<p>)\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))(?=(</p>|<br></p>))#";

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
