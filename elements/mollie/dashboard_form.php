<?php
defined('C5_EXECUTE') or die('Access Denied.');

extract($vars);
?>

<div class="form-group">
  <?php echo $form->label('mollieApiKey', t('Enter your API Key')); ?>
  <?php echo $form->text('mollieApiKey', $apiKey); ?>
</div>

<div class="small" style="color: #999;">
  <?php echo t('After adding the API key, go to the mollie settings page below the store settings to scan for available payment options.'); ?>
</div>

<div class="small" style="color: #999;">
  <p>
    <?php
    echo t('Most Mollie methods are direct online payment. The status after an order will be "Awaiting processing" and paid.');
    echo '<br/>';
    echo t('However, Bank transfer and SEPA is not paid immediately. On delayed payment, the status will be "Awaiting processing" and not paid.');
    echo '<br/>';
    echo '<strong>';
    echo t('Delayed payments (such as bank transfer and SEPA) have to be checked manually.');
    echo '</strong>';
    ?>
  </p>
</div>

<div class="form-group">
  <?php echo $form->label('mollieOrderStatusOnCancel', t('Status of order when a payment is cancelled')); ?>
  <?php echo $form->select('mollieOrderStatusOnCancel', $statusList, $orderStatusOnCancel); ?>
</div>

<div class="small" style="color: #999;">
  <strong>
    <?php echo t('Important: '); ?>
  </strong>
  <?php echo t('A cancelled order (on payment) will get the status chosen and will be flagged as unpaid.'); ?>
</div>
