<?php
/*******************************************************************\
 * Jobfia v3.0
 * http://www.Jobfia.com
 *
 * Copyright (c) 2012-2015 Jobfia Software. All rights reserved.
  * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	if (!(isset($_SESSION['adm']['id']) && is_numeric($_SESSION['adm']['id'])))
	{
		header("Location: login.php");
		exit();
	}
	else
	{
		$admin_panel = 1;
	}

?>