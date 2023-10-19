<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	require_once("inc/config.inc.php");

	$q = (int)$_GET['q'];
	if (!$q) return;

	$sql = "SELECT min_price FROM jobfia_categories WHERE category_id='$q' LIMIT 1";
	$result = smart_mysql_query($sql);

	if (mysqli_num_rows($result) > 0)
	{
		$row = mysqli_fetch_array($result);

		if ($row['min_price'] != "0.00")
		{
			echo "minimum price is ";
			echo DisplayMoney($row['min_price']);
		}
	}

?>