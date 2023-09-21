<?php

/*
* include necessary files
*/

require_once( 'includes/global.inc.php' );

// destroy user session
(new CookieHandler) -> unset_user( );

$redirect_delay = 5000;

/*
* set header stuff
*/

$g_str_script_block = "

  function onloadHandler()
  {
    xgene360_cu.redirect( './login.php', $redirect_delay );
  }
  
  xgene360_cu.using( 'event' );
  xgene360_cu.event.addDOMListener( window, 'onload', onloadHandler );
";

require_once( 'includes/header.inc.php' );

?>

<!-- Start Content -->
<div class="centered">

  <h3>Thank you for using XGene 360. You have been successfully logged out.</h3><br />
  If this page <strong>does not</strong> redirect you in <?php echo $redirect_delay/1000 ?> secs, please <strong><a href="login.php">click here</a></strong>.

</div>
<!-- End Content -->

<?php

require_once( 'includes/footer.inc.php' );

?>