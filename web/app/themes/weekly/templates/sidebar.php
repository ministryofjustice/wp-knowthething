<section class="widget widget_add">
  <a href="#" class="btn btn-primary btn-block add-btn" data-toggle="modal" data-target=".add-modal">
    Add <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
  </a>
</section>
<section class="widget widget_recent_weeks">
  <h3>Recent Weekly</h3>
  <?php
    $past = 10; // Go back 10 weeks
    $currentWeek = date("W");
    $old_time = time();
  ?>
  <ul>
  <?php if(date('w', $old_time) == 1): ?>
    <li>
      <a href="/<?= date("Y", $old_time); ?>/<?= date("W", $old_time); ?>/"><?= date("d/m/Y", $old_time) ?></a>
    </li>
    <?php $past = 9; ?>
  <?php endif; ?>
  <?php for($i=0; $i < $past; $i++): ?>
    <?php $time = strtotime("last Monday", $old_time); ?>
    <li>
      <a href="/<?= date("Y", $time); ?>/<?= date("W", $time); ?>/"><?= date("d/m/Y", $time) ?></a>
    </li>
    <?php $old_time = $time; ?>
  <?php endfor; ?>
  </ul>
</section>
<?php //dynamic_sidebar('sidebar-primary'); ?>

