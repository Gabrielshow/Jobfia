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
		$id = (int)$_GET['id'];

		smart_mysql_query("DELETE FROM jobfia_categories WHERE category_id='$id'");
		smart_mysql_query("DELETE FROM jobfia_job_to_category WHERE category_id='$id'");

		$res = smart_mysql_query("SELECT category_id FROM jobfia_categories WHERE parent_id='$id'");
		if (mysqli_num_rows($res) > 0)
		{
			while ($row = mysqli_fetch_array($res))
			{
				smart_mysql_query("DELETE FROM jobfia_categories WHERE category_id='".$row['category_id']."'");
			}
		}

		header("Location: categories.php?msg=deleted");
		exit();
	}

?>