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


	if (isset($_POST['action']) && $_POST['action'] == "forgot")
	{
		$email = strtolower(mysqli_real_escape_string($conn, getPostParameter('email')));

		if (!($email) || $email == "")
		{
			header("Location: forgot.php?msg=1");
			exit();
		}
		else
		{
			if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				header("Location: forgot.php?msg=2");
				exit();
			}
		}
		
		$query = "SELECT * FROM jobfia_users WHERE email='$email' AND status='active' LIMIT 1";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			$newPassword = generatePassword(10);
			$update_query = "UPDATE jobfia_users SET password='".PasswordEncryption($newPassword)."' WHERE user_id='".(int)$row['user_id']."'";
			
			if (smart_mysql_query($update_query))
			{
				$etemplate = GetEmailTemplate('forgot_password');
				$esubject = $etemplate['email_subject'];
				$emessage = $etemplate['email_message'];

				$emessage = str_replace("{first_name}", $row['fname'], $emessage);
				$emessage = str_replace("{username}", $row['username'], $emessage);
				$emessage = str_replace("{password}", $newPassword, $emessage);
				$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
				$to_email = $row['fname'].' '.$row['lname'].' <'.$email.'>';

				SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
				
				header("Location: forgot.php?msg=4");
				exit();
			}
		}
		else
		{
			header("Location: forgot.php?msg=3");
			exit();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Forgot Password";
	
	$single_page = 1;

	require_once "inc/header.inc.php";
	
?>
	<div class="row">
	<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">

	<h1 class="text-center">Forgot Password</h1>

	<?php if (isset($_GET['msg']) && is_numeric($_GET['msg']) && $_GET['msg'] != 4) { ?>
		<div class="alert alert-danger">
			<i class="fa fa-times"></i> 
			<?php if ($_GET['msg'] == 1) { ?>Please enter your email address<?php } ?>
			<?php if ($_GET['msg'] == 2) { ?>Please enter a valid email address<?php } ?>
			<?php if ($_GET['msg'] == 3) { ?>Sorry, we could not find your email address in our system!<?php } ?>
		</div>
	<?php }elseif($_GET['msg'] == 4) { ?>
		<div class="alert alert-success">Your new password has been sent to your email address</div>
	<?php }else{ ?>
		<p align="center">Enter your email below and we will send you an email with new password.</p>
	<?php } ?>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == 4) { ?>
		<p align="center"><a class="goback" href="<?php echo SITE_URL; ?>login.php">Back to Login Page</a></p>
	<?php }else{ ?>
		<div align="center" style="margin: 0 auto; background: #F9F9F9; padding: 15px; border-radius: 8px">
		<form action="" method="post">
			<div class="input-group">
	            <input type="text" class="form-control" name="email" size="25" value="" placeholder="Your Email Address" />
	            <span class="input-group-btn">
					<input type="hidden" name="action" value="forgot" />
					<input type="submit" class="btn btn-success btn-lg btn-block" name="send" id="send" value="Send Password" />
	            </span>
			</div>
		</form>
		</div>
	<?php } ?>
	
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>