<header>
  <h1 class="entry-title"><?php the_title(); ?></h1>
</header>

<div class="entry-content">
  <?php the_content(); ?>
</div>

<?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
