<?php
  use Roots\Sage\Extras;
?>
<?php
  $image = get_the_post_thumbnail( $post->ID, 'image-thumb', array('class'=>'img-responsive') );
?>
<?php if($video = get_post_meta( $post->ID, 'video' )): ?>
<article <?php post_class(); ?>>
  <div class="row">
    <div class="col-md-6">
      <header>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span> <?php the_title(); ?></a></h2>
        <?php get_template_part('templates/entry-meta'); ?>
      </header>
      <div class="entry-summary">
        <?php the_excerpt(); ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="embed-responsive embed-responsive-16by9"><?= wp_oembed_get($video[0]) ?></div>
    </div>
  </div>
</article>
<?php elseif(!empty($image)): ?>
<article <?php post_class(); ?>>
  <div class="row">
    <div class="col-md-6">
      <header>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span> <?php the_title(); ?></a></h2>
        <?php get_template_part('templates/entry-meta'); ?>
      </header>
      <div class="entry-summary">
        <?php the_excerpt(); ?>
      </div>
    </div>
    <div class="col-md-6">
      <?= $image; ?>
    </div>
  </div>
</article>
<?php else: ?>
<article <?php post_class(); ?>>
  <header>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><span class="glyphicon glyphicon-file" aria-hidden="true"></span> <?php the_title(); ?></a></h2>
    <?php get_template_part('templates/entry-meta'); ?>
  </header>
  <div class="entry-summary">
    <?php the_excerpt(); ?>
  </div>
</article>
<?php endif; ?>
