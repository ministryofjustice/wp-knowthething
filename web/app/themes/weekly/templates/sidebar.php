<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_recent_weeks">
  <h3>Recent Weeks</h3>
  <?php
    $weeks = array();
    $posts = get_posts(array(
      'numberposts' => -1,
      'orderby' => 'post_date',
      'order' => 'ASC',
      'post_type' => 'post',
      'post_status' => 'publish'
    ));

    foreach($posts as $post) {
      $weeks[date('Y/W', strtotime($post->post_date))][] = $post;
    }
    krsort($weeks);

  ?>
  <ul class="weeks">
  <?php foreach($weeks as $week => $posts) : ?>
    <?php $split = explode('/', $week); ?>
    <?php $count = 0; ?>
    <?php foreach($posts as $post) : setup_postdata($post); ?>
      <?php $count++; ?>
    <?php endforeach; ?>
    <?php
    $week_start = new DateTime();
    $week_start->setISODate($split[0],$split[1]);
    ?>
    <li><a href="/<?= $week ?>/"><?= $week_start->format('d/m/Y'); ?> <strong><?= $count ?></strong></a></li>
  <?php endforeach; ?>
  </ul>
</section>
<section class="widget widget_bug">
  <p><small>Spotted a bug? <a href="mailto:toby.schrapel@digital.justice.gov.uk">Email me</a></small></p>
</section>
<?php //dynamic_sidebar('sidebar-primary'); ?>

