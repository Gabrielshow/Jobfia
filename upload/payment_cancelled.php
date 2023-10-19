<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Payment Canceled";
	
	$single_page = 1;
	
	require_once ("inc/header.inc.php");
	
?>
	
	<h1><i class="fa fa-remove" style="color: #f03e3f"></i>  Payment Canceled</h1>
	
	<div class="alert alert-danger">
		<p>Sorry, your payment has failed, please try again.</p>
		<p>If you see this error message again, please <a href="<?php echo SITE_URL; ?>contact.php">contact us</a>.</p>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>