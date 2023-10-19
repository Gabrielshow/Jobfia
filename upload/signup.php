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


	if (isset($_POST['action']) && $_POST['action'] == "register")
	{
		unset($errs);
		$errs = array();

		$usertype			= mysqli_real_escape_string($conn, getPostParameter('usertype'));
		$fname				= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('fname'))));
		$lname				= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('lname'))));
		$email				= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$username			= mysqli_real_escape_string($conn, strtolower(getPostParameter('username')));
		$pwd				= mysqli_real_escape_string($conn, getPostParameter('password'));
		$pwd2				= mysqli_real_escape_string($conn, getPostParameter('password2'));
		$country			= (int)getPostParameter('country');
		$captcha			= mysqli_real_escape_string($conn, getPostParameter('captcha'));
		$tos				= (int)getPostParameter('tos');
		$newsletter			= (int)getPostParameter('newsletter');
		$ref_id				= (int)getPostParameter('referer_id');
		$ip					= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
		$unsubscribe_key	= GenerateKey($username);

		if (!($usertype && $fname && $lname && $email && $username && $pwd && $pwd2 && $country))
		{
			$errs[] = "Please fill in all fields";
		}

		if (!$usertype)
		{
			$errs[] = "Please select user type";
		}
		else
		{
			$usertypes = array("worker", "employer", "both");
			if (!in_array($usertype, $usertypes))
			{
				$errs[] = "Please select user type";
			}

			if ($usertype == "worker" || $usertype == "both")
				$worker = 1;
			else
				$worker = 0;
		}

		if (SIGNUP_CAPTCHA == 1)
		{
			if (!$captcha)
			{
				$errs[] = "Please enter security code";
			}
			else
			{
				if (empty($_SESSION['captcha']) || strcasecmp($_SESSION['captcha'], $captcha) != 0)
				{
					$errs[] = "Wrong security code";
				}
			}
		}

		if (isset($username) && $username != "" && !preg_match('/^[a-z\d]{3,20}$/i', $username))
		{
			$errs[] = "Username must be between 3-20 characters (letters and numbers)";
			if (stristr($username, ' ')) $errs[] = "Username must not contain spaces";
		}


		if (isset($email) && $email != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = "Please enter a valid email address";
		}
		else
		{
			if (CHECK_SPAM_EMAIL == 1)
			{
				if (disposable_check($email)) $errs[] = "Please use other email address";
			}
		}

		if (isset($pwd) && $pwd != "" && isset($pwd2) && $pwd2 != "")
		{
			if ($pwd !== $pwd2)
			{
				$errs[] = "Password confirmation is wrong";
			}
			elseif ((strlen($pwd)) < 6 || (strlen($pwd2) < 6) || (strlen($pwd)) > 20 || (strlen($pwd2) > 20))
			{
				$errs[] = "Password must be between 6-20 characters";
			}
			elseif (stristr($pwd, ' '))
			{
				$errs[] = "Password must not contain spaces";
			}
		}

		if (!(isset($tos) && $tos == 1))
		{
			$errs[] = "You must agree to the Terms &amp; Conditions";
		}

		if (count($errs) == 0)
		{
					$check_query = "SELECT username FROM jobfia_users WHERE username='$username' OR email='$email' LIMIT 1";
					$check_result = smart_mysql_query($check_query);

					if (mysqli_num_rows($check_result) != 0)
					{
						header ("Location: signup.php?msg=exists");
						exit();
					}

					if (BLOCK_SAME_IP == 1)
					{
						$ip_check_query = "SELECT username FROM jobfia_users WHERE ip='$ip' OR last_ip='$ip' LIMIT 1";
						$ip_check_result = smart_mysql_query($ip_check_query);

						if (mysqli_num_rows($ip_check_result) != 0)
						{
							header ("Location: signup.php?msg=exists2");
							exit();
						}
					}

					// Sign Up bonus
					if (is_numeric(SIGNUP_BONUS) && SIGNUP_BONUS > 0) $signup_bonus = SIGNUP_BONUS; else $signup_bonus = 0;
					
					// Check referrer 
					if (isset($ref_id) && $ref_id > 0 && isUserExist($ref_id)) $ref_id = $ref_id; else $ref_id = 0;

					if (ACCOUNT_ACTIVATION == 1)
					{
						$activation_key = GenerateKey($username);

						if (PIN_VERIFICATION == 1) $pin_code = GeneratePIN();

						$insert_query = "INSERT INTO jobfia_users SET worker='$worker', usertype='$usertype', username='$username', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', avatar='no_avatar.png', country_id='$country', pin_code='$pin_code', balance='$signup_bonus', ref_id='$ref_id', newsletter='$newsletter', ip='$ip', status='inactive', activation_key='$activation_key', unsubscribe_key='$unsubscribe_key', created=NOW()";
						smart_mysql_query($insert_query);
						$new_user_id = mysqli_insert_id($conn);
					
						///////////////////////  Send Message  //////////////////////////////
						$etemplate = GetEmailTemplate('activate');
						$esubject = $etemplate['email_subject'];
						$emessage = $etemplate['email_message'];

						$activate_link = SITE_URL."activate.php?key=".$activation_key;

						$emessage = str_replace("{first_name}", $fname, $emessage);
						$emessage = str_replace("{username}", $username, $emessage);
						$emessage = str_replace("{password}", $pwd, $emessage);
						$emessage = str_replace("{activate_link}", $activate_link, $emessage);
						$to_email = $fname.' '.$lname.' <'.$email.'>';
		
						SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
						//////////////////////////////////////////////////////////////////////

						header("Location: activate.php?msg=1");
						exit();
					}
					else
					{
						$insert_query = "INSERT INTO jobfia_users SET worker='$worker', usertype='$usertype', username='$username', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', avatar='no_avatar.png', country_id='$country', pin_code='$pin_code', balance='$signup_bonus', ref_id='$ref_id', newsletter='$newsletter', ip='$ip', status='active', activation_key='',  unsubscribe_key='$unsubscribe_key', last_activity=NOW(), last_login=NOW(), login_count='1', last_ip='$ip', created=NOW()";
						smart_mysql_query($insert_query);
						$new_user_id = mysqli_insert_id($conn);

						// add refer a friend bonus
						if (REFER_FRIEND_BONUS > 0 && isset($ref_id) && $ref_id > 0)
						{
							if (REFER_FRIEND_BONUS_AFTER == 0)
							{
								$ref_bonus_status = "confirmed";
								smart_mysql_query("UPDATE jobfia_users SET balance=balance+".REFER_FRIEND_BONUS." WHERE user_id='$ref_id' LIMIT 1");
							}
							else
							{
								$ref_bonus_status = "pending";
							}

							$reference_id = GenerateReferenceID();
							$ref_res = smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$ref_id', recipient_id='$new_user_id', payment_type='friend_bonus', amount='".REFER_FRIEND_BONUS."', status='$ref_bonus_status', created=NOW()");
						}

						if (!session_id()) session_start();
						$_SESSION['userid']		= $new_user_id;
						$_SESSION['usertype']	= $usertype;
						$_SESSION['country']	= $country;
						$_SESSION['FirstName']	= $fname;

						////////////////////////////////  Send Message  //////////////////////////////
						$etemplate = GetEmailTemplate('signup');
						$esubject = $etemplate['email_subject'];
						$emessage = $etemplate['email_message'];

						$emessage = str_replace("{first_name}", $fname, $emessage);
						$emessage = str_replace("{username}", $username, $emessage);
						$emessage = str_replace("{password}", $pwd, $emessage);
						$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
						$to_email = $fname.' '.$lname.' <'.$email.'>';

						SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
						////////////////////////////////////////////////////////////////////////////////

						header("Location: myaccount.php?msg=welcome");
						exit();
					}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Sign Up";
	
	require_once ("inc/header.inc.php");
	
?>

		<?php if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
			<p align="center"><a href="javascript: void(0);" onclick="facebook_login();" class="connect-f"><img src="<?php echo SITE_URL; ?>images/facebook_connect.png" /></a></p>
			<div style="border-bottom: 1px dotted #ddd; width: 400px; margin: 0 auto;">
				<div style="font-size: 12px; font-weight: bold; background-color: #FFF; color: #DDD; margin: 0 auto; top: 8px; text-align: center; width: 100px; position: relative;">or</div>
			</div><br/>
		<?php } ?>


		<section class="job-bg user-page">
		<div class="container">
			<div class="row text-center">
				<!-- user-login -->			
				<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
					<div class="user-account job-user-account">						

					<?php if (BLOCK_SAME_IP == 1 && !$_POST['action'] && !$_GET['msg']) { ?>
						<div class="alert alert-info text-left"><b>NOTE</b>: One account per computer, our system detects the IP of users and if more than one account is created you will be banned and unable to join this site again.</div>
					<?php } ?>						
						
						<h2 style="margin: 5px 0;">Create an Account</h2>
						<p>Already have an account? <a href="<?php echo SITE_URL; ?>login.php">Log In</a></p><br>
						
						<?php if (isset($allerrors) || isset($_GET['msg'])) { ?>
							<div class="alert alert-danger text-left">
								<?php if (isset($_GET['msg']) && $_GET['msg'] == "exists") { ?>
									Username or email address you have entered is already in use.<br/> <a href="<?php echo SITE_URL; ?>forgot.php">Forgot your password?</a></font><br/>
								<?php } ?>
								<?php if (isset($_GET['msg']) && $_GET['msg'] == "exists2") { ?>
									&#155; Sorry, we have registered member from your computer.<br/> <a href="<?php echo SITE_URL; ?>forgot.php">Forgot your password?</a></font><br/>
								<?php }elseif (isset($allerrors)) { ?>
									<?php echo $allerrors; ?>
								<?php } ?>
							</div>
						<?php } ?>

									<form action="" method="post">			
										<div class="row form-group">
											<div class="col-sm-12 user-type">										
												<div style="text-align: left; padding-left: 30%; padding-top: 5px; padding-bottom: 5px; background: #fcfcfc; border-radius: 7px; margin: 5px 0;">
													<div class="radio"><label><input type="radio" name="usertype" value="worker" id="worker" <?php if (!$usertype || @$usertype == "worker") echo "checked='checked'"; ?> style="margin-top: -12px" /> I am a worker</label></div>
													<div class="radio"><label><input type="radio" name="usertype" value="employer" id="employer" <?php if (@$usertype == "employer") echo "checked='checked'"; ?> style="margin-top: -12px" /> I am an employer</label></div>
													<div class="radio"><label><input type="radio" name="usertype" value="both" id="both" <?php if (@$usertype == "both") echo "checked='checked'"; ?> style="margin-top: -12px" /> Both</label></div>
												</div>
											</div>
										</div>						
										<div class="form-group">
											<input type="text" id="fname" name="fname" class="form-control" placeholder="First Name" value="<?php echo getPostParameter('fname'); ?>" required>
										</div>
										<div class="form-group">
											<input type="text" id="lname" name="lname" class="form-control" placeholder="Last Name" value="<?php echo getPostParameter('lname'); ?>" required>
										</div>										
										<div class="form-group">
											<input type="email" id="email" class="form-control" name="email" placeholder="Email" value="<?php echo getPostParameter('email'); ?>" required>
										</div>
										<div class="form-group">
											<input type="text" id="username" class="form-control" name="username" placeholder="Username" value="<?php echo getPostParameter('username'); ?>" required>
										</div>										
										<div class="form-group">
											<input type="password" id="password" class="form-control" name="password" placeholder="Password" value="" required>
										</div>
										<div class="form-group">
											<input type="password" id="password2" class="form-control" name="password2" placeholder="Confirm Password" value="" required>
										</div>
										<!--
										<div class="form-group">
											<input type="text" id="phone" class="form-control" name="phone" placeholder="Phone Number">
										</div>
										-->
										<select name="country" class="form-control" id="country" required>
										<option value="">Select Your Country</option>
										<?php
											$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
											$rs_country = smart_mysql_query($sql_country);
											$total_country = mysqli_num_rows($rs_country);
						
											if ($total_country > 0)
											{
												while ($row_country = mysqli_fetch_array($rs_country))
												{
													if ($country == $row_country['country_id'])
														echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
													else
														echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
												}
											}
										?>
										</select>
										<?php if (SIGNUP_CAPTCHA == 1) { ?>
										<div class="form-group">
											<div class="row">
											<div class="col-sm-6">
												<input type="text" id="captcha" class="form-control" name="captcha" value="" placeholder="Security Code" required>
											</div>
											<div class="col-sm-6">
												<img src="<?php echo SITE_URL; ?>captcha.php?rand=<?php echo rand(); ?>" id="captchaimg" align="absmiddle" /> &nbsp; <a href="javascript: refreshCaptcha();"><i class="fa fa-refresh fa-lg jobfia_tooltip" title="Refresh image"></i></a>
											</div>
											</div>			
											<script language="javascript" type="text/javascript">
												function refreshCaptcha()
												{
													var img = document.images['captchaimg'];
													img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
												}
											</script>
										</div>
										<?php } ?>
										<div class="checkbox">
											<label class="pull-left checked" for="tos"><input type="checkbox" name="tos" id="tos" value="1" <?php echo (@$tos == 1 || !@$tos) ? "checked" : "" ?>> By signing up for an account you agree to our <a href="<?php echo SITE_URL; ?>terms.php" target="_blank">Terms and Conditions</a></label>
										</div>
										<div class="checkbox">
											<label class="pull-left checked" for="newsletter"><input type="checkbox" name="newsletter" id="newsletter" value="1" <?php echo (@$newsletter == 1 || !$newsletter) ? "checked" : "" ?>> I wish to receive newsletter</label>
										</div>
										<?php if (isset($_COOKIE['referer_id']) && is_numeric($_COOKIE['referer_id'])) { ?>
											<input type="hidden" name="referer_id" id="referer_id" value="<?php echo (int)$_COOKIE['referer_id']; ?>" />
										<?php } ?>									
										<input type="hidden" name="action" id="action" value="register" />
										<button type="submit" class="btn btn-lg btn-block">Sign Up</button>
									</form>
								
					</div>
				</div><!-- user-login -->			
			</div><!-- row -->	
		</div><!-- container -->
	</section><!-- signup-page -->


<?php require_once ("inc/footer.inc.php"); ?>