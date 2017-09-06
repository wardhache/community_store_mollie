<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);

$form = Core::Make('helper/form');
$statusList = \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus::getList();
?>
<div class="form-group">
  <?=$form->label('apiKey',t('Enter your API Key'))?>
  <?=$form->text('apiKey',$apiKey)?>
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
  <?=$form->label('orderStatusOnCancel', t('Status of order when a payment is cancelled'));?>
  <?=$form->select('orderStatusOnCancel', $statusList, $orderStatusOnCancel);?>
</div>
<div class="small" style="color: #999;">
  <strong>
    <?php echo t('Important: '); ?>
  </strong>
  <?php echo t('A cancelled order (on payment) will get the status chosen and will be flagged as unpaid.'); ?>
</div>
