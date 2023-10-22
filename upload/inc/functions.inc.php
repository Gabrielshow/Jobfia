<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/


/**
 * Run mysql query
 * @param	$sql		mysql query to run
 * @return	boolean		false if failed mysql query
*/

function smart_mysql_query($sql)
{
	global $conn;
	$res = mysqli_query($conn, $sql) or die("<p align='center'><span style='font-size:13px; font-family: tahoma, arial, helvetica, sans-serif; color: #000;'>query failed: ".mysqli_error($conn)."</span></p>");
	if (!$res) { return false; }
	return $res;
}



/**
 * Retrieves parameter from POST array
 * @param	$name	parameter name
*/

function getPostParameter($name)
{
	$data = isset($_POST[$name]) ? $_POST[$name] : null;
	if(!is_null($data) && get_magic_quotes_gpc() && is_string($data))
	{
		$data = stripslashes($data);
	}
	$data = trim($data);
	$data = htmlentities($data, ENT_QUOTES, 'UTF-8');
	return $data;
}



/**
 * Retrieves parameter from GET array
 * @param	$name	parameter name
*/

function getGetParameter($name)
{
	return isset($_GET[$name]) ? $_GET[$name] : false;
}



/**
 * Tries to find parameter in GET array, if it is not set
 * tries to return from post array
 * @param	$name	Pararmeter name
 */

function getGetOrPostParameter($name){
	if($value = getGetParameter($name)){
		return $value;
	}else{
		return getPostParameter($name);
	}
}



/** 
 * Retrieves parameter from SESSION array
 * @param	$name	Parameter name
 */

function getSessionParameter($name){
	return isset($_SESSION[$name]) ? $_SESSION[$name] : false;
}


/**
 * Check if user logged in
 * @return	boolen		false or true
*/

if (!function_exists('isLoggedIn')) {
	function isLoggedIn()
	{
		if (!(isset($_SESSION['userid']) && is_numeric($_SESSION['userid'])))
			return false;
		else
			return true;
	}
}


/**
 * Returns random password
 * @param	$length		length of string
 * @return	string		random password
*/

if (!function_exists('generatePassword')) {
	function generatePassword($length = 8)
	{
		$password = "";
		$possible = "0123456789bcdfghjkmnpqrstvwxyz";
		$i = 0; 

		while ($i < $length)
		{ 
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			if (!strstr($password, $char))
			{ 
				$password .= $char;
				$i++;
			}
		}
		return $password;
	}
}


/**
 * Returns random key
 * @param	$text		string
 * @return	string		random key for user verification
*/

if (!function_exists('GenerateKey')) {
	function GenerateKey($text)
	{
		$text = preg_replace("/[^0-9a-zA-Z]/", " ", $text);
		$text = substr(trim($text), 0, 50);
		$key = md5(time().$text.mt_rand(1000,9999));
		return $key;
	}
}



/**
 * Returns address verification pin code
 * @return	string		address verification pin code
*/

if (!function_exists('GeneratePIN')) {
	function GeneratePIN()
	{
		$pin = substr(md5(uniqid(mt_rand(), true)) , 0, 12);
		return $pin;
	}
}



/**
 * Calculate percentage
 * @param	$amount				Amount
 * @param	$percent			Percent value
 * @return	string				returns formated money value
*/

if (!function_exists('CalculatePercentage')) {
	function CalculatePercentage($amount, $percent)
	{
		return number_format(($amount/100)*$percent,2,'.','');
	}
}



/**
 * Returns formated money value
 * @param	$amount				Amount
 * @param	$hide_currency		Hide or Show currency sign
 * @return	string				returns formated money value
*/

if (!function_exists('DisplayMoney')) {
	function DisplayMoney($amount, $hide_currency = 0)
	{
		$newamount = number_format($amount, 2, '.', '');
		
		if ($hide_currency != 1)
		{
			switch (SITE_CURRENCY_FORMAT)
			{
				case "1": $newamount = SITE_CURRENCY.$newamount; break;
				case "2": $newamount = SITE_CURRENCY." ".$newamount; break;
				case "3": $newamount = SITE_CURRENCY.number_format($amount, 2, ',', ''); break;
				case "4": $newamount = $newamount." ".SITE_CURRENCY; break;
				case "5": $newamount = $newamount.SITE_CURRENCY; break;
				default: $newamount = SITE_CURRENCY.$newamount; break;
			}	
		}
		return $newamount;
	}
}



/**
 * Returns relative date
 * @param	$time			time
 * @return	string			returns relative date
*/

if (!function_exists('relative_date')) {
	function relative_date($time)
	{
		define("SECOND", 1);
		define("MINUTE", 60 * SECOND);
		define("HOUR", 60 * MINUTE);
		define("DAY", 24 * HOUR);
		define("MONTH", 30 * DAY);

		$delta = time() - $time;

		if ($delta < 1 * MINUTE)
		{
			return $delta == 1 ? "one second ago" : $delta . " seconds ago";
		}
		if ($delta < 2 * MINUTE)
		{
			return "a minute ago";
		}
		if ($delta < 45 * MINUTE)
		{
			return floor($delta / MINUTE) . " minutes ago";
		}
		if ($delta < 90 * MINUTE)
		{
			return "an hour ago";
		}
		if ($delta < 24 * HOUR)
		{
			return floor($delta / HOUR) . " hours ago";
		}
		if ($delta < 48 * HOUR)
		{
			return "yesterday";
		}
		if ($delta < 30 * DAY)
		{
			return floor($delta / DAY) . " days ago";
		}
		if ($delta < 12 * MONTH)
		{
			$months = floor($delta / DAY / 30);
			return $months <= 1 ? "one month ago" : $months . " months ago";
		}
		else
		{
			$years = floor($delta / DAY / 365);
			return $years <= 1 ? "one year ago" : $years . " years ago";
		}
	}
}



/**
 * Check email address blacklist
 * @param	$email		email
 * @return	boolean		false or true
*/

if (!function_exists('disposable_check')) {
function disposable_check($email)
{
	$result = smart_mysql_query("SELECT * FROM jobfia_email_blacklist WHERE status='active'");
	if (mysqli_num_rows($result) > 0)
	{
		unset($blacklist);
		$blacklist = array();
		while ($row = mysqli_fetch_array($result))
		{
			$blacklist[] = trim($row['domain']);
		}

		$email_split = explode('@', $email);
		$email_domain = $email_split[1];

		if (in_array($email_domain, $blacklist)) return true;
	}
	return false;
}
}



/**
 * Returns random string
 * @param	$len	string length
 * @param	$chars	chars in the string
 * @return	string	random string
*/

