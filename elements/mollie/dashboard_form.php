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
  <?php
  echo t('Most Mollie methods are direct online payment. The status after an order should be "Processing".');
  echo '<br/>';
  echo t('However, Bank transfer is not paid immediately. The status above will be set on Bank transfer payments.');
  ?>
</div>
<div class="form-group">
  <?=$form->label('orderDeleteOnCancel', 'Cancel order when a payment is cancelled');?>
  <?=$form->select('orderDeleteOnCancel', array('No', 'Yes'), $orderDeleteOnCancel);?>
</div>
<div class="small" style="color: #999;">
  <strong>
    <?php echo t('Important: '); ?>
  </strong>
  <?php echo t('Normally a cancelled order will remain on the standard status and is unpaid.'); ?>
</div>
