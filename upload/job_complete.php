<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Job Complete";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	require_once ("inc/usermenu.inc.php");

?>

	<!-- auto check //show green // paid auto //dev -->

	<h1><i class="fa fa-check-circle-o" style="color: #5ac0de"></i> Thank you for finishing the job!</h1>

	<?php if ($_GET['status'] == 'paid') { ?>
	<p>Congratulation! You have successfully complete the job and earned money!</p>
	<?php }else{ ?>
	<div class="well">
		<i class="fa fa-user-circle fa-lg"></i> Employer will review your submitted task soon.<br/>
		If you meet the requirements set by employer, you will get paid.
	</div>
	<?php } ?>

	<p>
		<a class="btn btn-info" href="<?php echo SITE_URL; ?>jobs.php"><i class="fa fa-briefcase fa-lg"></i> See other available jobs</a> 
		<a class="btn btn-info" href="<?php echo SITE_URL; ?>jobs_finished.php"><i class="fa fa-check-circle-o fa-lg"></i> See jobs you finished</a>
	</p>


<?php require_once ("inc/footer.inc.php"); ?>