if (!function_exists('GenerateRandString')) {
	function GenerateRandString(int $length, string $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string 
{
  $string = '';
  
  for($i = 0; $i < $length; $i++) {

    $charIndex = rand(0, strlen($chars) - 1);
    
    $string .= $chars[$charIndex];
  }

  return $string;
}
}



/**
 * Returns random order's reference ID
 * @return	string	Reference ID
*/

if (!function_exists('GenerateReferenceID')) {
	function GenerateReferenceID()
	{
		unset($num);

		$num = GenerateRandString(8,"0123456789");
    
		$check = smart_mysql_query("SELECT * FROM jobfia_transactions WHERE reference_id='$num'");
		if (mysqli_num_rows($check) == 0)
		{
			return $num;
		}
		else
		{
			return GenerateOrderID();
		}
	}
}


/**
 * Check user login
 * @return	boolen			false or true
*/
if (!function_exists('CheckCookieLogin')) {
function CheckCookieLogin()
{
	global $conn;
	
    $uname = mysqli_real_escape_string($conn, $_COOKIE['usname']);

	if (!empty($uname))
	{
        $check_query = "SELECT * FROM jobfia_users WHERE login_session='$uname' LIMIT 1";
		$check_result = smart_mysql_query($check_query);
		
		if (mysqli_num_rows($check_result) > 0)
		{
			$row = mysqli_fetch_array($check_result);
			
			$_SESSION['userid'] = $row['user_id'];
			$_SESSION['FirstName'] = $row['fname'];

			setcookie("uname", $uname, time()+3600*24*365, '/');

			return true;
		}
		else
		{
			return false;
		}
    }
	else
	{
		return false;
	}
}
}



/**
 * Returns Encrypted password
 * @param	$password	password
 * @return	string		encrypted password
*/

if (!function_exists('PasswordEncryption')) {
	function PasswordEncryption($password)
	{
		return md5(sha1($password));
	}
}



/**
 * Returns setting value by setting's key
 * @param	$setting_key	Setting's Key
 * @return	string	setting's value
*/

if (!function_exists('GetSetting')) {
	function GetSetting($setting_key)
	{
		$setting_result = smart_mysql_query("SELECT setting_value FROM jobfia_settings WHERE setting_key='".$setting_key."'");
		$setting_total = mysqli_num_rows($setting_result);

		if ($setting_total > 0)
		{
			$setting_row = mysqli_fetch_array($setting_result);
			$setting_value = $setting_row['setting_value'];
		}
		else
		{
			die ("config settings not found");
		}

		return $setting_value;
	}
}



/**
 * Returns content for static pages
 * @param	$content_name	Content's Name or Content ID
 * @return	array	(1) - Page Title, (2) - Page Text
*/

if (!function_exists('GetContent')) {
	function GetContent($content_name)
	{
		if (is_numeric($content_name))
		{
			$content_id = (int)$content_name;
			$content_result = smart_mysql_query("SELECT * FROM jobfia_content WHERE content_id='".$content_id."' LIMIT 1");
		}
		else
		{
			$content_result = smart_mysql_query("SELECT * FROM jobfia_content WHERE name='".$content_name."' LIMIT 1");
		}

		if (mysqli_num_rows($content_result) > 0)
		{
			$content_row = mysqli_fetch_array($content_result);
			$contents['title'] = stripslashes($content_row['title']);
			$contents['text'] = stripslashes($content_row['description']);
			$contents['meta_description']	= stripslashes($content_row['meta_description']);
			$contents['meta_keywords']		= stripslashes($content_row['meta_keywords']);
		}
		else
		{
			$contents['title'] = "Page not found";
			$contents['text'] = "<p align='center'>Sorry, page not found.</p><p align='center'><a class='goback' href='".SITE_URL."'>Go to home page</a></p>";
		}

		return $contents;
	}
}



/**
 * Sends email
 * @param	$recipient		Email Recipient
 * @param	$subject		Email Subject
 * @param	$message		Email Message
 * @param	$noreply_mail	No Reply Email flag
 * @param	$from			FROM headers
*/

if (!function_exists('SendEmail')) {
	function SendEmail($recipient, $subject, $message, $noreply_mail = 0, $from = "")
	{
		define('EMAIL_TYPE', 'html');			// html, text
		define('EMAIL_CHARSET', 'UTF-8');

		if ($noreply_mail == 1) $SITE_MAIL = NOREPLY_MAIL; else $SITE_MAIL = SITE_MAIL;

		if (SMTP_MAIL == 1)
		{
			require_once('phpmailer/PHPMailerAutoload.php');

			$mail = new PHPMailer();
			
			$mail->IsSMTP();
			$mail->CharSet = EMAIL_CHARSET;		// email charset
			$mail->SMTPDebug = 0;				// 0 = no output, 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true;				// enable SMTP authentication
			$mail->SMTPSecure = SMTP_SSL;		// sets the prefix to the servier (ssl, tls)
			$mail->Host = SMTP_HOST;			// SMTP server
			$mail->Port = SMTP_PORT;			// SMTP port
			$mail->Username = SMTP_USERNAME;	// SMTP username
			$mail->Password = SMTP_PASSWORD;	// SMTP password

			if (EMAIL_TYPE == "text")
			{
				$mail->ContentType = 'text/plain';
				$mail->IsHTML(false);
			}
			else
			{
				$mail->IsHTML(true);
			}

			$mail->Subject = $subject;
			if ($from != "")
			{
				$afrom = str_replace('>', '', $from);
				$aafrom = explode("<", $afrom);
				$from_name = $aafrom[0];
				$from_email = $aafrom[1];
				$mail->SetFrom ($from_email, $from_name);
			}
			else
			{
				$mail->SetFrom ($SITE_MAIL, EMAIL_FROM_NAME);
			}
			$mail->Body = $message;
			$efrom = str_replace('>', '', $recipient);
			$eefrom = explode("<", $efrom);
			$recipient_name = $eefrom[0];
			$recipient_email = $eefrom[1];

			$mail->AddAddress ($recipient_email, $recipient_name);
			//$mail->AddBCC ('sales@example.com', 'Example.com Sales Dep.');

			if(!$mail->Send())
				return false;
			else
				return true;
		}
		else
		{
			$headers = 'MIME-Version: 1.0' . "\r\n";
			
			if (EMAIL_TYPE == "text")
				$headers .= 'Content-type: text/plain; charset='.EMAIL_CHARSET.'' . "\r\n";
			else
				$headers .= 'Content-type: text/html; charset='.EMAIL_CHARSET.'' . "\r\n";
			
			if ($from != "")
				$headers .= $from. "\r\n";
			else
				$headers .= 'From: '.EMAIL_FROM_NAME.' <'.$SITE_MAIL.'>' . "\r\n";

			mail($recipient, $subject, $message, $headers);
		}
	}
}



/**
 * Returns content for email template
 * @param	$email_name	Email Template Name
 * @return	array	(1) - Email Subject, (2) - Email Message
*/

if (!function_exists('GetEmailTemplate')) {
	function GetEmailTemplate($email_name)
	{
		$etemplate_result = smart_mysql_query("SELECT * FROM jobfia_email_templates WHERE email_name='".$email_name."' LIMIT 1");
		$etemplate_total = mysqli_num_rows($etemplate_result);

		if ($etemplate_total > 0)
		{
			$etemplate_row = mysqli_fetch_array($etemplate_result);
			$etemplate['email_subject'] = stripslashes($etemplate_row['email_subject']);
			$etemplate['email_message'] = stripslashes($etemplate_row['email_message']);

			$etemplate['email_message'] = "<html>
								<head>
									<title>".$etemplate['email_subject']."</title>
								</head>
								<body>
								<table width='80%' border='0' cellpadding='10'>
								<tr>
									<td align='left' valign='top'>".$etemplate['email_message']."</td>
								</tr>
								</table>
								</body>
							</html>";
		}

		return $etemplate;
	}
}



/**
 * Returns categories dropdown list
 * @param	$parent_id		parent category ID
 * @param	$sep			separator
 * @param	$current		current category ID
 * @param	$parent			show/hide parent categories
 * @return	string			categories dropdown list
*/

if (!function_exists('ShowCategoriesDropDown')) {
	function ShowCategoriesDropDown ($parent_id, $sep = "", $current = 0, $parent = 0)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_categories WHERE category_id<>'$current' AND parent_id='$parent_id' ORDER BY name");
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$category_id	= $row['category_id'];
				$category_name	= $row['name'];
				
				if ($parent > 0 && $category_id == $parent) $selected = " selected=\"selected\""; else $selected = "";
				if ($category_id == (int)$_REQUEST['cat'] || $category_id == (int)$_REQUEST['category']) $selected = " selected=\"selected\""; else $selected = "";
				
				echo "<option value=\"".$category_id."\"".$selected.">".$sep.$category_name."</option>\n";
				ShowCategoriesDropDown($category_id, $sep.$category_name." &gt; ", $current, $parent);
			}
		}
	}
}



/**
 * Converts minutes to days/hours/mins
 * @param	$minutes	minutes
 * @return	string		new time
*/

if (!function_exists('ConvertDuration')) {
	function ConvertDuration($minutes)
	{
		$minutes = (int)$minutes;

		$d = floor ($minutes / 1440);
		$h = floor (($minutes - $d * 1440) / 60);
		$m = $minutes - ($d * 1440) - ($h * 60);

		$new_time = "";
		if ($d > 0) $new_time .= $d." days";
		if ($h > 0) $new_time .= $h." hours";
		if ($m > 0) $new_time .= $m." minutes";

		return $new_time;
	}
}



