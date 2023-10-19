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

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_task_id = (int)$_GET['id'];
	}

	$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' AND job_id IN (SELECT job_id from jobfia_job_tasks WHERE worker_id='$userid') LIMIT 1");
	$total = mysqli_num_rows($result);
	
	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);

		$proof_file			= substr(SITE_URL,0,-1).JOBFIA_PROOFS_URL.$row['proof_file'];
		$proof_file_path	= PUBLIC_HTML_PATH.JOBFIA_PROOFS_URL.$row['proof_file'];

		if (file_exists($proof_file_path))
		{
			smart_mysql_query("UPDATE jobfia_job_tasks SET proof_downloads=proof_downloads+1 WHERE job_id='".(int)$row['job_id']."' AND job_task_id='$job_task_id' LIMIT 1");

			header("Location: ".$proof_file);
			exit();
		}
	}

?>