<?php while (have_posts()) : the_post(); ?>
  <article>
    <?php get_template_part('templates/page', 'header'); ?>
    <?php get_template_part('templates/content', 'page'); ?>
  </article>
<?php endwhile; ?>
