<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	require_once("inc/config.inc.php");

	if (!(isset($_SESSION['userid']) && is_numeric($_SESSION['userid'])))
	{
		$_SESSION["login_redirect"] = $_SERVER["REQUEST_URI"];
		 
		// check cookie
		if (!CheckCookieLogin())
		{
			header("Location: login.php?msg=3");
			exit();
		}
		
		header("Location: login.php?msg=3");
		exit();
	}
	else
	{
		$userid	= (int)$_SESSION['userid'];
	}

?>