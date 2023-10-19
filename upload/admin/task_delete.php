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


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$job_task_id	= (int)$_REQUEST['id'];
		$job_id			= (int)$_REQUEST['job_id'];
		$pn				= (int)$_REQUEST['pn'];

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		$row = mysqli_fetch_array($result);
		if ($row['status'] == "confirmed")
		{
			// deduct money from user account
			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".$row['job_price']." WHERE user_id='".(int)$row['worker_id']."' LIMIT 1");
		}

		if ($row['proof_file'] != "")
		{
			if (file_exists(PUBLIC_HTML_PATH.JOBFIA_PROOFS_URL.$row['proof_file'])) @unlink(PUBLIC_HTML_PATH.JOBFIA_PROOFS_URL.$row['proof_file']);
		}

		smart_mysql_query("DELETE FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' LIMIT 1");

		header("Location: job_tasks.php?id=$job_id&msg=deleted&page=".$pn."&column=".$_GET['column']."&order=".$_GET['order']);
		exit();
	}

?>