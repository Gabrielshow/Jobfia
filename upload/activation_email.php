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

	if (ACCOUNT_ACTIVATION != 1)
	{
		header("Location: index.php");
		exit();
	}

	if (isset($_POST['action']) && $_POST['action'] == "send_activation")
	{
		$email = strtolower(mysqli_real_escape_string($conn, getPostParameter('email')));

		if (!($email) || $email == "")
		{
			header("Location: activation_email.php?msg=1");
			exit();
		}
		else
		{
			if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				header("Location: activation_email.php?msg=2");
				exit();
			}
		}
		
		$query = "SELECT * FROM jobfia_users WHERE email='$email' AND status='inactive' AND activation_key!='' LIMIT 1";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			////////////////////////////////  Send Message  //////////////////////////////
			$etemplate = GetEmailTemplate('activate');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$activate_link = SITE_URL."activate.php?key=".$row['activation_key'];

			$emessage = str_replace("{first_name}", $fname, $emessage);
			$emessage = str_replace("{username}", $username, $emessage);
			$emessage = str_replace("{password}", $pwd, $emessage);
			$emessage = str_replace("{activate_link}", $activate_link, $emessage);
			$to_email = $fname.' '.$lname.' <'.$email.'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);

			header("Location: activation_email.php?msg=4");
			exit();
		}
		else
		{
			header("Location: activation_email.php?msg=3");
			exit();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Account Authentication Email";
	
	$single_page = 1;

	require_once "inc/header.inc.php";
	
?>

	<h1 class="text-center">Account Authentication Email</h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == 4) { ?>
		<div class="alert alert-success">Activation email has been sent to your email address.</div>
	<?php }else{ ?>

	<p class="text-center">
		If you haven't received your authentication email after a few minutes of registering an account, enter your email address below and we'll send it to you again.<br/>
		The email from us may arrive in your Bulk or Junk folder.
	</p>

	<div class="row">
	<div class="col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2">

	<?php if (isset($_GET['msg']) && is_numeric($_GET['msg']) && $_GET['msg'] != 4) { ?>
		<div class="alert alert-danger">
			<i class="fa fa-times"></i> 
			<?php if ($_GET['msg'] == 1) { echo "Please enter your email"; } ?>
			<?php if ($_GET['msg'] == 2) { echo "Please enter a valid email address"; } ?>
			<?php if ($_GET['msg'] == 3) { echo "Sorry, we could not find your email address in our system!"; } ?>
		</div>
	<?php } ?>

	<?php if (!(isset($_GET['msg']) && $_GET['msg'] == 4)) { ?>
	<div style="margin: 0 auto; background: #F9F9F9; padding: 10px; border-radius: 8px">
      <form action="" method="post">
        <div class="form-group row">
			<!--<label class="col-sm-3 text-right" style="padding-top: 10px"><b>Email Address:</b></label>-->
            <div class="col-sm-8 col-sm-offset-2">
	            <div class="input-group">
	            	<input type="text" class="form-control" name="email" size="30" value="" placeholder="Your Email Address" />
	            	<span class="input-group-btn">
						<input type="hidden" name="action" value="send_activation" />
						<button type="submit" class="btn btn-success btn-lg" name="send" id="send">Submit</button>	            	
	            	</span>
	            </div>
            </div>
          </div>
      </form>
	</div>
	<?php } ?>
	
	</div>
	</div>
	
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>