<?php
defined('C5_EXECUTE') or die('Access Denied.');
extract($vars);
?>

<?php if ($methods) { ?>
    <?php foreach ($methods as $method) { ?>
        <div class="form-group">
            <div class="radio">
                <label>
                    <input type="radio" id="molliePaymentMethod<?php echo $method->getID(); ?>"
                           value="<?php echo $method->getMollieID(); ?>" name="molliePaymentMethod"/>
                    <img src="<?php echo $method->getImage(); ?>"
                         style="height: 24px; width: auto;"/> <?php echo $method->getTitle(); ?>
                </label>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="alert alert-danger">
        <?php echo t('Error: No payment options available. Please contact the website owner.'); ?>
    </div>
<?php } ?>
