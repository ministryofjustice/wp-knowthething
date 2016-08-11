<?php

use Roots\Sage\Extras;

$archive_years = Extras\get_post_archive_months();

/**
 * Determine the currently active year, if on an archive page.
 */
if (is_archive()) {
  $active_month = get_query_var('monthnum');
  $active_year = get_query_var('year');
}
else {
  $active_month = false;
  $active_year = false;
}

/**
 * Determine which year to expand by default.
 */
if (isset($archive_years[$active_year])) {
  $expand_year = $active_year;
}
else if (count($archive_years) > 0) {
  $keys = array_keys($archive_years);
  $expand_year = array_shift($keys);
}
else {
  $expand_year = false;
}

?>

<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_archives">
  <h3>Monthly Archives</h3>
  <div class="panel-group" id="archive" role="tablist" aria-multiselectable="true">
    <?php foreach ($archive_years as $year => $months): ?>
      <?php $expand = ($year == $expand_year); ?>
      <div class="panel panel-dark">
        <div class="panel-heading" role="tab" id="heading<?php echo $year; ?>">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#archive" href="#archive<?php echo $year; ?>" aria-expanded="<?php echo $expand ? 'true' : 'false'; ?>" aria-controls="archive<?php echo $year; ?>" class="<?php echo $expand ? '' : 'collapsed'; ?>">
              <?php echo $year; ?>
            </a>
          </h4>
        </div>
        <div id="archive<?php echo $year; ?>" class="panel-collapse collapse<?php echo $expand ? ' in' : ''; ?>" role="tabpanel" aria-labelledby="heading<?php echo $year; ?>">
          <div class="btn-group-vertical" style="width:100%;">
            <?php foreach ($months as $month): ?>
              <?php
              $date = new DateTime($month->year . '-' . zeroise($month->month, 2) . '-01');
              $active = ($active_month == $month->month && $active_year == $month->year);
              ?>
              <a href="<?php echo get_month_link($month->year, $month->month); ?>" class="btn btn-dark btn-block btn-justified-badge<?php echo $active ? ' active' : ''; ?>">
                <span class="btn-label"><?php echo $date->format('F'); ?></span>
                <span class="badge"><?php echo $month->post_count; ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<section class="widget widget_bug">
  <small>Spotted a bug? <a href="https://github.com/ministryofjustice/wp-weekly/issues" target="_blank">Report it here.</a></small>
</section>
