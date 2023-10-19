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


	if (!(FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != ""))
	{
		header ("Location: index.php");
		exit();
	}

	require_once("inc/facebook/facebook.php");

	$facebook = new Facebook(array(
		'appId'  => FACEBOOK_APPID,
		'secret' => FACEBOOK_SECRET,
		'cookie' => true
	));


	$user = $facebook->getUser();

	if ($user != "")
	{
	  try {

		$user_profile	= $facebook->api('/me');
		$logoutUrl		= $facebook->getLogoutUrl(); //
		$fuserid		= mysqli_real_escape_string($conn, $user_profile["id"]);
		$fusername		= mysqli_real_escape_string($conn, $user_profile["username"]);
		$newtoken		= base64_encode($fuserid."::".$fusername);
		$ip				= getenv("REMOTE_ADDR");

		$check_query = smart_mysql_query("SELECT * FROM jonfia_users WHERE auth_provider='facebook' AND auth_uid='".$fuserid."' LIMIT 1");

		if (mysqli_num_rows($check_query) > 0)
		{
			$row = mysql_fetch_object($check_query);

			if ($row['status'] == 'inactive')
			{
				header("Location: login.php?msg=2");
				exit();
			}

			smart_mysql_query("UPDATE jobfia_users SET last_ip='$ip', login_count=login_count+1, last_login=NOW(), last_activity=NOW() WHERE user_id='".(int)$row['user_id']."' LIMIT 1");

			if (!session_id()) session_start();
			$_SESSION['userid'] = $row['user_id'];
			$_SESSION['FirstName'] = $row['fname'];

			if ($_SESSION['goRetailerID'])
			{
				$goRetailerID = (int)$_SESSION['goRetailerID'];
				$redirect_url = GetRetailerLink($goRetailerID, GetStoreName($goRetailerID));
				unset($_SESSION['goRetailerID']);
			}
			else
			{
				$redirect_url = "myaccount.php";
			}

			header("Location: ".$redirect_url);
			exit();
		}
		else
		{
			$tokenarr			= explode("::",base64_decode($_GET['token']));
			$passcode			= mysqli_real_escape_string($conn, $tokenarr[0]);
			//$username			= mysqli_real_escape_string($conn, $tokenarr[1]);
			$name				= explode('.',$username);
			$fname				= mysqli_real_escape_string($conn, $name[0]);
			$lname				= mysqli_real_escape_string($conn, $name[1]);
			//$email			= mysqli_real_escape_string($conn, $name[2]);
			$password			= PasswordEncryption(generatePassword(10));
			$country			= "";
			$unsubscribe_key	= GenerateKey($username);
			if (PIN_VERIFICATION == 1) $pin_code = GeneratePIN();
			$ip					= getenv("REMOTE_ADDR");
			
			if (isset($_COOKIE['referer_id']) && is_numeric($_COOKIE['referer_id'])) $ref_id = (int)$_COOKIE['referer_id']; else $ref_id = 0;

			### Get access token
			$access_token = $facebook->getAccessToken();

			// SIGN UP bonus //
			if (is_numeric(SIGNUP_BONUS) && SIGNUP_BONUS > 0) $signup_bonus = SIGNUP_BONUS; else $signup_bonus = 0;

			$insert_query = "INSERT INTO jobfia_users SET username='$email', password='$password', email='$email', fname='$fname', lname='$lname', country_id='$country', balance='$signup_bonus', phone='', pin_code='$pin_code', ref_id='$ref_id', newsletter='1', ip='$ip', status='active', auth_provider='facebook', auth_uid='$passcode', activation_key='', unsubscribe_key='$unsubscribe_key', last_activity=NOW(), last_login=NOW(), login_count='1', last_ip='$ip', created=NOW()";
			smart_mysql_query($insert_query);
			$new_user_id = mysqli_insert_id($conn);

			// add refer a friend bonus
			if (REFER_FRIEND_BONUS > 0 && isset($ref_id) && $ref_id > 0 && isUserExist($ref_id))
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
				$ref_res = smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='ref_id', recipient_id='$new_user_id', payment_type='friend_bonus', amount='".REFER_FRIEND_BONUS."', status='$ref_bonus_status', created=NOW()");
			}

			////////////////////////////////  Send welcome message  //////////////////
			$etemplate = GetEmailTemplate('signup');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$emessage = str_replace("{first_name}", $fname, $emessage);
			$emessage = str_replace("{username}", $email, $emessage);
			$emessage = str_replace("{password}", $pwd, $emessage);
			$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
			$to_email = $fname.' '.$lname.' <'.$email.'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
			//////////////////////////////////////////////////////////////////////////

			if (!session_id()) session_start();
			$_SESSION['userid']		= $new_user_id;
			$_SESSION['FirstName']	= $fname;
				
			header("Location: myaccount.php?msg=welcome"); // forward new user to member dashboard
			exit();
		}

	  } catch (FacebookApiException $e) {
		//echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
		$user = null;
	  }
	}
	else
	{
		$params = array(
			'canvas' => 1,
			'scope'  => 'email,offline_access,publish_stream,user_birthday,user_location',
			'fbconnect' => 1,
			'redirect_uri' => 'https://apps.facebook.com/'.FACEBOOK_APPID,
		);

		$fb_login_url = $facebook->getLoginUrl($params);
		header("Location: ".$fb_login_url);
		exit();
	}

?>