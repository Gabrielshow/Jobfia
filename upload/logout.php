<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();

	unset($_SESSION['userid'], $_SESSION['usertype'], $_SESSION['FirstName'], $_SESSION['country']);
	
	session_destroy();

	header("Location: login.php");
	exit();
	
?>