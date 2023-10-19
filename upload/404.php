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
	$PAGE_TITLE = "Page not found";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	
?>

	<h1 class="text-center">Page not found</h1>

	<p align="center"><img src="<?php echo SITE_URL; ?>images/404.png" /></p>
	<h3 class="text-center">Sorry, the page you are looking for could not be found.</h3>
	<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>"><i class="fa fa-chevron-left"></i> Go back to home</a></p>


<?php require_once ("inc/footer.inc.php"); ?>