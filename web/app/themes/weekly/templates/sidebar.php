<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_recent_weeks">
  <h3>Recent Weeks</h3>
  <?php
    global $wpdb;
    $months = [];
    $current_month = "";
    $weeks = $wpdb->get_results( "SELECT WEEK(post_date,1) AS week, YEAR(post_date) as year FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY week DESC" );
    foreach ( $weeks as $index => $week ) {
      $week_start = new DateTime();
      $week_start->setISODate($week->year,$week->week);
      $month = $week_start->format('m');
    ?>
    <?php if(!in_array($month, $months)): $months[] = $month; ?>
      <a href="#" class="months-link" data-month="week-<?= $month; ?>"><?= $week_start->format('F'); ?></a>
      <ul class="weeks week-<?= $month; ?><?php if(date("m") == $month): ?> active<?php endif; ?>">
    <?php endif; ?>

    <?php
      $posts_this_week = $wpdb->get_results( "SELECT ID FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish' AND WEEK(post_date,1) = '" . $week->week . "'" );
      $count = 0;
      foreach ( $posts_this_week as $post ) {
        $count++;
      }
      ?>
      <li><a href="/<?php echo $week->year . '/' . $week->week; ?>/"><?= $week_start->format('d/m/Y'); ?> <strong><?= $count ?></strong></a></li>
    <?php

    $next_week = new DateTime();
    $next_week->setISODate($weeks[$index+1]->year,$weeks[$index+1]->week);
    $next_month = $next_week->format('m');
    if($next_month != $month) {
      echo "</ul>";
    }

    }
  ?>

  </ul>
</section>
<section class="widget widget_bug">
  <p><small>Spotted a bug? <a href="mailto:toby.schrapel@digital.justice.gov.uk">Email me</a></small></p>
</section>
<?php //dynamic_sidebar('sidebar-primary'); ?>

