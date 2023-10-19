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
	require_once("./inc/adm_functions.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pn		= (int)$_GET['pn'];
		$userid = (int)$_GET['id'];
  
		DeleteUser($userid);
		
		header("Location: users.php?msg=deleted&page=".$pn."&column=".$_GET['column']."&order=".$_GET['order']);
		exit();
	}

?>