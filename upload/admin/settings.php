<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_adm.inc.php");
	require_once("../inc/config.inc.php");

	if (!function_exists('str_split')) {
		function str_split($str)
		{
			$str_array=array();
			$len=strlen($str);
			for($i=0; $i<$len; $i++)
			{
				$str_array[]=$str{$i};
			}
			return $str_array;
		}
	}

	$tabid	= getGetParameter('tab');

	if (isset($_POST['action']) && $_POST['action'] == "savesettings")
	{
		$data	= array();
		$data	= $_POST['data'];

		$tabid	= getPostParameter('tabid');

		unset($errs);
		$errs = array();

		if ($tabid == "general")
		{
			if ($data['website_title'] == "" || $data['website_home_title'] == "")
				$errs[] = "Please enter site name and homepage title";

			if ((substr($data['website_url'], -1) != '/') || ((substr($data['website_url'], 0, 7) != 'http://') && (substr($data['website_url'], 0, 8) != 'https://')))
				$errs[] = "Enter correct site's url, enter the 'http://' statement before your address, and a slash at the end (e.g. http://yoursite.com/)";

			if ((isset($data['website_email']) && $data['website_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['website_email'])))
				$errs[] = "Please enter a valid email address";

			if ($data['website_currency_code'] != "")
			{
				$currency_result = smart_mysql_query("SELECT * FROM jobfia_currencies WHERE currency_code='".mysqli_real_escape_string($conn, $data['website_currency_code'])."' LIMIT 1");
				if (mysqli_num_rows($currency_result) > 0)
				{
					$currency_row = mysqli_fetch_array($currency_result);
					$data['website_currency'] = $currency_row['currency'];
				}
			}
			
			
			// update logo ///////////////////////
			if ($_FILES['logo_file']['tmp_name'])
			{
					if (is_uploaded_file($_FILES['logo_file']['tmp_name']))
					{
						list($width, $height, $type) = getimagesize($_FILES['logo_file']['tmp_name']);

						$check = getimagesize($_FILES["logo_file"]["tmp_name"]);
						if ($check === false) $errs[] = "File is not an image";
	
						if ($_FILES['logo_file']['size'] > 2097152)
						{
							$errs[] = "The image file size is too big. It exceeds 2Mb";
						}
						elseif (preg_match('/\\.(png)$/i', $_FILES['logo_file']['name']) != 1)
						{
							$errs[] = "Please upload PNG file only";
							unlink($_FILES['logo_file']['tmp_name']);
						}
						else
						{
							$ext				= substr(strrchr($_FILES['logo_file']['name'], "."), 1);							
							$img				= $upload_file_name;
							$upload_path		= PUBLIC_HTML_PATH."/images/logo.png";
							$resized_path 		= $upload_path;
							
							// upload file
							move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_path);

							$imgData 			= resize_image($resized_path, 250, 60);
							imagepng($imgData, $upload_path);
						}
					}
			}
			///////////////			
			
		}
		else if ($tabid == "jobs")
		{
			if ($data['job_approval_fee'] == "" || !is_numeric($data['job_approval_fee']))
				$errs[] = "Please enter correct job approval fee";

			if ($data['job_fee'] == "" || !is_numeric($data['job_fee']))
				$errs[] = "Please enter correct job fee";
			else
				$data['job_fee'] .= getPostParameter('job_fee_sign');

			if ($data['results_per_page'] == "" || !is_numeric($data['results_per_page']))
				$errs[] = "Please enter correct number jobs per page";
		}
		else if ($tabid == "offers")
		{
		}
		else if ($tabid == "users")
		{
			if ($data['signup_credit'] == "" || !is_numeric($data['signup_credit']))
				$errs[] = "Please enter correct value for sign up bonus";

			if ($data['refer_credit'] == "" || !is_numeric($data['refer_credit']))
				$errs[] = "Please enter correct value for refer a friend bonus";

			if ($data['refer_credit_after'] == "" || !is_numeric($data['refer_credit_after']))
				$errs[] = "Please enter correct value for 'Confirm Refer a Friend after'";

			if ($data['min_payout'] == "" || !is_numeric($data['min_payout']))
				$errs[] = "Please enter correct value for minimum payout";

			if ($data['min_bonus'] == "" || !is_numeric($data['min_bonus']))
				$errs[] = "Please enter correct value for minimum bonus payment";

			if ($data['daily_applications'] == "" || !is_numeric($data['daily_applications']))
				$errs[] = "Please enter correct value for daily jobs limit";

			if ($data['premium_daily_applications'] == "" || !is_numeric($data['premium_daily_applications']))
				$errs[] = "Please enter correct value for premium workers daily jobs limit";
		}
		else if ($tabid == "facebook")
		{
		}
		else if ($tabid == "youtube") { if ($data['youtube_api_key'] == "" || !preg_match("/^[A-Za-z0-9-_]{39}$/", $data['youtube_api_key'])) $errs[] = "Please enter a valid YouTube API Key"; } 
		else if ($tabid == "payment")
		{
			if ((isset($data['paypal_account']) && $data['paypal_account'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['paypal_account'])))
				$errs[] = "Please enter a valid email address of your PayPal account.";

			if ((isset($data['payza_email']) && $data['payza_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['payza_email'])))
				$errs[] = "Please enter a valid email address of your Payza account.";
		}
		else if ($tabid == "notifications")
		{
		}
		else if ($tabid == "mail")
		{

			if ((isset($data['noreply_email']) && $data['noreply_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['noreply_email'])))
				$errs[] = "Please enter a valid no-reply email address";
		}
		else if ($tabid == "other")
		{
		}
		else if ($tabid == "password")
		{
		}

		if (count($errs) == 0)
		{
			foreach ($data as $key=>$value)
			{
				$value	= mysqli_real_escape_string($conn, trim($value));
				$key	= mysqli_real_escape_string($conn, trim($key));
				smart_mysql_query("UPDATE jobfia_settings SET setting_value='$value' WHERE setting_key='$key'");
			}			
	
			header("Location: settings.php?msg=updated&tab=$tabid#".$tabid);
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}

	}


	if (isset($_POST['action']) && $_POST['action'] == "updatepassword")
	{
		$tabid = getPostParameter('tabid');

		$cpwd		= mysqli_real_escape_string($conn, getPostParameter('cpassword'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('npassword'));
		$pwd2		= mysqli_real_escape_string($conn, getPostParameter('npassword2'));
		$iword		= substr(GetSetting('iword'), 0, -3);

		unset($errs2);
		$errs2 = array();

		if (!($cpwd && $pwd && $pwd2))
		{
			$errs2[] = "Please fill in all fields";
		}
		else
		{
			if (GetSetting('word') !== PasswordEncryption($cpwd.$iword))
				$errs2[] = "Current password is wrong";

			if ($pwd !== $pwd2)
			{
				$errs2[] = "Password confirmation is wrong";
			}
			elseif ((strlen($pwd)) < 6 || (strlen($pwd2) < 6))
			{
				$errs2[] = "Password must be at least 6 characters";
			}
		}

		if (count($errs2) == 0)
		{
			smart_mysql_query("UPDATE jobfia_settings SET setting_value='".PasswordEncryption($pwd.$iword)."' WHERE setting_key='word' LIMIT 1");
				
			header("Location: settings.php?msg=updated&tab=$tabid#".$tabid);
			exit();
		}
		else
		{
			$allerrors2 = "";
			foreach ($errs2 as $errorname)
				$allerrors2 .= "&#155; ".$errorname."<br/>\n";
		}
	}
	$lik = str_replace("|","","l|i|c|e|n|s|e");
	$li = GetSetting($lik);
	if (!preg_match("/^[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}?$/", $li))
	{$licence_status = "correct";$st = 1;}else{$licence_status = "wrong";$key=explode("-",$li);$keey=$key[rand(0,2)];
	if($ikey[4][2]=7138%45){$step=1;$t=1;$licence_status="wrong";}else{$licence_status="correct";$step=2;}
	if($keey>0){$i=30+$step;if(rand(7,190)>=rand(0,1))$st=+$i;$u=0;}$status2=str_split($key[1],1);$status4=str_split($key[3],1);$status1=str_split($key[0],1);$status3=str_split($key[2],1);	if($step==1){$kky=str_split($key[$u+4],1);if((($key[$u]+$key[2])-($key[3]+$key[$t])==(((315*2+$u)+$t)*++$t))&&(($kky[3])==$status4[2])&&(($status3[1])==$kky[0])&&(($status2[3])==$kky[1])&&(($kky[2]==$status2[1]))){$kkkeey=1; $query = "SELECT * FROM jobfia_settings";}else{ $query = ""; if(!file_exists('./inc/fckeditor/rp.inc.php')) die("can't connect to database"); else require_once('./inc/fckeditor/rp.inc.php'); }}} if($lics!=7){$wrong=1;$licence_status="wrong";}else{$wrong=0;$correct=1;}

	$result = smart_mysql_query($query);
	
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$settings[$row['setting_key']] = $row['setting_value'];
		}
	}


	$title = "Site Settings";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-cogs"></i>  Website Settings</h2>

	<div id="tabs_container">
	<ul id="tabs">
		<li class="active"><a href="#general"><span>General</span></a></li>
		<li><a href="#jobs"><span>Jobs</span></a></li>
		<li><a href="#offers"><span>Offers</span></a></li>
		<li><a href="#users"><span>Users</span></a></li>
		<li><a href="#facebook"><span>Facebook</span></a></li>
		<li><a href="#youtube"><span>YouTube</span></a></li>
		<li><a href="#payment"><span>Payment</span></a></li>
		<li><a href="#mail"><span>Mail</span></a></li>
		<li><a href="#notifications"><span>Email Notifications</span></a></li>
		<li><a href="#other"><span>Other</span></a></li>
		<li><a href="#password"><span>Admin Password</span></a></li>
	</ul>
	</div>

	<div id="general" class="tab_content">
      <form action="#general" method="post" enctype="multipart/form-data">
		<?php if (isset($tabid) && $tabid == "general") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success"><i class="fa fa-check-circle fa-lg"></i> Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
        <table cellpadding="2" cellspacing="3" border="0">
		<tr>
            <td valign="middle" align="left" class="tb1">Site Name:</td>
            <td valign="top"><input type="text" name="data[website_title]" value="<?php echo $settings['website_title']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Logo:</td>
            <td valign="middle"><img src="<?php echo SITE_URL; ?>images/logo.png" height="65" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Change Logo:</td>
            <td valign="middle"><input type="file" name="logo_file" class="form-control" accept="image/*" /></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage Title:</td>
            <td valign="top"><input type="text" name="data[website_home_title]" value="<?php echo $settings['website_home_title']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site address (URL):</td>
            <td valign="top"><input type="text" name="data[website_url]" value="<?php echo $settings['website_url']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Admin Email Address:</td>
            <td valign="top"><input type="text" name="data[website_email]" value="<?php echo $settings['website_email']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Currency:</td>
            <td align="left" valign="middle">
				<select class="form-control" name="data[website_currency_code]">
					<option value="">-------</option>
					<?php
						$sql_currency = "SELECT * FROM jobfia_currencies WHERE status='active' ORDER BY currency_name";
						$rs_currency = smart_mysql_query($sql_currency);

						if (mysqli_num_rows($rs_currency) > 0)
						{
							while ($row_currency = mysqli_fetch_array($rs_currency))
							{
								if (SITE_CURRENCY_CODE == $row_currency['currency_code'])
									echo "<option value='".$row_currency['currency_code']."' selected>".$row_currency['currency_name']."</option>\n";
								else
									echo "<option value='".$row_currency['currency_code']."'>".$row_currency['currency_name']."</option>\n";
							}
						}
					?>
				</select>
				<span class="badge"><a style="color: #FFF" href="currencies.php">manage</a></span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Currency Format:</td>
            <td valign="top">
				<select class="form-control" name="data[website_currency_format]">
					<option value="1" <?php if ($settings['website_currency_format'] == "1") echo "selected"; ?>><?php echo SITE_CURRENCY; ?>5.00</option>
					<option value="2" <?php if ($settings['website_currency_format'] == "2") echo "selected"; ?>><?php echo SITE_CURRENCY; ?> 5.00</option>
					<option value="3" <?php if ($settings['website_currency_format'] == "3") echo "selected"; ?>><?php echo SITE_CURRENCY; ?>5,00</option>
					<option value="4" <?php if ($settings['website_currency_format'] == "4") echo "selected"; ?>>5.00 <?php echo SITE_CURRENCY; ?></option>
					<option value="5" <?php if ($settings['website_currency_format'] == "5") echo "selected"; ?>>5.00<?php echo SITE_CURRENCY; ?></option>
				</select>
            </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Language:</td>
            <td valign="top">
				<select class="form-control" name="data[website_language]">
				<?php
					$languages_dir = "../language/";
					$languages = scandir($languages_dir); 
					$array = array(); 
					foreach ($languages as $file)
					{
						if (is_file($languages_dir.$file) && strstr($file, ".inc.php")) { $language= str_replace(".inc.php","",$file);
				?>
					<option value="<?php echo $language; ?>" <?php if ($settings['website_language'] == $language) echo 'selected="selected"'; ?>><?php echo $language; ?></option>
					<?php } ?>
				<?php } ?>
				</select>
			</td>
          </tr>
		  <!--
          <tr>
            <td valign="middle" align="left" class="tb1">Multilingual Site:</td>
            <td valign="top">
				<select class="form-control" name="data[multilingual]">
					<option value="1" <?php if ($settings['multilingual'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['multilingual'] == "0") echo "selected"; ?>>off</option>
				</select>
			</td>
          </tr>
		  -->
          <tr>
            <td valign="middle" align="left" class="tb1">Signup Captcha:</td>
            <td valign="middle">
				<select class="form-control" name="data[signup_captcha]">
					<option value="1" <?php if ($settings['signup_captcha'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['signup_captcha'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show Site Stats:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_site_statistics]">
					<option value="1" <?php if ($settings['show_site_statistics'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_site_statistics'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Time Zone:</td>
            <td valign="middle">
				<select class="form-control" name="data[website_timezone]">
				<option value="">--- Use System Default ---</option>
				<?php if (count($timezone) > 0) { ?>
					<?php foreach ($timezone as $v) { ?>
						<option value="<?php echo $v; ?>" <?php if ($settings['website_timezone'] == $v) echo "selected"; ?>><?php echo $v; ?></option>
					<?php } ?>
				<?php } ?>
				</select>				
			</td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="general" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
		  </table>
		</form>
	</div>



	<div id="jobs" class="tab_content">
	<form action="#jobs" method="post">
		<?php if (isset($tabid) && $tabid == "jobs") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table cellpadding="2" cellspacing="3" border="0">
		<tr>
            <td valign="middle" align="left" class="tb1">Job Approval Fee:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[job_approval_fee]" value="<?php echo $settings['job_approval_fee']; ?>" size="3" class="form-control" /></td>
          </tr>
			<?php
					if (strstr($settings['job_fee'], '%'))
					{
						$job_fee = str_replace('%','',$settings['job_fee']);
						$selected1 = "";
						$selected2 = "selected";
					}
					else
					{
						$job_fee = $settings['job_fee'];
						$selected1 = "selected";
						$selected2 = "";
					}
			?>
          <tr>
            <td valign="middle" align="left" class="tb1">Job Fee:</td>
            <td valign="middle">
				<input type="text" name="data[job_fee]" value="<?php echo $job_fee; ?>" size="3" class="form-control" />
				<select class="form-control" name="job_fee_sign">
					<option value="" <?php echo $selected1; ?>><?php echo SITE_CURRENCY; ?></option>
					<option value="%" <?php echo $selected2; ?>>%</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Auto approve tasks after:</td>
            <td valign="middle"><input type="text" name="data[auto_confirm_days]" value="<?php echo $settings['auto_confirm_days']; ?>" size="3" class="form-control" /> day(s) <span class="note">pending tasks will be auto marked as satisfied & paid to worker</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Delete notcompleted tasks after:</td>
            <td valign="middle"><input type="text" name="data[del_notcomplete_days]" value="<?php echo $settings['del_notcomplete_days']; ?>" size="3" class="form-control" /> day(s)</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Worker can delete "awaitng review" tasks:</td>
            <td valign="middle">
				<select class="form-control" name="data[cancel_applicants]">
					<option value="1" <?php if ($settings['cancel_applicants'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['cancel_applicants'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show jobs for not registered users:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_jobs_unreg]">
					<option value="1" <?php if ($settings['show_jobs_unreg'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_jobs_unreg'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show job details for not registered users:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_job_info_unreg]">
					<option value="1" <?php if ($settings['show_job_info_unreg'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_job_info_unreg'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show workers list for not registered users:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_workers_unreg]">
					<option value="1" <?php if ($settings['show_workers_unreg'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_workers_unreg'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show user profile for not registered users:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_users_unreg]">
					<option value="1" <?php if ($settings['show_users_unreg'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_users_unreg'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show latest completed tasks:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_lastest_completed]">
					<option value="1" <?php if ($settings['show_lastest_completed'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_lastest_completed'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show jobs on homepage:</td>
            <td valign="middle">
				<select class="form-control" name="data[show_homepage_jobs]">
					<option value="1" <?php if ($settings['show_homepage_jobs'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_homepage_jobs'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage jobs:</td>
            <td valign="middle">
				<select class="form-control" name="data[homepage_jobs_type]">
					<option value="1" <?php if ($settings['homepage_jobs_type'] == "1") echo "selected"; ?>>only homepage jobs</option>
					<option value="0" <?php if ($settings['homepage_jobs_type'] == "0") echo "selected"; ?>>show recent jobs</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage jobs:</td>
            <td valign="middle">
				<input type="text" name="data[homepage_jobs_limit]" value="<?php echo $settings['homepage_jobs_limit']; ?>" size="3" class="form-control" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Hide jobs from users from other countries:</td>
            <td valign="middle">
				<select class="form-control" name="data[hide_not_country_jobs]">
					<option value="1" <?php if ($settings['hide_not_country_jobs'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['hide_not_country_jobs'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note">make jobs viewable only to users of that country</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Auto hide completed jobs from worker:</td>
            <td valign="middle">
				<select class="form-control" name="data[worker_hide_jobs]">
					<option value="1" <?php if ($settings['worker_hide_jobs'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['worker_hide_jobs'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Auto approve new jobs:</td>
            <td valign="middle">
				<select class="form-control" name="data[auto_approve_jobs]">
					<option value="1" <?php if ($settings['auto_approve_jobs'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['auto_approve_jobs'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
			<tr>
            <td valign="middle" align="left" class="tb1">Verification code:</td>
            <td valign="middle">
				<select class="form-control" name="data[proof_vcode]">
					<option value="1" <?php if ($settings['proof_vcode'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['proof_vcode'] == "0") echo "selected"; ?>>off</option>
				</select>
				<span class="note">allow to use verification code for job's proof</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Default time to complete job:</td>
            <td valign="middle"><input type="text" name="data[default_job_time]" value="<?php echo $settings['default_job_time']; ?>" size="3" class="form-control" /> minutes</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Job's countries limit:</td>
            <td valign="middle"><input type="text" name="data[job_countries_limit]" value="<?php echo $settings['job_countries_limit']; ?>" size="3" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Allow proof's files:</td>
            <td valign="middle">
				<select class="form-control" name="data[allow_proofs_upload]">
					<option value="1" <?php if ($settings['allow_proofs_upload'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['allow_proofs_upload'] == "0") echo "selected"; ?>>no</option>
				</select>			
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Proof's file max size:</td>
            <td valign="middle"><input type="text" name="data[proof_max_size]" value="<?php echo $settings['proof_max_size']; ?>" size="10" class="form-control" /> bytes</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Proof's files allowed:</td>
            <td valign="top"><input type="text" name="data[proof_filetypes]" value="<?php echo $settings['proof_filetypes']; ?>" size="25" class="form-control" /> <span class="note">example: gif|jpg|png|jpeg</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Min worker rating:</td>
            <td valign="top"><input type="text" name="data[min_worker_rating]" value="<?php echo $settings['min_worker_rating']; ?>" size="3" class="form-control" /> <span class="note">to apply jobs</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Min job positions:</td>
            <td valign="top"><input type="text" name="data[min_positions]" value="<?php echo $settings['min_positions']; ?>" size="3" class="form-control" /> <span class="note">minimum positions per job</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Featured job fee:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[featured_job_fee]" value="<?php echo $settings['featured_job_fee']; ?>" size="3" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Jobs per page:</td>
            <td valign="top">
				<select class="form-control" name="data[results_per_page]">
					<option value="10" <?php if ($settings['results_per_page'] == "10") echo "selected"; ?>>10</option>
					<option value="20" <?php if ($settings['results_per_page'] == "20") echo "selected"; ?>>20</option>
					<option value="50" <?php if ($settings['results_per_page'] == "50") echo "selected"; ?>>50</option>
					<option value="100" <?php if ($settings['results_per_page'] == "100") echo "selected"; ?>>100</option>
				</select>
            </td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="jobs" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
		</table>
	</form>
	</div>


	<div id="offers" class="tab_content">
	<form action="#offers" method="post">
		<?php if (isset($tabid) && $tabid == "offers") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="left" class="tb1">Post offers:</td>
            <td valign="middle">
				<select class="form-control" name="data[submit_services]">
					<option value="1" <?php if ($settings['submit_services'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['submit_services'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note">allow users post service they provide</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage offers:</td>
            <td valign="middle">
				<input type="text" name="data[homepage_offers_limit]" value="<?php echo $settings['homepage_offers_limit']; ?>" size="3" class="form-control" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Auto approve new offers:</td>
            <td valign="middle">
				<select class="form-control" name="data[auto_approve_services]">
					<option value="1" <?php if ($settings['auto_approve_services'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['auto_approve_services'] == "0") echo "selected"; ?>>no</option>
				</select>
			</td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="offers" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
		</table>
	</form>
	</div>


	<div id="users" class="tab_content">
	<form action="#users" method="post">
		<?php if (isset($tabid) && $tabid == "users") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="left" class="tb1">Sign Up Bonus:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[signup_credit]" value="<?php echo $settings['signup_credit']; ?>" size="3" class="form-control" />
				<span class="note">Sign up bonus for new members (0 = disabled)</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Refer a Friend Bonus:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[refer_credit]" value="<?php echo $settings['refer_credit']; ?>" size="3" class="form-control" />
				<span class="note">Amount which users earn when they refer a friend (0 = disabled)</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Confirm Refer a Friend after:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[refer_credit_after]" value="<?php echo $settings['refer_credit_after']; ?>" size="3" class="form-control" />
				<span class="note">Confirm 'Refer a Friend' bonus for user when his friend's account balance reach this amount (0 = disabled)</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Minimum Payout:</td>
            <td nowrap="nowrap" valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[min_payout]" value="<?php echo $settings['min_payout']; ?>" size="3" class="form-control" />
				<span class="note">Amount which users need to earn before they request payout</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Account Email Activation:</td>
            <td valign="middle">
				<select class="form-control" name="data[account_activation]">
					<option value="1" <?php if ($settings['account_activation'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['account_activation'] == "0") echo "selected"; ?>>off</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Address Verification:</td>
            <td valign="middle">
				<select class="form-control" name="data[pin_verification]">
					<option value="1" <?php if ($settings['pin_verification'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['pin_verification'] == "0") echo "selected"; ?>>off</option>
				</select>
				<span class="note" title="user will need to enter pin code to verify his physical address to be able to do jobs"></span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Block Spam Emails:</td>
            <td valign="middle">
				<select class="form-control" name="data[check_spam_email]">
					<option value="1" <?php if ($settings['check_spam_email'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['check_spam_email'] == "0") echo "selected"; ?>>off</option>
				</select>
				&nbsp;<a href="email_blacklist.php" target="_blank">manage blacklist &#155;</a>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Avatars Size:</td>
            <td valign="middle">
				<input type="text" name="data[avatar_width]" value="<?php echo $settings['avatar_width']; ?>" size="3" class="form-control" /> x 
				<input type="text" name="data[avatar_height]" value="<?php echo $settings['avatar_height']; ?>" size="3" class="form-control" /> px
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Allow 1 Sign Up per IP address:</td>
            <td valign="middle">
				<select class="form-control" name="data[block_same_ip]">
					<option value="0" <?php if ($settings['block_same_ip'] == "0") echo "selected"; ?>>off</option>
					<option value="1" <?php if ($settings['block_same_ip'] == "1") echo "selected"; ?>>on</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Bonus Payments:</td>
            <td valign="middle">
				<select class="form-control" name="data[allow_send_bonus]">
					<option value="1" <?php if ($settings['allow_send_bonus'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['allow_send_bonus'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note">allow employer send bonus payment for worker(s)</span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Min bonus payment:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[min_bonus]" value="<?php echo $settings['min_bonus']; ?>" size="3" class="form-control" /> <span class="note">minimum amount for bonus payment</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Bonus payment fee:</td>
            <td valign="middle"><input type="text" name="data[bonus_fee]" value="<?php echo $settings['bonus_fee']; ?>" size="3" class="form-control" />% <span class="note">site's fee for bonus payments</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Worker can apply for:</td>
            <td valign="middle"><input type="text" name="data[daily_applications]" value="<?php echo $settings['daily_applications']; ?>" size="3" class="form-control" />&nbsp; jobs per day <span class="note">(0 = no limit)</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Allow premium accounts:</td>
            <td valign="middle">
				<select class="form-control" name="data[premium_accounts]">
					<option value="1" <?php if ($settings['premium_accounts'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['premium_accounts'] == "0") echo "selected"; ?>>no</option>
				</select>			
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Premium account fee:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[premium_account_fee]" value="<?php echo $settings['premium_account_fee']; ?>" size="3" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Premium worker can apply for:</td>
            <td valign="middle"><input type="text" name="data[premium_daily_applications]" value="<?php echo $settings['premium_daily_applications']; ?>" size="3" class="form-control" />&nbsp; jobs per day <span class="note">(0 = no limit)</span></td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="users" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
		</table>
	</form>
	</div>


	<div id="payment" class="tab_content">
	<form action="#payment" method="post">
		<?php if (isset($tabid) && $tabid == "payment") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="left" class="tb1">Min Deposit:</td>
            <td valign="middle"><?php echo SITE_CURRENCY; ?> <input type="text" name="data[min_deposit]" value="<?php echo $settings['min_deposit']; ?>" size="3" class="form-control" /> <span class="note">minimum amount for deposit</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">PayPal Account:</td>
            <td valign="middle">
				<input type="text" name="data[paypal_account]" value="<?php echo $settings['paypal_account']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/paypal.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Payza Account:</td>
            <td valign="middle">
				<input type="text" name="data[payza_account]" value="<?php echo $settings['payza_account']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/payza.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Payza IPN Security Code:</td>
            <td valign="middle">
				<input type="password" name="data[payza_security_code]" value="<?php echo $settings['payza_security_code']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/payza.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Skrill Account:</td>
            <td valign="middle">
				<input type="text" name="data[skrill_account]" value="<?php echo $settings['skrill_account']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/skrill.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Skrill Secret Word:</td>
            <td valign="middle">
				<input type="password" name="data[skrill_security_code]" value="<?php echo $settings['skrill_security_code']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/skrill.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td width="100" valign="middle" align="left" class="tb1">Authorize.net API Login ID:</td>
            <td valign="middle">
				<input type="text" name="data[authorize_login]" value="<?php echo $settings['authorize_login']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/authorize-net.png" align="absmiddle" />
			</td>
          </tr>
		  <tr>
            <td valign="middle" align="left" class="tb1">Authorize.net Transaction Key:</td>
            <td valign="middle">
				<input type="text" name="data[authorize_transaction_key]" value="<?php echo $settings['authorize_transaction_key']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/authorize-net.png" align="absmiddle" />
			</td>
          </tr>
		  <tr>
            <td valign="middle" align="left" class="tb1">Authorize.net MD5 Hash:</td>
            <td valign="middle">
				<input type="text" name="data[authorize_md5_hash]" value="<?php echo $settings['authorize_md5_hash']; ?>" size="33" class="form-control" />&nbsp; <img src="images/icons/authorize-net.png" align="absmiddle" />
			</td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="payment" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
		</table>
	</form>
	</div>


	<div id="facebook" class="tab_content">
		<form action="#facebook" method="post">
		<?php if (isset($tabid) && $tabid == "facebook") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table width="100%" cellpadding="2" cellspacing="3" border="0">
		<tr>
			<td width="60%" align="left" valign="top">
				<table cellpadding="2" cellspacing="3" border="0">
				  <tr>
					<td valign="middle" align="left" class="tb1">Facebook Connect:</td>
					<td valign="top">
						<select class="form-control" name="data[facebook_connect]">
							<option value="1" <?php if ($settings['facebook_connect'] == "1") echo "selected"; ?>>yes</option>
							<option value="0" <?php if ($settings['facebook_connect'] == "0") echo "selected"; ?>>no</option>
						</select>				
					</td>
				  </tr>
				  <tr>
					<td valign="middle" align="left" class="tb1">App ID:</td>
					<td valign="top"><input type="text" name="data[facebook_appid]" value="<?php echo $settings['facebook_appid']; ?>" size="40" class="form-control" /></td>
				  </tr>
				  <tr>
					<td valign="middle" align="left" class="tb1">App Secret:</td>
					<td valign="top"><input type="text" name="data[facebook_secret]" value="<?php echo $settings['facebook_secret']; ?>" size="40" class="form-control" /></td>
				  </tr>
				  <tr>
					<td align="center" valign="bottom">&nbsp;</td>
					<td align="left" valign="top">
						<input type="hidden" name="tabid" id="tabid" value="facebook" />
						<input type="hidden" name="action" id="action" value="savesettings" />
						<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
					</td>
				  </tr>
				</table>
			</td>
			<td width="40%" align="left" valign="top">
				<p style="text-align: justify">
					To enable this feature, a valid Facebook App ID/API key and App secret are required. This information is provided on the <a href="https://developers.facebook.com/apps" target="_blank">apps page</a> of Facebook's developer website. <br/><br/>If you don't already have an existing Facebook app, you will need to create one to get the App ID/API key and App secret to use with this feature.
				</p>
			</td>
		</tr>
		</table>
		</form>
	</div>
	
	<div id="youtube" class="tab_content">
<form action="#youtube" method="post">
<?php if (isset($tabid) && $tabid == "youtube") { ?>
<?php if (isset($allerrors) && $allerrors != "") { ?>
<div class="alert alert-danger"><?php echo $allerrors; ?></div>
<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
<div class="alert alert-success">Settings have been successfully saved</div>
<?php } ?>
<?php } ?>
<table cellpadding="2" cellspacing="3" border="0">
<tr>
        <td valign="middle" align="left" class="tb1">YouTube API Key:</td>
        <td valign="middle"><input type="text" name="data[youtube_api_key]" value="<?php echo $settings['youtube_api_key']; ?>" size="40" class="form-control" /></td>
    </tr>
    <tr>
        <td align="center" valign="bottom">&nbsp;</td>
<td align="left" valign="top">
<input type="hidden" name="tabid" id="tabid" value="youtube" />
<input type="hidden" name="action" id="action" value="savesettings" />
<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
        </td>
    </tr>
</table>
</form>
</div>
	


	<div id="mail" class="tab_content">

		<script type="text/javascript">
		$(function(){
			send_mail_method();
		});
		function send_mail_method(){
			emethod = $("#smtp_mail").val();
			if(emethod == 1){
				$("#smtp_details").show();
			}else{
				$("#smtp_details").hide();
			}
		}
		</script>

		<form action="#mail" method="post">
		<?php if (isset($tabid) && $tabid == "mail") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table width="100%" cellpadding="2" cellspacing="3" border="0">
		<tr>
			<td width="150" valign="middle" align="right" class="tb1">Site Emails From Name:</td>
			<td valign="middle" align="left"><input type="text" name="data[email_from_name]" value="<?php echo $settings['email_from_name']; ?>" size="30" class="form-control" /></td>
		</tr>
          <tr>
            <td valign="middle" align="right" class="tb1">No-reply Email Address:</td>
            <td valign="middle"><input type="text" name="data[noreply_email]" value="<?php echo $settings['noreply_email']; ?>" size="30" class="form-control" /></td>
          </tr>
		<tr>
			<td valign="middle" align="right" class="tb1">Mail Type:</td>
			<td valign="middle">
				<select class="form-control" name="data[smtp_mail]" id="smtp_mail" onchange="send_mail_method();">
					<option value="0" <?php echo ($settings['smtp_mail'] == "0") ? "selected" : ""; ?>>PHP mail()</option>
					<option value="1" <?php echo ($settings['smtp_mail'] == "1") ? "selected" : ""; ?>>SMTP</option>
				</select>				
			</td> 
		</tr>
		</table>		
		<table cellpadding="2" cellspacing="3"  border="0" id="smtp_details" <?php if ($settings['smtp_mail'] != 1 && @$data['smtp_mail'] != 1) { ?>style="display: none;"<?php } ?>>
		<tr>
			<td width="150" valign="middle" align="right" class="tb1">SMTP Port:</td>
			<td valign="middle"><input type="text" name="data[smtp_port]" value="<?php echo $settings['smtp_port']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="right" class="tb1">SMTP Host:</td>
			<td valign="middle"><input type="text" name="data[smtp_host]" value="<?php echo $settings['smtp_host']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="right" class="tb1">SMTP Username:</td>
			<td valign="middle"><input type="text" name="data[smtp_username]" value="<?php echo $settings['smtp_username']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="right" class="tb1">SMTP Password:</td>
			<td valign="middle"><input type="password" name="data[smtp_password]" value="<?php echo $settings['smtp_password']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="right" class="tb1">SMTP SSL Type:</td>
			<td valign="middle">
				<label><input type="radio" name="data[smtp_ssl]" value="" <?php echo ($settings['smtp_ssl'] == "") ? "checked" : ""; ?> /> None</label> 
				<label><input type="radio" name="data[smtp_ssl]" value="ssl" <?php echo ($settings['smtp_ssl'] == "ssl") ? "checked" : ""; ?> /> SSL</label>
				<label><input type="radio" name="data[smtp_ssl]" value="tls" <?php echo ($settings['smtp_ssl'] == "tls") ? "checked" : ""; ?> /> TLS</label>					
			</td>
		</tr>
		</table>
		<table cellpadding="2" cellspacing="3"  border="0">
		<tr>
			<td width="150" align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="mail" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
		</tr>
		</table>
		</form>
	</div>


	<div id="notifications" class="tab_content">
		<form action="#notifications" method="post">
		<?php if (isset($tabid) && $tabid == "notifications") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<p><b>Notify admin by email when:</b></p>
		<table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="5" valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><input type="hidden" name="data[email_new_job]" value="0" /><input type="checkbox" name="data[email_new_job]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_job'] == 1) ? "checked" : "" ?>/>&nbsp; new job added</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><input type="hidden" name="data[email_new_offer]" value="0" /><input type="checkbox" name="data[email_new_offer]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_offer'] == 1) ? "checked" : "" ?>/>&nbsp; new offer added</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><input type="hidden" name="data[email_new_deposit]" value="0" /><input type="checkbox" name="data[email_new_deposit]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_deposit'] == 1) ? "checked" : "" ?> />&nbsp; new deposit made</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><input type="hidden" name="data[email_new_withdraw]" value="0" /><input type="checkbox" name="data[email_new_withdraw]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_withdraw'] == 1) ? "checked" : "" ?>/>&nbsp; new withdraw request</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><input type="hidden" name="data[email_new_ticket]" value="0" /><input type="checkbox" name="data[email_new_ticket]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_ticket'] == 1) ? "checked" : "" ?> />&nbsp; new support ticket</td>
          </tr>
          <tr>
			<td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="notifications" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
          </tr>
		  </table>
		</form>
	</div>


	<div id="other" class="tab_content">
		<form action="#other" method="post">
		<?php if (isset($tabid) && $tabid == "other") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="left" class="tb1">Facebook Page URL:</td>
            <td valign="top"><input type="text" name="data[facebook_page]" value="<?php echo $settings['facebook_page']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show Facebook Like Box:</td>
            <td valign="top">
				<select class="form-control" name="data[show_fb_likebox]">
					<option value="1" <?php if ($settings['show_fb_likebox'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_fb_likebox'] == "0") echo "selected"; ?>>no</option>
				</select>				
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Twitter Page URL:</td>
            <td valign="top"><input type="text" name="data[twitter_page]" value="<?php echo $settings['twitter_page']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Google Plus URL:</td>
            <td valign="top"><input type="text" name="data[googleplus_page]" value="<?php echo $settings['googleplus_page']; ?>" size="40" class="form-control" /></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">ADDthis.com ID:</td>
            <td valign="top"><input type="text" name="data[addthis_id]" value="<?php echo $settings['addthis_id']; ?>" size="20" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Google Analytics:</td>
            <td valign="top"><textarea name="data[google_analytics]" cols="55" rows="5" class="form-control"><?php echo $settings['google_analytics']; ?></textarea></td>
          </tr>
          <tr>
			<td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="other" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
          </tr>
		</table>
		</form>
	</div>


	<div id="password" class="tab_content">
		<form action="#password" method="post">
		<?php if (isset($tabid) && $tabid == "password") { ?>
			<?php if (isset($allerrors2) && $allerrors2 != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors2; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Password has been changed successfully</div>
			<?php } ?>
		<?php } ?>
		<img src="images/icons/password.gif" style="position: absolute; right: 10px;" />
        <table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="100" valign="middle" align="left" class="tb1">Current Password:</td>
            <td valign="top"><input type="password" name="cpassword" value="" size="30" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">New Admin Password:</td>
            <td valign="top"><input type="password" name="npassword" value="" size="30" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Confirm New Password:</td>
            <td valign="top"><input type="password" name="npassword2" value="" size="30" class="form-control" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="password" />
				<input type="hidden" name="action" id="action" value="updatepassword" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Change Password" />
			</td>
          </tr>
        </table>
		</form>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>