<?php

use Roots\Sage\Extras;

?>

<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_recent_weeks">
  <h3>Monthly Archives</h3>
  <?php
  $years = Extras\get_post_archive_months();
  $first = true;
  ?>
  <div class="panel-group" id="archive" role="tablist" aria-multiselectable="true">
    <?php foreach ($years as $year => $months): ?>
      <div class="panel panel-dark">
        <div class="panel-heading" role="tab" id="heading<?php echo $year; ?>">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#archive" href="#archive<?php echo $year; ?>" aria-expanded="<?php echo $first ? 'true' : 'false'; ?>" aria-controls="archive<?php echo $year; ?>" class="<?php echo $first ? '' : 'collapsed'; ?>">
              <?php echo $year; ?>
            </a>
          </h4>
        </div>
        <div id="archive<?php echo $year; ?>" class="panel-collapse collapse<?php echo $first ? ' in' : ''; ?>" role="tabpanel" aria-labelledby="heading<?php echo $year; ?>">
          <div class="btn-group-vertical" style="width:100%;">
            <?php foreach ($months as $month): ?>
              <?php
              $date = new DateTime($month->year . '-' . zeroise($month->month, 2) . '-01');
              ?>
              <a href="<?php echo get_month_link($month->year, $month->month); ?>" class="btn btn-dark btn-block btn-align-left">
                <?php echo $date->format('F'); ?>
                <span class="badge pull-right"><?php echo $month->post_count; ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php if ($first) $first = false; ?>
    <?php endforeach; ?>
  </div>
</section>
<section class="widget widget_bug">
  <small>Spotted a bug? <a href="https://github.com/ministryofjustice/wp-weekly/issues" target="_blank">Report it here.</a></small>
</section>
