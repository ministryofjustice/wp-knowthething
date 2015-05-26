<?php
use Roots\Sage\Extras;
if(!empty(get_query_var('post_week')) && !empty(get_query_var('post_year'))) {
  $query = Extras\get_week_posts(get_query_var('post_week'), get_query_var('post_year'));
} elseif(!empty(get_query_var('s'))) {
  $value = Extras\test_input(get_query_var('s'));
  $args = [
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    's' => $value,
  ];
  $query = new \WP_Query($args);
} else {
  $query = $wp_query;
}
?>

<?php if (!$query->have_posts()) : ?>
  <div class="alert alert-warning">
    <?php _e('Sorry, no results were found.', 'sage'); ?>
  </div>
<?php endif; ?>

<?php if ($query->have_posts()) : ?>
  <?php if( !empty(get_query_var('post_year')) && !empty(get_query_var('post_week')) ): ?>
    <?php
      if(!empty(get_query_var('post_year')) && !empty(get_query_var('post_week'))) {
        $timestamp = strtotime(get_query_var('post_year') . "W" . get_query_var('post_week'));
      } elseif(date('w') == 1) {
        $timestamp = time();
      } else {
        $timestamp = strtotime("last monday");
      }
      $weekBeginning = date('d/m/Y', $timestamp);
    ?>
    <h2 class="week-beginning" id="<?= $timestamp ?>"><?= $weekBeginning ?></h2>
  <?php elseif( is_front_page() ): ?>
    <h2 class="week-beginning">Latest Posts</h2>
    <div class="btn-group category-drop">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Category <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <?php
        $categories = get_categories();
        foreach ($categories as $category) {
          if($category->term_id != 1){
            $option .= '<li><a href="'.get_option('home').'/category/'.$category->slug.'">';
            $option .= $category->cat_name;
            $option .= ' ('.$category->category_count.')';
            $option .= '</a></li>';
          }
        }
        echo $option;
        ?>
      </ul>
    </div>

  <?php endif; ?>

  <?php while ($query->have_posts()) : $query->the_post(); ?>
    <?php get_template_part('templates/content', get_post_type() != 'post' ? get_post_type() : get_post_format()); ?>
  <?php endwhile; ?>
<?php endif; ?>
