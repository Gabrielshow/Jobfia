<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_adm.inc.php");
	require_once("../inc/config.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$content_id = (int)$_GET['id'];
		
		smart_mysql_query("DELETE FROM jobfia_content WHERE content_id='$content_id' AND name='page'");
		
		header("Location: content.php?msg=deleted");
		exit();
	}

?>