/**
 * Returns sub categories list
 * @param	$cat_id		Primary Category ID
 * @return	array		sub categories list 
*/

if (!function_exists('GetSubCategories')) {
	function GetSubCategories ($cat_id)
	{
		static $sub_categories;

		$result = smart_mysql_query("SELECT category_id FROM jobfia_categories WHERE parent_id='".(int)$cat_id."'");
	
		if (mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$sub_categories[] = $row['category_id'];
				GetSubCategories($row['category_id']);
			}

			return $sub_categories;
		}
	}
}



/**
 * Returns category's min price for posting
 * @param	$category_id	Category ID
 * @return	string			category's min price
*/

if (!function_exists('GetCategoryPrice')) {
	function GetCategoryPrice($category_id)
	{
		$result = smart_mysql_query("SELECT min_price FROM jobfia_categories WHERE category_id='".(int)$category_id."' LIMIT 1");
		
		if (mysqli_num_rows($result) > 0)
		{		
			$row = mysqli_fetch_array($result);
			$category_price = $row['min_price'];

			return $category_price;
		}
	}
}



/**
 * Returns total jobs in category
 * @param	$category_id	Category ID
 * @param	$all			count all jobs
 * @return	integer			total jobs
*/

if (!function_exists('CategoryTotalJobs')) {
	function CategoryTotalJobs ($category_id, $all = 0)
	{
		if ($all == 1)
			$result = smart_mysql_query("SELECT COUNT(job_id) as total FROM jobfia_job_to_category WHERE category_id='$category_id'");
		elseif ($all == 2)
			$result = smart_mysql_query("SELECT COUNT(job_id) as total FROM jobfia_job_to_category WHERE category_id='$category_id' AND job_id IN (SELECT job_id FROM jobfia_jobs WHERE job_type='offer')");
		else
			$result = smart_mysql_query("SELECT COUNT(job_id) as total FROM jobfia_job_to_category WHERE category_id='$category_id' AND job_id IN (SELECT job_id FROM jobfia_jobs WHERE job_type='job')");

		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}



/**
 * Returns job's categories
 * @param	$job_id		Job ID
 * @return	string		job's categories list
*/

if (!function_exists('GetJobCategory')) {
	function GetJobCategory($job_id)
	{
		$sql_job_categories = smart_mysql_query("SELECT cc.*, c.name FROM jobfia_job_to_category cc, jobfia_categories c WHERE cc.category_id=c.category_id AND cc.job_id='".(int)$job_id."' ORDER BY c.name");
		
		if (mysqli_num_rows($sql_job_categories) > 0)
		{
			$job_categories = "";

			while ($row_job_categories = mysqli_fetch_array($sql_job_categories))
			{
				$job_categories .= "<a href='".SITE_URL."jobs.php?cat=".$row_job_categories['category_id']."'>".$row_job_categories['name']."</a>";
			}

			return $job_categories;
		}
		else
		{
			return "---";
		}
	}
}



/**
 * Returns job's targeting (countries list)
 * @param	$job_id				Job ID
 * @param	$show_only_images	Show/Hide country name
 * @return	string				job's countries list
*/

if (!function_exists('GetJobCountriesList')) {
	function GetJobCountriesList($job_id, $show_only_images = 0)
	{
		$sql_job_countires = smart_mysql_query("SELECT jc.country_id, c.* FROM jobfia_job_to_country jc, jobfia_countries c WHERE jc.country_id=c.country_id AND jc.job_id='".(int)$job_id."' ORDER BY c.name");

		if (mysqli_num_rows($sql_job_countires) > 0)
		{
			$job_countires = "";
		
			while ($row_job_countires = mysqli_fetch_array($sql_job_countires))
			{
				if ($show_only_images == 1)
					$job_countires .= "<img src='".SITE_URL."images/flags/".strtolower($row_job_countires['code']).".png' alt='".$row_job_countires['name']."' title='".$row_job_countires['name']."' align='absmiddle' /> ";
				else
					$job_countires .= "<span class='country_list'><img src='".SITE_URL."images/flags/".strtolower($row_job_countires['code']).".png' alt='".$row_job_countires['name']."' title='".$row_job_countires['name']."' align='absmiddle' /> ".$row_job_countires['name']."</span>";
			}

			return $job_countires;
		}
		else
		{
			return false;
			//return "International (All Countries)";
		}
	}
}



/**
 * Checks job's targeting
 * @param	$job_id				Job ID
 * @param	$country			User's country
 * @return	boolean				true/false
*/

if (!function_exists('CheckTargeting')) {
	function CheckTargeting($job_id, $country)
	{
		$sql_job_countires = smart_mysql_query("SELECT * FROM jobfia_job_to_country WHERE job_id='".(int)$job_id."' AND (country_id='".(int)$country."' OR country_id='111111')");
		if (mysqli_num_rows($sql_job_countires) > 0)
			return true;
		else
			return false;
	}
}



/**
 * Returns country name
 * @param	$country_id		Country ID
 * @param	$show_name		Show/Hide country name
 * @return	string			country name
*/

if (!function_exists('GetCountry')) {
	function GetCountry($country_id, $show_name = 1)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_countries WHERE country_id='".(int)$country_id."' LIMIT 1");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			if ($show_name == 1)
				$country_name = "<img src='".SITE_URL."images/flags/".strtolower($row['code']).".png' alt='".$row['name']."' title='".$row['name']."' align='absmiddle' /> ".$row['name']."";
			else
				$country_name = "<img src='".SITE_URL."images/flags/".strtolower($row['code']).".png' alt='".$row['name']."' title='".$row['name']."' align='absmiddle' />";
		
			return $country_name;
		}
	}
}



/**
 * Returns  member's current balance
 * @param	$userid					User ID
 * @param	$hide_currency_option	Hide or show currency sign
 * @return	string					member's current balance
*/

if (!function_exists('GetUserBalance')) {
	function GetUserBalance($userid, $hide_currency_option = 0)
	{
		$query = "SELECT balance FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) != 0)
		{
			$row = mysqli_fetch_array($result);
			$withdraw_requests = mysqli_fetch_array(smart_mysql_query("SELECT SUM(amount) AS total FROM jobfia_transactions WHERE user_id='".(int)$userid."' AND payment_type='withdraw' AND status='request'"));
			
			$balance = $row['balance'] - $withdraw_requests['total'];

			if ($balance> 0)
				return DisplayMoney($balance, $hide_currency_option);
			else
				return DisplayMoney(0, $hide_currency_option);
		}
		else
		{
			return DisplayMoney(0, $hide_currecy_option);
		}
	}
}



/**
 * Confirm Task
 * @param	$task_id	Task ID
*/

if (!function_exists('ConfirmTask')) {
	function ConfirmTask($task_id)
	{
		$task_id = (int)$task_id;
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_task_id='".$task_id."' AND status<>'confirmed'");
		
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			smart_mysql_query("UPDATE jobfia_job_tasks SET status='confirmed' WHERE job_task_id='".$task_id."'");

			$job_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='".(int)$row['job_id']."' LIMIT 1"));
			$employer_id = (int)$job_row['user_id'];


			// if all positions filled - change job status to 'complete'
			$completed_tasks = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' AND ((status='confirmed') OR (status='pending') OR (status='notcomplete'))"));

			if ($completed_tasks['total'] == $job_row['positions'])
				smart_mysql_query("UPDATE jobfia_jobs SET status='complete' WHERE job_id='".(int)$job_id."' LIMIT 1");
			/////////////////////////////////////////////////////////////


			// add job payment to worker
			smart_mysql_query("UPDATE jobfia_users SET balance=balance+".$job_row['price'].", satisfied_tasks=satisfied_tasks+1 WHERE user_id='".(int)$row['worker_id']."' LIMIT 1");
			
			// Deduct Job fee from employer's account balance
			if (strstr(JOB_FEE, '%'))
			{
				$job_price = CalculatePercentage($job_row['price'], JOB_FEE) + $job_row['price'];
				smart_mysql_query("UPDATE jobfia_users SET balance=balance-".$job_price." WHERE user_id='".$employer_id."' LIMIT 1");
			}
			else
			{
				$job_price = JOB_FEE + $job_row['price'];
				smart_mysql_query("UPDATE jobfia_users SET balance=balance-".$job_price." WHERE user_id='".$employer_id."' LIMIT 1");
			}
		}
	}
}



