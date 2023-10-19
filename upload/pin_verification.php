<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");
	require_once("inc/auth.inc.php");


	if (PIN_VERIFICATION != 1)
	{
		header ("Location: index.php");
		exit();
	}
	
	if (!$_GET['msg'])
	{
		$query	= "SELECT * FROM jobfia_users WHERE user_id='$userid' AND pin_verified='0' AND status='active' LIMIT 1";
		$result = smart_mysql_query($query);
		if (mysqli_num_rows($result) > 0)
		{
			header("Location: pin_verification.php?msg=5");
			exit();
		}
	}

	if ($_POST['action'] != "" && $_POST['action'] == "verify")
	{
		$pin_code = mysqli_real_escape_string($conn, getPostParameter('pin_code'));

		if (!($pin_code) || $pin_code == "")
		{
			header("Location: pin_verification.php?msg=1");
			exit();
		}

		$check_query = "SELECT * FROM jobfia_users WHERE user_id='$user_id' AND pin_code='$pin_code' AND pin_verified='0' AND status='active' LIMIT 1";
		$check_result = smart_mysql_query($check_query);
		if (mysqli_num_rows($check_result) > 0)
		{
			smart_mysql_query("UPDARE jobfia_users SET pin_verified='1' WHERE user_id='$userid' LIMIT 1");
			header("Location: pin_verification.php?msg=4");
			exit();
		}
		else
		{
			header("Location: pin_verification.php?msg=3");
			exit();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Pin Verification";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	//require_once ("inc/usermenu.inc.php");

?>

	<h1><i class="fa fa-map-marker"></i> Pin Verification</h1>

	<?php if (!$_GET['msg']) { ?>
		<p>Please verify your address by entering 12-characters pin code, which we sent to your postal address.</p>
	<?php } ?>

	<?php if($_GET['msg'] == "4") { ?>
		<div class="alert alert-success text-center"><h4><i class="fa fa-check-circle-o fa-3x"></i></h4> <h4>Congratulations! Your address was verified!</h4></div>
	<?php }elseif($_GET['msg'] == "5") { ?>
		<div class="alert alert-success text-center"><h4><i class="fa fa-check-circle-o fa-3x"></i></h4> <h4>Your address was already verified!</h4></div>
	<?php } ?>

	<?php if (isset($_GET['msg']) && ($_GET['msg'] == 4 || $_GET['msg'] == 5)) { ?>
		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-chevron-left"></i> Go Back</a></p>
	<?php }else{ ?>

		<?php if (isset($_GET['msg']) && is_numeric($_GET['msg']) && $_GET['msg'] != 4) { ?>
		<div class="alert alert-danger">
			<i class="fa fa-times"></i> 
			<?php if ($_GET['msg'] == 1) { ?>Please enter your pin code<?php } ?>
			<?php if ($_GET['msg'] == 2) { ?>Please enter a valid pin code<?php } ?>
			<?php if ($_GET['msg'] == 3) { ?>Sorry, we could not find this pin code<?php } ?>
		</div>
		<?php } ?>

		<div align="center" style="margin: 0 auto; background: #F9F9F9; padding: 10px; ">
		<form action="" method="post">
			<b>Your pin code:</b></td>
			<input type="text" class="form-control" name="pin_code" size="30" maxlength="12" value="<?php echo getPostParameter('pin_code'); ?>" />
			<input type="hidden" name="action" value="verify" />
			<input type="submit" class="btn btn-success" name="send" id="send" value="Verify" />
		</form>
		</div>
	<?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>