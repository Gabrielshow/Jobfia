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
	$PAGE_TITLE = "Payment Successful";
	
	$single_page = 1;
	
	require_once ("inc/header.inc.php");
	
?>

	<?php if ($_GET['offer'] && $_GET['offer'] == 1) { ?>
	
		<h1><i class="fa fa-check-circle-o" style="color: #7ad108"></i> Thank you for purchase</h1>
		
		<div class="alert alert-success">
			<p>Thank you for offer purchase! User will receive your request soon.</p>
		</div>
		
		<p>
			<a class="btn btn-success" href="<?php echo SITE_URL; ?>myoffers.php">Go to My Offers  <i class="fa fa-angle-right"></i></a>
			<a class="btn btn-info" href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-user-circle-o"></i>  Go to My Account</a>
		</p>	
	
	<?php }else{ ?>
	
		<h1><i class="fa fa-check-circle-o" style="color: #7ad108"></i> Payment Successful</h1>
		
		<div class="alert alert-success">
			<p>Thank you for payment! Your account balance has been successfully updated.</p>
		</div>
		
		<p>
			<a class="btn btn-success" href="<?php echo SITE_URL; ?>mypayments.php">Go to My Payments  <i class="fa fa-angle-right"></i></a>
			<a class="btn btn-info" href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-user-circle-o"></i>  Go to My Account</a>
		</p>	
	
	<?php } ?>



<?php require_once ("inc/footer.inc.php"); ?>