/**
 * Mark as Not Completed Task
 * @param	$task_id	Task ID
*/

if (!function_exists('NotCompleteTask')) {
	function NotCompleteTask($task_id)
	{
		$task_id = (int)$task_id;
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_task_id='$task_id' AND status='pending' LIMIT 1");
		
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			smart_mysql_query("UPDATE jobfia_job_tasks SET status='notcomplete' WHERE job_task_id='$task_id' LIMIT 1");

			// send notification to worker
			NotCompleteAlert($task_id);
		}
	}
}



/**
 * Decline Task
 * @param	$task_id	Task ID
*/

if (!function_exists('DeclineTask')) {
	function DeclineTask($task_id)
	{
		$task_id = (int)$task_id;
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_task_id='$task_id' AND status<>'confirmed' LIMIT 1");
		
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			$job_id = (int)$row['job_id'];
			smart_mysql_query("UPDATE jobfia_job_tasks SET status='declined' WHERE job_task_id='$task_id' LIMIT 1");

			smart_mysql_query("UPDATE jobfia_users SET satisfied_tasks=satisfied_tasks-1 WHERE user_id='".(int)$row['worker_id']."' LIMIT 1");

			// if not all positions filled - change job status to 'active'
			$job_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='$job_id' LIMIT 1"));
			if ($job_row['status'] == "complete")
			{
				$completed_tasks = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id='$job_id' AND ((status='confirmed') OR (status='pending') OR (status='notcomplete'))"));

				if ($completed_tasks['total'] < $job_row['positions'])
					smart_mysql_query("UPDATE jobfia_jobs SET status='active' WHERE job_id='$job_id' LIMIT 1");
			}
		}
	}
}



/**
 * Returns total of pending tasks
 * @param	$userid		Employer ID
 * @return	integer		total of pending tasks
*/

