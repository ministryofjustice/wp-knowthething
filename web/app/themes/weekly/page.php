<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class('single single_page'); ?>>
    <?php get_template_part('templates/content', 'page'); ?>
  </article>
<?php endwhile; ?>
