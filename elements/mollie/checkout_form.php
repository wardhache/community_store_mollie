<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);


if(!empty($molliemethods)){

  echo t('Click on "Complete Order" to start the payment process.<br/>');
  echo t('In the next screen you will be able to choose between these payment methods:<br/>');
  foreach($molliemethods as $mMethod){
    echo '<img src="'.$mMethod['pImage'].'" title="'.$mMethod['pTitle'].'"/> ';
  }
}else{
  echo '<p>';
  echo t('Error: No payment options available. Please contact the website owner.');
  echo '</p>';
}

?>
