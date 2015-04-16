<p class="byline author vcard"><?= __('By', 'sage'); ?>
  <a href="<?= get_author_posts_url(get_the_author_meta('ID')); ?>" rel="author" class="fn">
    <?= get_the_author(); ?>
  </a>
  <time class="updated" datetime="<?= get_the_time('c'); ?>"> about <?= human_time_diff( get_the_time('U')) . ' ago'; ?></time>
   | <a href="<?= the_permalink(); ?>#comments"><?= $c = get_comments_number(); ?> comment<?php if($c != 1): ?>s<?php endif; ?></a>
</p>
