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


	if (isset($_POST['action']) && $_POST['action'] == "login")
	{
		$username	= mysqli_real_escape_string($conn, getPostParameter('username'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$remember	= (int)getPostParameter('remember');
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($username && $pwd))
		{
			$errormsg = "Please enter username and password";
		}
		else
		{
			$sql = "SELECT * FROM jobfia_users WHERE username='$username' AND password='".PasswordEncryption($pwd)."' LIMIT 1";
			$result = smart_mysql_query($sql);

			if (mysqli_num_rows($result) != 0)
			{
					$row = mysqli_fetch_array($result);

					if ($row['status'] == 'inactive')
					{
						// if not new user
						if ($row['login_count'] > 0)
						{
							header("Location: login.php?msg=4");
							exit();
						}
						else
						{
							header("Location: login.php?msg=2");
							exit();
						}
					}

					if ($remember == 1)
					{
						$cookie_hash = md5(sha1($username.$ip));
						setcookie("uname", $cookie_hash, time()+3600*24*365, '/');
						$login_sql = "login_session = '$cookie_hash', ";
					}

					smart_mysql_query("UPDATE jobfia_users SET ".$login_sql." last_ip='$ip', login_count=login_count+1, last_login=NOW(), last_activity=NOW() WHERE user_id='".(int)$row['user_id']."' LIMIT 1"); 

					if (!session_id()) session_start();
					$_SESSION['userid']		= $row['user_id'];
					$_SESSION['usertype']	= $row['usertype'];
					$_SESSION['country']	= $row['country_id'];
					$_SESSION['FirstName']	= $row['fname'];
					
					if (isset($_SESSION["login_redirect"]))
					{
        				header("Location: " . $_SESSION["login_redirect"]);
        				unset($_SESSION["login_redirect"]);
        				exit();
        			}
        			else
        			{
						header("Location: myaccount.php");
						exit();
					}
			}
			else
			{
					header("Location: login.php?msg=1");
					exit();
			}
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Log in";

	require_once ("inc/header.inc.php");

?>

	<!-- signin-page -->
	<section class="clearfix job-bg user-page">
		<div class="container">
			<div class="row text-center">
				<!-- user-login -->			
				<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
					<div class="user-account"><br><br><br>
						<h2 style="margin: 5px 0;">Log In</h2>
						<p>Don't have an account? <a href="<?php echo SITE_URL; ?>signup.php">Sign Up</a></p>					
		
						<?php if (isset($errormsg) || isset($_GET['msg'])) { ?>
							<div class="alert alert-danger">
								<?php if (isset($errormsg) && $errormsg != "") { ?>
									<i class="fa fa-times"></i> <?php echo $errormsg; ?>
								<?php }else{ ?>
									<?php if ($_GET['msg'] == 1) { ?><i class="fa fa-times"></i> Invalid username or password<?php } ?>
									<?php if ($_GET['msg'] == 2) { ?>Sorry, your account is inactive.<br/>Please check your email for activation link<?php } ?>
									<?php if ($_GET['msg'] == 3) { ?><i class="fa fa-info-circle fa-lg"></i> You must login first<?php } ?>
									<?php if ($_GET['msg'] == 4) { ?>Sorry, your account is inactive.<br/>For more information please <a href="<?php echo SITE_URL; ?>contact.php">contact us</a><?php } ?>
								<?php } ?>
							</div>
						<?php } ?>						
						
						<!-- form -->
						<form action="" method="post">
							<div class="form-group">
								<input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo getPostParameter('username'); ?>">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" placeholder="Password">
							</div>
							<input type="hidden" name="action" value="login" />
							<button type="submit" class="btn btn-lg btn-block" name="login">Login</button>					
							<!-- forgot-password -->
							<div class="user-option">
								<div class="checkbox pull-left">
									<label for="remember"><input type="checkbox" name="remember" id="remember" value="1" <?php echo (@$remember == 1) ? "checked" : "" ?>/> Remember me </label>
								</div>
								<div class="pull-right forgot-password">
									<a href="<?php echo SITE_URL; ?>forgot.php">Forgot password?</a>
									<?php if (ACCOUNT_ACTIVATION == 1) { ?>
										<br/><a href="<?php echo SITE_URL; ?>activation_email.php">Resend activation email</a>
									<?php } ?>								
								</div>
							</div><!-- forgot-password -->
						</form><!-- form -->
					</div>
				</div><!-- user-login -->			
			</div><!-- row -->	
		</div><!-- container -->
	</section><!-- signin-page -->


	<?php if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
		<div style="border-bottom: 1px dotted #ddd; margin-bottom: 20px;">
			<div style="font-size: 12px; font-weight: bold; background-color: #fff; color: #DDD; margin: 0 auto; top: 8px; text-align: center; width: 100px; position: relative;">or</div>
		</div>
		<p align="center"><a href="javascript: void(0);" onclick="facebook_login();" class="connect-f"><img src="<?php echo SITE_URL; ?>images/facebook_connect.png" /></a></p>
	<?php } ?>



<?php require_once ("inc/footer.inc.php"); ?>