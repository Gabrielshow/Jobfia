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
		$pn			= (int)$_GET['pn'];
		$report_id	= (int)$_GET['id'];

		DeleteReport($report_id);

		if ($_GET['type'] == "jobs")
		{
			header("Location: job_reports.php?msg=deleted&page=".$pn);
			exit();
		}
		elseif ($_GET['type'] == "users")
		{
			header("Location: user_reports.php?msg=deleted&page=".$pn);
			exit();
		}
	}

?>