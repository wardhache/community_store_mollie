<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if ($status === 'rescanned') { ?>
    <div class="alert alert-success">
        <?php echo t('Payment methods are successfully updated.'); ?>
    </div>
<?php } ?>

<?php if (!empty($methods)) { ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th></th>
            <th><?php echo t('Name'); ?></th>
            <th><?php echo t('Minimum'); ?></th>
            <th><?php echo t('Maximum'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($methods as $method) { ?>
            <tr>
                <td><img src="<?php echo $method->getImage(); ?>"/></td>
                <td><?php echo $method->getTitle(); ?></td>
                <td><?php echo $method->getMinimum(); ?></td>
                <td><?php echo $method->getMaximum(); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>

<div class="ccm-dashboard-header-buttons">
    <form action="<?php echo $this->action('rescan'); ?>" class="form-inline" method="post">
        <?php echo $form->submit('rescan', t('Rescan'), array('class' => 'btn btn-primary')); ?>

        <a href="<?= Url::to('/dashboard/store/settings#settings-payments') ?>" class="btn btn-default">
            <i class="fa fa-gear"></i> <?= t("General Settings") ?>
        </a>
    </form>
</div>