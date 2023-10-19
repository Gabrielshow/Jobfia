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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$content_id = (int)$_GET['id'];
		$content = GetContent($content_id);
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $content['title'];
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<h1><?php echo $content['title']; ?></h1>
	<p><?php echo $content['text']; ?></p>


<?php require_once("inc/footer.inc.php"); ?>