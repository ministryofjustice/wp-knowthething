<?php

use Roots\Sage\Utils;

$datetime = new DateTime(get_post_time('r', true));
$datetime->setTimezone(new DateTimeZone(get_option('timezone_string')));
$human_date = Utils\human_date($datetime);
$attr_datetime = $datetime->format('c');
$attr_title = $datetime->format(get_option('date_format')) . ' at ' . $datetime->format(get_option('time_format'));

?>

<p class="byline author vcard"><?= __('By', 'sage'); ?>
  <a href="<?= get_author_posts_url(get_the_author_meta('ID')); ?>" rel="author" class="fn"><?= get_the_author(); ?></a>
  <time class="updated" datetime="<?= $attr_datetime; ?>" title="<?= $attr_title; ?>"> <?= $human_date; ?></time>
   | <a href="<?= the_permalink(); ?>#comments"><?= $c = get_comments_number(); ?> comment<?php if($c != 1): ?>s<?php endif; ?></a>
</p>
