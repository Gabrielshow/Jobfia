<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	if ($_SESSION['usertype'] != "worker")
	{
		header("Location: myaccount.php?allow=worker");
		exit();
	}

?>