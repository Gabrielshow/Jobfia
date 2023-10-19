<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/


	if (isset($_GET['url']) && $_GET['url'] != "")
	{
		$url = $_GET['url'];

		$go_url = "https://hidereferrer.com/?".$url;
		header("Location: ".$go_url);
		//header("Location: ".$url);
		exit();
	}

?>