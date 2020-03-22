<?php
defined('C5_EXECUTE') or die('Access Denied.');

extract($vars);
?>

<div class="form-group">
  <?php echo $form->label('mollieApiKey', t('Enter your API Key')); ?>
  <?php echo $form->text('mollieApiKey', $apiKey); ?>
</div>

<div class="form-group">
  <?php echo $form->label('mollieOrderStatusOnCancel', t('Status of order when a payment is cancelled')); ?>
  <?php echo $form->select('mollieOrderStatusOnCancel', $statusList, $orderStatusOnCancel); ?>
</div>

<a href="<?php echo URL::to('/dashboard/store/settings/paymollie'); ?>" class="btn btn-default">
    <?php echo t('View Mollie Payment Methods'); ?>
</a>