if (!function_exists('GetPendingTasksTotal')) {
	function GetPendingTasksTotal($job_id = 0, $userid = 0)
	{
		if ($job_id > 0)
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' AND status='pending'");
		else
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id IN (SELECT job_id FROM jobfia_jobs WHERE user_id='".(int)$userid."') AND status='pending'");			

		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



if (!function_exists('GetJobPendingTasks')) {
	function GetJobPendingTasks($job_id = 0)
	{
		if ($job_id > 0) $sql = "job_id='".(int)$job_id."' AND";
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE $sql status='pending'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}

if (!function_exists('GetJobPaidTasks')) {
	function GetJobPaidTasks($job_id = 0)
	{
		if ($job_id > 0) $sql = "job_id='".(int)$job_id."' AND";
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE $sql status='confirmed'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}

if (!function_exists('GetJobDeclinedTasks')) {
	function GetJobDeclinedTasks($job_id = 0)
	{
		if ($job_id > 0) $sql = "job_id='".(int)$job_id."' AND";
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE $sql status='declined'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns job invites total number
 * @param	$worker_id	Worker ID
 * @return	integer		job invites total
*/

if (!function_exists('GetJobInvitesTotal')) {
	function GetJobInvitesTotal($worker_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_worker_invites WHERE worker_id='".(int)$worker_id."' AND status='pending'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns job reports total number
 * @return	integer		job reports total
*/

if (!function_exists('GetJobReportsTotal')) {
	function GetJobReportsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_reports WHERE job_id<>'0'"); // AND viewed='0'
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns user reports total number
 * @return	integer		user reports total
*/

if (!function_exists('GetUserReportsTotal')) {
	function GetUserReportsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_reports WHERE user_id<>'0'"); // AND viewed='0'
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns jobs completed tasks total
 * @return	integer		completed tasks total
*/

if (!function_exists('GetDoneTasksTotal')) {
	function GetDoneTasksTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE status='pending' OR status='confirmed'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns jobs total
 * @return	integer		jobs total
*/

if (!function_exists('GetJobsTotal')) {
	function GetJobsTotal($job_type = "")
	{
		if ($job_type != "") $where = "WHERE job_type='$job_type' AND status='active'";
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs $where");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns available job total number
 * @return	integer		available job total
*/

if (!function_exists('GetActiveJobsTotal')) {
	function GetActiveJobsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns pending jobs total number
 * @param	$job_type	Job Type
 * @return	integer		pending jobs total
*/

if (!function_exists('GetPendingJobsTotal')) {
	function GetPendingJobsTotal($job_type = "job")
	{
		if ($job_type != "") $where = "job_type='$job_type' AND";
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE $where status='pending'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns all workers total earnings
 * @return	string		all workers total earnings
*/

if (!function_exists('GetWorkersEarnTotal')) {
	function GetWorkersEarnTotal()
	{
		$result = smart_mysql_query("SELECT SUM(job_price) AS total_earnings FROM jobfia_job_tasks WHERE status='confirmed'");
		$row = mysqli_fetch_array($result);
		return DisplayMoney($row['total_earnings']);
	}
}



/**
 * Returns worker's total earnings
 * @param	$worker_id		Worker ID
 * @return	string			worker's total earnings
*/

if (!function_exists('GetWorkerEarnTotal')) {
	function GetWorkerEarnTotal($worker_id)
	{
		$result = smart_mysql_query("SELECT SUM(job_price) AS total_earnings FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND status='confirmed'");
		$row = mysqli_fetch_array($result);
		return DisplayMoney($row['total_earnings']);
	}
}



/**
 * Returns users total
 * @return	integer		users total
*/

if (!function_exists('GetUsersTotal')) {
	function GetUsersTotal($country_id = "")
	{
		if ($country_id > 0) $sql = "WHERE country_id='".(int)$country_id."'";
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users");
		$row = mysqli_fetch_array($result);
		return number_format($row['total']);
	}
}


/**
 * Returns active workers total
 * @return	integer		active workers total
*/

if (!function_exists('GetWorkersTotal')) {
	function GetWorkersTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE (usertype='worker' OR worker='1') AND status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns employer's posted jobs total
 * @param	$employer_id	Employer ID
 * @return	integer			posted jobs total
*/

if (!function_exists('GetEmployerJobsTotal')) {
	function GetEmployerJobsTotal($employer_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE user_id='".(int)$employer_id."' AND job_type!='offer'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


if (!function_exists('GetUserOffersTotal')) {
	function GetUserOffersTotal($user_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE user_id='".(int)$user_id."' AND job_type='offer'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


if (!function_exists('GetUserSalesTotal')) {
	function GetUserSalesTotal($user_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_orders WHERE offer_id IN (SELECT job_id FROM jobfia_jobs WHERE user_id='".(int)$user_id."' AND job_type='offer') AND status!='cancelled' AND status!='declined'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


if (!function_exists('GetOfferSalesTotal')) {
	function GetOfferSalesTotal($offer_id, $today = 0)
	{
		if ($today == 1) $add_query = " AND DATE(created) = DATE(NOW())"; else $add_query = "";
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_orders WHERE offer_id='".(int)$offer_id."' AND status!='cancelled' AND status!='declined' ".$add_query);
		
		$row = mysqli_fetch_array($result);
		
		if ($today == 1 && $row['total'] > 0)
			return "+".(int)$row['total'];
		else
			return (int)$row['total'];
	}
}

if (!function_exists('GetOrdersQueueTotal')) {
	function GetOrdersQueueTotal($offer_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_orders WHERE offer_id='".(int)$offer_id."' AND status='pending'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns employer's average job price
 * @param	$employer_id	Employer ID
 * @return	string			average job price
*/

if (!function_exists('GetEmployerJobPrice')) {
	function GetEmployerJobPrice($employer_id)
	{
		$result = smart_mysql_query("SELECT AVG(price) AS average_price FROM jobfia_jobs WHERE user_id='".(int)$employer_id."'");
		$row = mysqli_fetch_array($result);
		return DisplayMoney($row['average_price']);
	}
}



/**
 * Returns worker's average job price
 * @param	$worker_id		Worker ID
 * @return	string			average job price
*/

if (!function_exists('GetWorkerJobPrice')) {
	function GetWorkerJobPrice($worker_id)
	{
		$result = smart_mysql_query("SELECT AVG(job_price) AS average_price FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND status='confirmed'");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return DisplayMoney($row['average_price']);
		}
	}
}



/**
 * Returns worker's total submitted jobs
 * @param	$worker_id		Worker ID
 * @return	integer			submitted jobs total
*/

if (!function_exists('GetWorkerJobsTotal')) {
	function GetWorkerJobsTotal($worker_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Returns offer feedbacks total
 * @param	$offer_id		Offer ID
 * @return	integer			offer feedbacks total
*/

if (!function_exists('GetOfferFeedbacksTotal')) {
	function GetOfferFeedbacksTotal($offer_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_feedbacks WHERE $offer_id='".(int)$offer_id."'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns worker's total pending (waiting for employer review) jobs
 * @param	$worker_id		Worker ID
 * @return	integer			pending jobs total
*/

if (!function_exists('GetWorkerPendingJobs')) {
	function GetWorkerPendingJobs($worker_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND status='pending'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns worker's total paid (satisfied) jobs
 * @param	$worker_id		Worker ID
 * @return	integer			paid jobs total
*/

if (!function_exists('GetWorkerPaidJobs')) {
	function GetWorkerPaidJobs($worker_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND status='confirmed'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns worker's total declined (not satisfied) jobs
 * @param	$worker_id		Worker ID
 * @return	integer			declined jobs total
*/

if (!function_exists('GetWorkerDeclinedJobs')) {
	function GetWorkerDeclinedJobs($worker_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND status='declined'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns employer total spend (paid) money
 * @param	$employer_id		Employer ID
 * @return	string				total spend money
*/

if (!function_exists('GetEmployerSpendTotal')) {
	function GetEmployerSpendTotal($employer_id)
	{
		$result = smart_mysql_query("SELECT SUM(job_price) AS total_price FROM jobfia_job_tasks WHERE job_id IN (SELECT job_id FROM jobfia_jobs WHERE user_id='".(int)$employer_id."') AND status='confirmed'");
		$row = mysqli_fetch_array($result);
		return DisplayMoney($row['total_price']);
	}
}



/**
 * Check if worker has premium account
 * @param	$worker_id		Worker ID
 * @return	boolean			false or true
*/

if (!function_exists('isPremium')) {
	function isPremium($worker_id)
	{
		$result = smart_mysql_query("SELECT premium FROM jobfia_users WHERE user_id='".(int)$worker_id."' LIMIT 1");
		$row = mysqli_fetch_array($result);

		if ($row['premium'] == 1) return true; else return false;
	}
}



/**
 * Check job's daily limit
 * @param	$job_id		Job ID
 * @return	boolean		false or true
*/

if (!function_exists('CheckDailyLimit')) {
	function CheckDailyLimit($job_id)
	{
		$result = smart_mysql_query("SELECT daily_limit FROM jobfia_jobs WHERE job_id='".(int)$job_id."' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			$daily_limit = $row['daily_limit'];
			
			if ($daily_limit > 0)
			{
				$tasks_today = GetJobWorkersTotal($job_id, "today");
				if ($daily_limit < $tasks_today)
					return true;
				else 
					return false;
			}
			else
			{
				return true;
			}
		}
	}
}



/**
 * Check if employer's job
 * @param	$job_id				Job ID
 * @param	$employer_id		Employer ID
 * @return	boolean				false or true
*/

if (!function_exists('isOwner')) {
	function isOwner($job_id, $employer_id)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='".(int)$job_id."' AND user_id='".(int)$employer_id."'");
		if (mysqli_num_rows($result) > 0)
			return true;
		else
			return false;
	}
}


/**
 * Check if employer's job
 * @param	$job_id				Job ID
 * @param	$employer_id		Employer ID
 * @return	boolean				false or true
*/

if (!function_exists('isWorked')) {
	function isWorked($job_id, $worker_id)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND job_id='".(int)$job_id."'");
		if (mysqli_num_rows($result) > 0)
			return true;
		else
			return false;
	}
}




/**
 * Check if user verified his physical address
 * @param	$user_id			User ID
 * @return	boolean				false or true
*/

if (!function_exists('isPinVerified')) {
	function isPinVerified($user_id)
	{
		$result = smart_mysql_query("SELECT pin_verified FROM jobfia_user WHERE user_id='".(int)$user_id."' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			
			if ($row['pin_verified'] == 1)
				return true;
		}
		return false;
	}
}



/**
 * Returns job available positions
 * @param	$job_id		Job ID
 * @return	string		available positions
*/

if (!function_exists('GetJobAvailablePositions')) {
	function GetJobAvailablePositions($job_id)
	{
		$row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' AND status<>'declined'"));
		$submitted_num = $row['total'];

		$row2 = mysqli_fetch_array(smart_mysql_query("SELECT positions FROM jobfia_jobs WHERE job_id='".(int)$job_id."'"));
		$positions_num = $row2['positions'];

		$available_positions = $positions_num - $submitted_num;
		
		if ($available_positions > 0)
			return $available_positions;
		else
			return "0";
	}
}



/**
 * Returns filled positions for job
 * @param	$job_id		Job ID
 * @return	integer		filled positions
*/

if (!function_exists('GetJobWorkersTotal')) {
	function GetJobWorkersTotal($job_id, $date = "")
	{
		if ($date != "") { if ($date == "today") $add_query = " AND DATE(added) = DATE(NOW())"; else $add_query = " AND added = '$date'"; }
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' ".$add_query." AND status<>'declined'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Check if employer has enough money to post job
 * @param	$employer_id	Employer ID
 * @return	boolean			false or true
*/

if (!function_exists('MoneyEnough')) {
	function MoneyEnough($employer_id)
	{
		$row = mysqli_fetch_array(smart_mysql_query("SELECT SUM(job_price) AS total FROM jobfia_jobs WHERE user_id='".(int)$employer_id."' AND (status='active' OR status='pending')"));
		$row2 = mysqli_fetch_array(smart_mysql_query("SELECT SUM(amount) AS total FROM jobfia_transactions WHERE user_id='".(int)$employer_id."' AND payment_type='withdraw' AND status='request'"));

		$spent_total = $row['total'] + $row2['total'];
		
		if ($spent_total > GetUserBalance($employer_id, $hide_currency_sign = 1))
			return false;
		else
			return true;
	}
}



/**
 * Check if job has available positions
 * @param	$job_id			Job ID
 * @return	boolean			false or true
*/

if (!function_exists('isAvaliableJob')) {
	function isAvaliableJob($job_id)
	{
		$result = smart_mysql_query("SELECT positions FROM jobfia_jobs WHERE job_id='".(int)$job_id."' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			if ($row['positions'] > GetJobWorkersTotal($job_id))
			{
				return true;
			}
			else
			{				
				return false;
			}
		}
	}
}



/**
 * Sent completed job notification to employer
 * @param	$job_id		Job ID
*/

if (!function_exists('SendJobNotification')) {
	function SendJobNotification($job_id)
	{
		$job_id = (int)$job_id;
		
		$result = smart_mysql_query("SELECT jobs.*, users.* FROM jobfia_jobs jobs, jobfia_users users WHERE jobs.user_id=users.user_id AND jobs.job_id='$job_id' AND users.status='active' AND users.newsletter='1' LIMIT 1");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			$etemplate = GetEmailTemplate('job_complete_alert');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
			$to_email = $row['fname']." ".$row['lname'].' <'.$row['email'].'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
	}
}


/**
 * Sent completed task notification to employer
 * @param	$job_id		Job ID
*/

if (!function_exists('SendTaskNotification')) {
	function SendTaskNotification($job_id)
	{
		$job_id = (int)$job_id;
		
		$result = smart_mysql_query("SELECT jobs.*, users.*, DATE_FORMAT(users.created, '%M %e, %Y') AS signup_date, DATE_FORMAT(users.last_login, '%M %e, %Y') AS last_login_date FROM jobfia_jobs jobs LEFT JOIN jobfia_users users ON jobs.user_id=users.user_id WHERE jobs.job_id='$job_id' AND users.notification3='1' LIMIT 1");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			$etemplate = GetEmailTemplate('new_task_alert');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
			$to_email = $row['fname']." ".$row['lname'].' <'.$row['email'].'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
	}
}



/**
 * Sent new job alert to members
 * @param	$job_id		Job ID
*/

if (!function_exists('SendNewJobNotification')) {
	function SendNewJobNotification($job_id)
	{
		$job_id = (int)$job_id;

		$where = "";
		$sql_jobs_country = smart_mysql_query("SELECT country_id FROM jobfia_job_to_country WHERE job_id='$job_id'");
		if (mysqli_num_rows($sql_jobs_country) > 0)
		{
			unset($jobs_country);
			$jobs_country = array();
			$job_country[] = "111111111111111111111";

			while ($row_jobs_country = mysqli_fetch_array($sql_jobs_country))
			{
				$jobs_country[] = $row_jobs_country['country_id'];
			}
			
			// if not worldwide job
			if (!in_array("111111", $jobs_country))
			{
				$jobs_country = array_map('intval', $jobs_country);
				$where .= "users.country_id IN (".implode(",",$jobs_country).") AND ";
			}
		}
		
		$result = smart_mysql_query("SELECT jobs.*, users.* FROM jobfia_jobs jobs, jobfia_users users WHERE $where jobs.user_id=users.user_id AND jobs.job_id='$job_id' AND users.status='active' AND users.notification1='1'");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			$etemplate = GetEmailTemplate('new_job_alert');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$emessage = str_replace("{job_price}", DisplayMoney($row['price']), $emessage);
			$emessage = str_replace("{job_url}", SITE_URL."job_details.php?id=".$job_id, $emessage);
			$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
			$to_email = $row['fname']." ".$row['lname'].' <'.$row['email'].'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
	}
}


/**
 * Sent job invitations to employer's favorite workers
 * @param	$job_id		Job ID
*/

if (!function_exists('SendJobInvitations')) {
	function SendJobInvitations($job_id)
	{
		$job_id = (int)$job_id;

		$result = smart_mysql_query("SELECT invites.*, jobs.*, users.* FROM jobfia_worker_invites invites, jobfia_jobs jobs, jobfia_users users WHERE invites.job_id=jobs.job_id AND invites.job_id='$job_id' AND invites.worker_id=users.user_id AND invites.status='active' AND users.status='active' AND users.notification2='1'");

		if (mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$etemplate = GetEmailTemplate('job_invitation');
				$esubject = $etemplate['email_subject'];
				$emessage = $etemplate['email_message'];

				$emessage = str_replace("{first_name}", $row['fname'], $emessage);
				$emessage = str_replace("{job_name}", $row['title'], $emessage);
				$emessage = str_replace("{job_price}", DisplayMoney($row['price']), $emessage);
				$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
				$to_email = $row['fname']." ".$row['lname'].' <'.$row['email'].'>';

				SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
			}
		}
	}
}


if (!function_exists('WorkerInFavoritesTotal')) {
	function WorkerInFavoritesTotal($user_id)
	{
		$user_id = (int)$user_id;

		$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_my_workers WHERE worker_id='$user_id' AND status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Sent not complete task notification to worker
 * @param	$task_id		Task ID
*/

if (!function_exists('NotCompleteAlert')) {
	function NotCompleteAlert($task_id)
	{
		$task_id = (int)$task_id;
		
		$result = smart_mysql_query("SELECT job_tasks.*, users.* FROM jobfia_job_tasks job_tasks, jobfia_users users WHERE job_tasks.job_task_id='".$task_id."' AND job_tasks.worker_id=users.user_id AND users.status='active' AND users.newsletter='1' LIMIT 1");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);

			$etemplate = GetEmailTemplate('job_notcomplete_alert');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];

			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
			$to_email = $row['fname']." ".$row['lname'].' <'.$row['email'].'>';

			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
	}
}



/**
 * Check if job was finished
 * @param	$job_id			Job ID
 * @param	$worker_id		Worker ID
 * @return	boolean			false or true
*/

if (!function_exists('isCompleteJob')) {
	function isCompleteJob($job_id, $worker_id)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='".(int)$job_id."' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			if ($row['few_times'] != 1)
			{
				$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE worker_id='".(int)$worker_id."' AND job_id='".(int)$job_id."'");
				if (mysqli_num_rows($result) > 0)
					return true;
				else
					return false;
			}
			else
			{				
				return false;
			}
		}
	}
}



/**
 * Returns job success rating
 * @param	$job_id			Job ID
 * @param	$show_bar		Show/Hide progress bar
 * @return	string			job suceess rating
*/

if (!function_exists('GetJobSuccess')) {
	function GetJobSuccess($job_id, $show_bar = 0)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' AND status<>'pending'");
		$submitted_count = mysqli_num_rows($result);

		if ($submitted_count > 0)
		{
			$confirmed_row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE job_id='".(int)$job_id."' AND status='confirmed'"));
			$confirmed_count = $confirmed_row['total'];

			$succees_rating = round(($confirmed_count * 100)/$submitted_count);

			if ($succees_rating < 30)
				$rating_color = "#FF2600";
			elseif ($succees_rating >= 30 && $succees_rating < 50)
				$rating_color = "#FC930A";
			elseif ($succees_rating >= 50 && $succees_rating < 70)
				$rating_color = "#15BBF2";
			elseif ($succees_rating >= 70)
				$rating_color = "#6dba09";
			else
				$rating_color = "#000000";

			$succees_rating .= "%";

			if ($show_bar == 1)
			{
				$succees_rating = $succees_rating."<div class='progress-bar'><div class='progress-value' style='background-color: ".$rating_color."; width: ".$succees_rating.";'></div></div>";
			}
			else
			{
				$succees_rating = "<span style='color:".$rating_color."'>".$succees_rating."</span>";
			}

			return $succees_rating;
		}
		else
		{
			if ($show_bar == 1)
			{
				return "---<br/>"; //N/A
				//$succees_rating = "<div class='progress-bar'><div class='progress-value' style='background-color: #FFFFFF; width: 100%;'></div></div>";
				//return $succees_rating;
			}
			else
			{
				return "---"; //N/A
			}
		}
	}
}


/**
 * Returns total of hidden jobs
*/

if (!function_exists('GetHiddenJobsTotal')) {
	function GetHiddenJobsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE DATE_ADD(last_activity, INTERVAL 20 minute) > NOW()");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Check if user sending SIGN UP bonus
 * @param	$user_id		user ID
 * @return	boolean			false / true
*/

if (!function_exists('isSignUpBonus')) {
	function isSignUpBonus($user_id)
	{
		$row = mysqli_fetch_array(smart_mysql_query("SELECT balance FROM jobfia_users WHERE user_id='".(int)$user_id."' LIMIT 1"));
		$balance = $row['balance'];

		$row2 = mysqli_fetch_array(smart_mysql_query("SELECT SUM(amount) as total FROM jobfia_transactions WHERE user_id='".(int)$user_id."' AND payment_type<>'withdraw' and status='paid'"));
		$total_transactions = $row2['total'];

		$jobs_complete = GetWorkerPaidJobs($user_id);

		if (($jobs_complete == 0) && ($balance <= SIGNUP_BONUS) && ($total_transactions < SIGNUP_BONUS))
			return true;
		else
			return false;
	}
}


/**
 * Returns total of new member's messages from administrator
 * @return	integer		total of new messages for member from administrator
*/

if (!function_exists('GetMemberMessagesTotal')) {
	function GetMemberMessagesTotal()
	{
		$userid	= $_SESSION['userid'];
		
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_messages_answers WHERE user_id='".(int)$userid."' AND is_admin='1' AND viewed='0'");
		$row = mysqli_fetch_array($result);

		if ($row['total'] == 0)
		{
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_messages WHERE user_id='".(int)$userid."' AND is_admin='1' AND viewed='0'");
			$row = mysqli_fetch_array($result);
		}
		return (int)$row['total'];
	}
}



/**
 * Returns total of new messages from users
 * @return	integer			total of new messages
*/

if (!function_exists('GetMessagesTotal')) {
	function GetMessagesTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_messages WHERE is_admin='0' AND viewed='0'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



/**
 * Returns total of inactive new users
 * @return	string	total
*/

if (!function_exists('GetInactiveUsersTotal')) {
	function GetInactiveUsersTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE status='inactive' AND login_count='0'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}



/**
 * Checks if user is exist
 * @return	boolen	true/false
*/

if (!function_exists('isUserExist')) {
	function isUserExist($userid)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1"); // AND status='active'
		if (mysqli_num_rows($result) > 0)
			return true;
		else
			return false;
	}
}



/**
 * Returns employer info
 * @param	$userid		Employer ID
 * @return	string		employer name
*/

if (!function_exists('GetEmployerInfo')) {
	function GetEmployerInfo($userid, $username = 0)
	{
		if ($userid == 0)
		{
			return "Admin";
		}
		else
		{
			$query = "SELECT * FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1";
			$result = smart_mysql_query($query);

			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				if ($username == 1)
					return "<a class='user' href='".SITE_URL."user_profile.php?id=".(int)$userid."'>".$row['username']."</a>";
				else
					return "<a class='user' href='".SITE_URL."user_profile.php?id=".(int)$userid."'>".$row['fname']." ".substr($row['lname'], 0, 1).".</a>";
			}
			else
			{
				return "<span class='no_user'>- User not found -</span>";
			}
		}
	}
}


if (!function_exists('GetUserAvatar')) {
	function GetUserAvatar($userid, $width = 0)
	{
		if ($userid == 0)
		{
			//return "no_avatar.png";
			return "<img src='".substr(SITE_URL, 0, -1).AVATARS_URL."no_avatar.png' width='".AVATAR_WIDTH."' height='".AVATAR_HEIGHT."'  style='border-radius: 50%'>";
		}
		else
		{
			$query = "SELECT * FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1";
			$result = smart_mysql_query($query);

			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				if ($width > 0)
					return "<img src='".substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']."' width='".$width."' height='".$width."' style='border-radius: 50%'>";
				else
					return "<img src='".substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']."' width='".AVATAR_WIDTH."' height='".AVATAR_HEIGHT."' class='img-responsive'>";
			}
			else
			{
				//return "no_avatar.png";
				return "<img src='".substr(SITE_URL, 0, -1).AVATARS_URL."no_avatar.png' width='".AVATAR_WIDTH."' height='".AVATAR_HEIGHT."'  style='border-radius: 50%'>";
			}
		}
	}
}



/**
 * Returns worker info
 * @param	$userid		Worker ID
 * @return	string		worker name
*/

if (!function_exists('GetWorkerInfo')) {
	function GetWorkerInfo($userid, $show_type = 0)
	{
		$query = "SELECT * FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			if ($show_type == 1) return "<a class='user' href='".SITE_URL."user_profile.php?id=".(int)$userid."'>".$row['fname']." ".substr($row['lname'], 0, 1).".</a>";
			if ($show_type == 2) return "<a class='user' href='".SITE_URL."user_profile.php?id=".(int)$userid."'>".$row['username']."</a>";
			return "<a class='user' href='".SITE_URL."user_profile.php?id=".(int)$userid."'>".$row['fname']." ".$row['lname']."</a>";
		}
		else
		{
			return "<span class='no_user'>User not found</span>";
		}
	}
}



/**
 * Returns worker rating
 * @param	$userid		Worker ID
 * @return	string		worker rating
*/

if (!function_exists('GetWorkerRating')) {
	function GetWorkerRating($userid, $show_stars = 0)
	{
		//default rating
		$max_rating = "5.0";

		$userid = (int)$userid;

		if (GetWorkerJobsTotal($userid) == 0) if ($show_stars == 1) return "<div class='rating'><div class='cover'></div><div class='progress' style='width: 100%;'></div></div>"; else return "5.0";

		$success_jobs_total = GetWorkerPaidJobs($userid);
		$wrong_jobs_total = GetWorkerDeclinedJobs($userid);
	
		$rating = $max_rating;
		$rating += $success_jobs_total*0.05;
		$rating -= $wrong_jobs_total*0.10;
		$rating = number_format($rating, 2, '.', '');

		if ($rating >= $max_rating)
			$worker_rating = $max_rating;
		else
			$worker_rating = $rating;

		if ($show_stars == 1)
		{
			if ($worker_rating == $max_rating) $worker_rating_stars = "100"; else $worker_rating_stars = $worker_rating*18.8;
			$worker_rating = "<div class='rating'><div class='cover'></div><div class='progress' style='width: ".$worker_rating_stars."%;'></div></div>";
		}

		return $worker_rating;
	}
}



/**
 * Returns worker's skill list
 * @param	$worker_id		Worker ID
 * @return	string			worker's skill list
*/

if (!function_exists('GetWorkerSkills')) {
	function GetWorkerSkills($worker_id)
	{
		$sql_worker_skills = smart_mysql_query("SELECT c.*, s.* FROM jobfia_worker_skills s, jobfia_categories c WHERE s.skill_id=c.category_id AND s.worker_id='".(int)$worker_id."' ORDER BY c.name");
		if (mysqli_num_rows($sql_worker_skills) > 0)
		{
			$skills = "<ul id='worker_skills'>";
			while ($row_worker_skills = mysqli_fetch_array($sql_worker_skills))
			{
				$skills .= "<li><i class='fa fa-check-square-o fa-lg' style='color: #01a650'></i> ".$row_worker_skills['name']."</li>";
			}
			$skills .= "</ul>";
			return $skills;
		}
		else
		{
			return false;
		}
	}
}



/**
 * Returns employer rating
 * @param	$userid		Employer ID
 * @return	string		employer rating
*/

if (!function_exists('GetEmployerRating')) {
	function GetEmployerRating($userid)
	{
		$query = "SELECT COUNT(jobfia_job_tasks.job_task_id) AS total FROM jobfia_jobs jobfia_jobs, jobfia_job_tasks jobfia_job_tasks WHERE jobfia_job_tasks.job_id=jobfia_jobs.job_id  AND jobfia_jobs.user_id='".(int)$userid."' AND jobfia_job_tasks.status='confirmed'";
		$result = smart_mysql_query($query);
		
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			
			$total_tasks = $row['total'];

			$no_rank_image	= "<img src='".SITE_URL."images/no_rank.png' />";
			$rank_image		= "<img src='".SITE_URL."images/rank.png' />";

			if ($total_tasks == 0)
				return $no_rank_image;				//New Member
			elseif ($total_tasks > 0)
				return str_repeat($rank_image, 1);	//Begineer
			elseif ($total_tasks > 1000)
				return str_repeat($rank_image, 2);	//Advanced
			elseif($total_tasks > 5000)
				return str_repeat($rank_image, 3);	//Expert
			elseif($total_tasks > 10000)
				return str_repeat($rank_image, 4);	//Pro
			elseif($total_tasks > 20000)
				return str_repeat($rank_image, 5);	//Very Pro
		}
	}
}




/**
 * Returns member's referrals total
 * @param	$userid		User ID
 * @return	string		member's referrals total
*/

if (!function_exists('GetReferralsTotal')) {
	function GetReferralsTotal($userid)
	{
		$query = "SELECT COUNT(*) AS total FROM jobfia_users WHERE ref_id='".(int)$userid."'";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return $row['total'];
		}
	}
}



if (!function_exists('GetReferralsPendingBonuses')) {
	function GetReferralsPendingBonuses($userid)
	{
		$query = "SELECT SUM(amount) AS total FROM jobfia_transactions WHERE user_id='".(int)$userid."' AND payment_type='friend_bonus' AND status='pending'";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return DisplayMoney($row['total']);
		}
	}
}



if (!function_exists('GetReferralsPaidBonuses')) {
	function GetReferralsPaidBonuses($userid)
	{
		$query = "SELECT SUM(amount) AS total FROM jobfia_transactions WHERE user_id='".(int)$userid."' AND payment_type='friend_bonus' AND status='paid'";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return DisplayMoney($row['total']);
		}
	}
}



/**
 * Returns member's ref link clicks
 * @param	$userid		User ID
 * @return	string		ref link clicks total
*/

if (!function_exists('GetRefClicksTotal')) {
	function GetRefClicksTotal($userid)
	{
		$query = "SELECT ref_clicks AS total FROM jobfia_users WHERE user_id='".(int)$userid."' LIMIT 1";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return $row['total'];
		}
	}
}



/**
 * Saves referral's ID in cookies
 * @param	$ref_id		Referrals's ID
*/

if (!function_exists('setReferral')) {
	function setReferral($ref_id)
	{
		//set up cookie for one month period
		setcookie("referer_id", $ref_id, time()+(60*60*24*30));
	}
}



/**
 * Returns payment type title 
 * @param	$payment_type	payment type name
 * @return	string			payment type title
*/

if (!function_exists('GetPaymentName')) {
	function GetPaymentName($payment_type)
	{
		switch($payment_type)
		{
			case "send_money":		$payment_name = "Send Money"; break;
			case "withdraw":		$payment_name = "Withdrawal"; break;
			case "deposit":			$payment_name = "Deposit"; break;
			case "signup_bonus":	$payment_name = "Signup Bonus"; break;
			case "friend_bonus":	$payment_name = "Refer a Friend Bonus"; break;
			case "account_upgrade":	$payment_name = "Premium Account"; break;
			default:				$payment_name = $payment_type; break;
		}

		return $payment_name;
	}
}


/**
 * Returns total of member's requested money
 * @return	string	total
*/

if (!function_exists('GetRequestsTotal')) {
	function GetRequestsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_transactions WHERE payment_type='withdraw' AND status='request'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


/**
 * Returns total of pending deposits
 * @return	string	total
*/

if (!function_exists('GetPendingDepositsTotal')) {
	function GetPendingDepositsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_transactions WHERE payment_type='deposit' AND status='pending'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


if (!function_exists('well_formed')) {
	function well_formed($str)
	{
		$str = strip_tags($str, "<br><br/>");
		//$str = preg_replace("/[^a-zA-Z0-9_\? (\n|\r\n)]+/", "", $str);
		$str = str_replace("&nbsp;", "", $str);
		//$str = str_replace("&", "&amp;", $str);
		//$str = html_entity_decode($str);
		return $str;
	}
}


/**
 * Updates user activity time
 * @param	$user_id		User ID
*/


if (!function_exists('UpdateUserActivity')) {
	function UpdateUserActivity($user_id)
	{
		// checks if user exist
		$rand = mt_rand(0, 1);
		if ($rand == 1)
		{
			$total = mysqli_num_rows(smart_mysql_query("SELECT user_id FROM jobfia_users WHERE user_id='".(int)$user_id."' AND status='active' LIMIT 1"));
			if ($total == 0)
			{
				header ("Location: logout.php");
				exit();
			}
		}
		smart_mysql_query("UPDATE jobfia_users SET last_activity=NOW() WHERE user_id='".(int)$user_id."' LIMIT 1");
	}
}


/**
 * Checks if user online
 * @param	$user_id		User ID
 * @return	boolean			false / true
*/

if (!function_exists('ifUserOnline')) {
	function ifUserOnline($user_id)
	{
		$result = smart_mysql_query("SELECT * FROM jobfia_users WHERE user_id='".(int)$user_id."' AND DATE_ADD(last_activity, INTERVAL 20 minute) > NOW() LIMIT 1");
		if (mysqli_num_rows($result) > 0)
			return true;
		else
			return false;
	}
}


/**
 * Returns total of online users
*/

if (!function_exists('GetUsersOnline')) {
	function GetUsersOnline()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE DATE_ADD(last_activity, INTERVAL 20 minute) > NOW()");
		$row = mysqli_fetch_array($result);
		return number_format($row['total']);
	}
}


/**
 * Create thumbnail image
 * @param	$source		Image Name
 * @param	$dest		Thumb 'Save As' Name
 * @param	$new_width	Thumb Width
 * @param	$new_height	Thumb Height
*/

function create_thumb($source, $dest, $new_width, $new_height)
{
    $sourcedate = 0;
    $destdate = 0;
    global $convert;

    if (file_exists($dest)) {
       clearstatcache();
       $sourceinfo = stat($source);
       $destinfo = stat($dest);
       $sourcedate = $sourceinfo[10];
       $destdate = $destinfo[10];
    }
    if (!file_exists("$dest") or ($sourcedate > $destdate)) {
       global $ImageTool;
       $imgsize = @GetImageSize($source);
       $width = $imgsize[0];
       $height = $imgsize[1];
	   $type = $imgsize[2];
 
      if ($width > $height) { // If the width is greater than the height it's a horizontal picture
        $xoord = ceil(($width - $height) / 2 );
        $width = $height;      // Then we read a square frame that equals the width
      } else {
        $yoord = ceil(($height - $width) / 2);
        $height = $width;
      }

        $new_im = ImageCreatetruecolor($new_width,$new_height);
		//$im = ImageCreateFromJPEG($source);

		switch($type) {
			case 1: $im = imagecreatefromgif($source); break;
			case 2: $im = imagecreatefromjpeg($source); break;
			case 3: $im = imagecreatefrompng($source); break;
			default: return "JPG, GIF and PNG only. Please try again";
		}

        imagecopyresampled($new_im,$im,0,0,$xoord,$yoord,$new_width,$new_height,$width,$height);
        ImageJPEG($new_im,$dest,90);
    }
}


/**
 * Delete user avatar
 * @param	$user_id	User ID
*/

if (!function_exists('DeleteAvatar')) {
	function DeleteAvatar($user_id)
	{
		$userid = (int)$user_id;
		$row = mysqli_fetch_array(smart_mysql_query("SELECT avatar FROM jobfia_users WHERE user_id='$userid' LIMIT 1"));
		if (file_exists(PUBLIC_HTML_PATH.AVATARS_URL.$row['avatar']) && $row['avatar'] != "" && $row['avatar'] != "no_avatar.png") @unlink(PUBLIC_HTML_PATH.AVATARS_URL.$row['avatar']);
	}
}


if (!function_exists('formatBytes')) {
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
}

if (!function_exists('ifWorker')) {
function ifWorker()
{
	if (isset($_SESSION['usertype']) && ($_SESSION['usertype'] == "worker" || $_SESSION['usertype'] == "both"))
		return true;
	else
		return false;
}
}

function highlight_word($content, $word, $color)
{
	$replace = '<span style="color: '.$color.';">'.$word.'</span>';
	$content = str_replace($word, $replace, $content);

	return $content;
}

function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
  
	//Get file extension
    $exploding = explode(".",$file);
    $ext = end($exploding);
    
    switch($ext){
        case "png":
            $src = imagecreatefrompng($file);
        break;
        case "jpeg":
        case "jpg":
            $src = imagecreatefromjpeg($file);
        break;
        case "gif":
            $src = imagecreatefromgif($file);
        break;
        default:
            $src = imagecreatefromjpeg($file);
        break;
    }  
    
    $dst = imagecreatetruecolor($newwidth, $newheight);
  
	// preserve transparency
	if ($ext == "gif" or $ext == "png"){
	    imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
	    imagealphablending($dst, false);
	    imagesavealpha($dst, true);
	}    
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}


	function country_ip($ip, $show_flag = 0)
	{
		$ip = substr($ip, 0, 15);
		require_once (PUBLIC_HTML_PATH."/inc/ip/geoip.inc");
		
		$gi	= geoip_open(PUBLIC_HTML_PATH."/inc/ip/GeoIP.dat",GEOIP_MEMORY_CACHE);
		$country = geoip_country_code_by_addr($gi, $ip); //us
		geoip_close($gi);

		$country = strtolower($country);

		//return "<img src='/images/flags/".$country.".png' align='absmiddle' />";
		return $country;	
	}//dev

?>