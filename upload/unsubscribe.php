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


	if (isset($_GET['key']) && is_string($_GET['key']) && preg_match('/^[a-z\d]{32}$/i', $_GET['key']))
	{
		$verification_code	= strtolower(mysqli_real_escape_string($conn, getGetParameter('key')));
		$verification_code	= preg_replace("/[^0-9a-zA-Z]/", " ", $verification_code);
		$verification_code	= substr(trim($verification_code), 0, 32);

		$check_result = smart_mysql_query("SELECT newsletter FROM jobfia_users WHERE activation_key='$verification_code' LIMIT 1");
        if (mysqli_num_rows($check_result) > 0)
		{
			$check_row = mysqli_fetch_array($check_result);

			if ($check_row['newsletter'] == "0")
			{
				header ("Location: unsubscribe.php?msg=1");
				exit();
			}
			elseif ($check_row['newsletter'] == "1")
			{
				smart_mysql_query("UPDATE jobfia_users SET newsletter='0' WHERE activation_key='$verification_code' LIMIT 1");
				header ("Location: unsubscribe.php?msg=1");
				exit();
			}
		}
		else
		{
			header ("Location: index.php");
			exit();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Unsubscribe";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<?php if (isset($_GET['msg']) && is_numeric($_GET['msg'])) { ?>
		<p>
			<?php if ($_GET['msg'] == 1) { echo "<h1><i class='fa fa-check-circle-o'></i> You have been successfully unsubscribed!</h1>"; } ?>
		</p>
	<?php } ?>


<?php require_once("inc/footer.inc.php"); ?>