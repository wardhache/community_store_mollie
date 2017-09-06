<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
?>
<?php
  if(empty($apiKey)){
    echo '<h4 style="color:red;">';
      echo t('Please set the API Key for mollie in the settings.');
    echo '</h4>';
  }else{
    ?>
    <h4>
      <?php
        echo t('Current mollie payment methods:')
      ?>
    </h4>
    <?php
      if(!empty($molliemethods)){
        echo '<table class="table table-striped">';
        echo '<tr><th style="width: 50px;">';
        echo '</th><th>';
        echo t('Name');
        echo '</th><th>';
        echo t('Minimum');
        echo '</th><th>';
        echo t('Maximum');
        echo '</th></tr>';
        foreach($molliemethods as $pmethod){
          echo '<tr>';
            echo '<td>';
              echo '<img src="'.$pmethod['pImage'].'"/>';
            echo '</td>';
            echo '<td>';
              echo $pmethod['pTitle'];
            echo '</td>';
            echo '<td>';
              echo $pmethod['pMinimum'];
            echo '</td>';
            echo '<td>';
              echo $pmethod['pMaximum'];
            echo '</td>';
          echo '</tr>';
        }
        echo '</table>';
      }
    ?>
    <form action="<?php echo $this->action('rescan'); ?>" method="post">
      <?php echo $form->submit('rescan', t('Rescan'), array('class' => 'btn btn-primary')); ?>
    </form>
    <p class="small">
      <?php
        echo t('Login to your mollie account to enable or disable the options of payment. Afterwards, click the rescan button on this page to load the methods.');
      ?>
    </p>
    <?php
  }
?>
