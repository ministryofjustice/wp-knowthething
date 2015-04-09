<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_recent_weeks">
  <h3>Recent Weeks</h3>
  <ul class="weeks">
  <?php
    global $wpdb;
    $weeks = $wpdb->get_results( "SELECT WEEK(post_date,1) AS week, YEAR(post_date) as year FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY week DESC" );
    foreach ( $weeks as $week ) {
      $posts_this_week = $wpdb->get_results( "SELECT ID FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish' AND WEEK(post_date,1) = '" . $week->week . "'" );
      $count = 0;
      foreach ( $posts_this_week as $post ) {
        $count++;
      }
      $week_start = new DateTime();
      $week_start->setISODate($week->year,$week->week);
      ?>
      <li><a href="/<?php echo $week->year . '/' . $week->week; ?>/"><?= $week_start->format('d/m/Y'); ?> <strong><?= $count ?></strong></a></li>
    <?php
    }
  ?>
  </ul>
</section>
<section class="widget widget_bug">
  <p><small>Spotted a bug? <a href="mailto:toby.schrapel@digital.justice.gov.uk">Email me</a></small></p>
</section>
<?php //dynamic_sidebar('sidebar-primary'); ?>

