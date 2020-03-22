<?php
defined('C5_EXECUTE') or die('Access Denied.');

extract($vars);
?>

<div class="form-group">
  <label><?= t('Api Key')?></label>
  <?php echo $form->text('mollieApiKey', $apiKey); ?>
</div>

<div class="form-group">
  <label><?= t('Status of order when a payment is cancelled')?></label>
  <?php echo $form->select('mollieOrderStatusOnCancel', $statusList, $orderStatusOnCancel); ?>
</div>

<a href="<?php echo URL::to('/dashboard/store/settings/paymollie'); ?>" class="btn btn-default">
    <?php echo t('View Mollie Payment Methods'); ?>
</a>
