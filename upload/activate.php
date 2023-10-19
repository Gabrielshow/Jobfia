<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/iflogged.inc.php");
	require_once("inc/config.inc.php");


	if (isset($_GET['key']) && is_string($_GET['key']) && preg_match('/^[a-z\d]{32}$/i', $_GET['key']))
	{
		$activation_key = strtolower(mysqli_real_escape_string($conn, getGetParameter('key')));
		$activation_key = preg_replace("/[^0-9a-zA-Z]/", " ", $activation_key);
		$activation_key = substr(trim($activation_key), 0, 32);

		$check_result = smart_mysql_query("SELECT * FROM jobfia_users WHERE activation_key='$activation_key' LIMIT 1");
        if (mysqli_num_rows($check_result) > 0)
		{
			$check_row = mysqli_fetch_array($check_result);

			if ($check_row['status'] == "active")
			{
				header ("Location: activate.php?msg=3");
				exit();
			}
			elseif ($check_row['status'] == "inactive" && $check_row['activation_key'] != "")
			{
				// add refer a friend bonus
				if (REFER_FRIEND_BONUS > 0 && $check_row['ref_id'] > 0 && isUserExist($check_row['ref_id']))
				{
					if (REFER_FRIEND_BONUS_AFTER == 0)
					{
						$ref_bonus_status = "confirmed";
						smart_mysql_query("UPDATE jobfia_users SET balance=balance+".REFER_FRIEND_BONUS." WHERE user_id='".(int)$check_row['ref_id']."' LIMIT 1");
					}
					else
					{
						$ref_bonus_status = "pending";
					}

					$reference_id = GenerateReferenceID();
					$ref_res = smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='".(int)$check_row['ref_id']."', recipient_id='".(int)$check_row['user_id']."', payment_type='friend_bonus', amount='".REFER_FRIEND_BONUS."', status='$ref_bonus_status', created=NOW()");
				}

				smart_mysql_query("UPDATE jobfia_users SET status='active', activation_key='' WHERE activation_key='$activation_key' AND login_count='0' LIMIT 1");
				header ("Location: activate.php?msg=2");
				exit();
			}
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Account Activation";
	
	$single_page  = 1;

	require_once ("inc/header.inc.php");

?>

	<?php if (isset($_GET['msg']) && is_numeric($_GET['msg'])) { ?>
		<p>
			<?php if ($_GET['msg'] == 1) { ?>
				<h1>Thank you for registration!</h1>
				<p>An activation email has been sent to your email address (don't forget to check your SPAM folder).</p>
				<p>Please check your email and click on the activation link.</p>
			<?php } ?>
			<?php if ($_GET['msg'] == 2) { ?>
				<h1>You have successfully activated your account</h1>
				<p>Welcome to the <?php echo SITE_TITLE; ?>! Please <a href="<?php echo SITE_URL; ?>login.php">click here</a> to log in.</p>
			<?php } ?>
			<?php if ($_GET['msg'] == 3) { ?>
				<h1>You have already activated your account</h1>
				<p>Please <a href="<?php echo SITE_URL; ?>login.php">click here</a> to log in.</p>
			<?php } ?>
		</p>
	<?php } ?>


<?php require_once("inc/footer.inc.php"); ?>