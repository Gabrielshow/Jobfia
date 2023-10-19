<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/


if (!function_exists('ApproveJob')) {
	function ApproveJob ($job_id)
	{
		$job_id = (int)$job_id;
		smart_mysql_query("UPDATE jobfia_jobs SET status='active', reviewed=NOW() WHERE job_id='$job_id' AND status='pending' LIMIT 1");

		// confirm worker's job invitation //
		smart_mysql_query("UPDATE jobfia_worker_invites SET status='active' WHERE job_id='$job_id' AND status='pending'");
		
		// send email to invited workers //
		SendJobInvitations($job_id);

		// deduc job approval fee and other fees from employer's account balance //
		$row = mysqli_fetch_array(smart_mysql_query("SELECT jobs.*, users.*, DATE_FORMAT(users.created, '%M %e, %Y') AS signup_date, DATE_FORMAT(users.last_login, '%M %e, %Y') AS last_login_date FROM jobfia_jobs jobs LEFT JOIN jobfia_users users ON jobs.user_id=users.user_id WHERE jobs.job_id='$job_id' LIMIT 1"));
	
		if (is_numeric(JOB_APPROVAL_FEE) && JOB_APPROVAL_FEE > 0)
			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".JOB_APPROVAL_FEE." WHERE user_id='".(int)$row['user_id']."' LIMIT 1");
	
		if ($row['featured'] == 1 && is_numeric(FEATURED_JOB_FEE) && FEATURED_JOB_FEE > 0)
			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".FEATURED_JOB_FEE." WHERE user_id='".(int)$row['user_id']."' LIMIT 1");

		// send email to members
		SendNewJobNotification($job_id);
		
		SendJobInvitations($job_id);

		//send email to employer
		if ($row['user_id'] > 0)
		{
			$etemplate = GetEmailTemplate('job_approved');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];
			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$to_email = $row['fname'].' '.$row['lname'].' <'.$row['email'].'>';
			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
		////////////////////////////////////////////////////////////////////////

		header("Location: jobs.php?msg=approved");
		exit();
	}
}


if (!function_exists('DeclineJob')) {
	function DeclineJob ($job_id)
	{
		$job_id = (int)$job_id;
		smart_mysql_query("UPDATE jobfia_jobs SET status='declined', reviewed=NOW() WHERE job_id='$job_id' AND status='pending' LIMIT 1");

		$row = mysqli_fetch_array(smart_mysql_query("SELECT jobs.*, users.*, DATE_FORMAT(users.created, '%M %e, %Y') AS signup_date, DATE_FORMAT(users.last_login, '%M %e, %Y') AS last_login_date FROM jobfia_jobs jobs LEFT JOIN jobfia_users users ON jobs.user_id=users.user_id WHERE jobs.job_id='$job_id' LIMIT 1"));
		
		//send email to employer
		if ($row['user_id'] > 0)
		{
			$etemplate = GetEmailTemplate('job_declined');
			$esubject = $etemplate['email_subject'];
			$emessage = $etemplate['email_message'];
			$emessage = str_replace("{first_name}", $row['fname'], $emessage);
			$emessage = str_replace("{job_name}", $row['title'], $emessage);
			$to_email = $row['fname'].' '.$row['lname'].' <'.$row['email'].'>';
			SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
		}
		/////////////////////////////////////////////////////////////////////////

		header("Location: jobs.php?msg=updated");
		exit();
	}
}


if (!function_exists('DeleteUser')) {
	function DeleteUser ($user_id)
	{
		$userid = (int)$user_id;
		smart_mysql_query("DELETE FROM jobfia_users WHERE user_id='$userid'");

		$result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE user_id='$userid'");
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				DeleteJob($row['job_id']);
			}
		}

		smart_mysql_query("DELETE FROM jobfia_jobs WHERE user_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_my_workers WHERE employer_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_transactions WHERE user_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_job_tasks WHERE worker_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_worker_skills WHERE worker_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_messages WHERE user_id='$userid'");
		smart_mysql_query("DELETE FROM jobfia_reports WHERE reporter_id='$userid'");
	}
}


if (!function_exists('DeleteCountry')) {
	function DeleteCountry ($country_id)
	{
		$countryid = (int)$country_id;
		smart_mysql_query("DELETE FROM jobfia_countries WHERE country_id='$countryid'");
	}
}


if (!function_exists('DeleteNews')) {
	function DeleteNews ($news_id)
	{
		$newsid = (int)$news_id;
		smart_mysql_query("DELETE FROM jobfia_news WHERE news_id='$newsid'");
	}
}


if (!function_exists('DeletePayment')) {
	function DeletePayment ($payment_id)
	{
		$payment_id = (int)$payment_id;
		smart_mysql_query("DELETE FROM jobfia_transactions WHERE transaction_id='$payment_id'");
	}
}


if (!function_exists('DeleteReport')) {
	function DeleteReport ($report_id)
	{
		$report_id = (int)$report_id;
		smart_mysql_query("DELETE FROM jobfia_reports WHERE report_id='$report_id'");
	}
}


if (!function_exists('DeleteEmailBlacklist')) {
	function DeleteEmailBlacklist ($blacklist_id)
	{
		$blacklist_id = (int)$blacklist_id;
		smart_mysql_query("DELETE FROM jobfia_email_blacklist WHERE blacklist_id='$blacklist_id'");
	}
}


if (!function_exists('DeleteJob')) {
	function DeleteJob ($job_id)
	{
		$job_id = (int)$job_id;

		$row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='$job_id' LIMIT 1"));

		smart_mysql_query("DELETE FROM jobfia_jobs WHERE job_id='$job_id'");
		smart_mysql_query("DELETE FROM jobfia_job_to_category WHERE job_id='$job_id'");
		smart_mysql_query("DELETE FROM jobfia_job_to_country WHERE job_id='$job_id'");
		smart_mysql_query("DELETE FROM jobfia_worker_invites WHERE job_id='$job_id'");
		smart_mysql_query("DELETE FROM jobfia_job_tasks WHERE job_id='$job_id'");
	}
}


if (!function_exists('DeleteMessage')) {
	function DeleteMessage ($message_id)
	{
		$mid = (int)$message_id;
		smart_mysql_query("DELETE FROM jobfia_messages WHERE message_id='$mid'");
		smart_mysql_query("DELETE FROM jobfia_messages_answers WHERE message_id='$mid'");
	}
}


if (!function_exists('GetMessageLastReaply')) {
	function GetMessageLastReaply ($message_id, $username = 0)
	{
		$mid = (int)$message_id;
		$result = smart_mysql_query("SELECT user_id FROM jobfia_messages_answers WHERE message_id='$mid' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return Getusername($row['user_id'], $username);
		}
		else
		{
			return "---";
		}
	}
}


if (!function_exists('BlockUnblockUser')) {
	function BlockUnblockUser ($user_id, $unblock=0)
	{
		$userid = (int)$user_id;

		if ($unblock == 1)
			smart_mysql_query("UPDATE jobfia_users SET status='active' WHERE user_id='$userid'");
		else
			smart_mysql_query("UPDATE jobfia_users SET status='inactive' WHERE user_id='$userid'");
	}
}



if (!function_exists('GetUsername')) {
	function GetUsername($user_id, $username = 0)
	{
		$user_id = (int)$user_id;

		if ($user_id == 0)
		{
			return "<span class='user'>admin</span>";
		}
		else
		{
			$result = smart_mysql_query("SELECT fname, lname, username FROM jobfia_users WHERE user_id='$user_id' LIMIT 1");
			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				if ($username == 1)
					return "<a class='user' href='user_details.php?id=$user_id'>".$row['username']."</a>";
				else
					return "<a class='user' href='user_details.php?id=$user_id'>".$row['fname']." ".$row['lname']."</a>";
			}
			else
			{
				return "<span class='no_user'>- User not found -</span>";
			}
		}
	}
}



if (!function_exists('GetRepliesNum')) {
	function GetRepliesNum($message_id)
	{
		$message_id = (int)$message_id;
		$row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(answer_id) as total_replies FROM jobfia_messages_answers WHERE message_id='$message_id'"));
		$total_replies = $row['total_replies'];

		if ($total_replies > 0) 
			return "<font color='#333'><b>".$total_replies."</b></font>";
		else
			return "<font color='#A3A3A3'>".$total_replies."</font>";
	}
}



if (!function_exists('CategoryPrice')) {
	function CategoryPrice ($category_id)
	{
		$result = smart_mysql_query("SELECT min_price FROM jobfia_categories WHERE category_id='$category_id'");
		$row = mysqli_fetch_array($result);
		return DisplayMoney($row['min_price']);
	}
}



if (!function_exists('GetCategoriesTotal')) {
	function GetCategoriesTotal() {
		$result = smart_mysql_query("SELECT COUNT(category_id) as total FROM jobfia_categories");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}



if (!function_exists('CategoriesDropDown')) {
	function CategoriesDropDown ($parent_id, $sep = "", $current = 0, $parent = 0)
	{
		$result = smart_mysql_query("SELECT name, category_id FROM jobfia_categories WHERE category_id<>'$current' AND parent_id='$parent_id' ORDER BY name");
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$category_id = $row['category_id'];
				$category_name = $row['name'];
				if ($parent > 0 && $category_id == $parent) $selected = " selected=\"selected\""; else $selected = "";
				echo "<option value=\"".$category_id."\"".$selected.">".$sep.$category_name."</option>\n";
				CategoriesDropDown($category_id, $sep.$category_name." &gt; ", $current, $parent);
			}
		}
	}
}



if (!function_exists('CategoriesList')) {
	function CategoriesList ($parent_id, $sep = "")
	{
		static $allcategories;
		$result = smart_mysql_query("SELECT name, category_id FROM jobfia_categories WHERE parent_id='$parent_id' ORDER BY name");
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				$category_id = $row['category_id'];
				$category_name = $row['name'];
				$allcategories[$category_id] = $sep.$category_name;
				CategoriesList($category_id, $sep.$category_name." &gt; ");
			}
		}
		return $allcategories;
	}
}



if (!function_exists('GetCountryUsers')) {
	function GetCountryUsers ($country_id, $all = 0)
	{
		if ($all == 1)
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_users WHERE country_id='$country_id'");
		else
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_users WHERE country_id='$country_id' AND status='active'");

		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}



if (!function_exists('GetCountryJobs')) {
	function GetCountryJobs ($country_id, $all = 0)
	{
		if ($all == 1)
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_to_country WHERE country_id='$country_id'");
		else
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_to_country WHERE country_id='$country_id' AND status='active'");

		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}




if (!function_exists('GetJobCategories')) {
	function GetJobCategories($job_id)
	{
		unset($cat_list);
		unset($job_cats);
		$job_cats = array();

		$sql_job_cats = smart_mysql_query("SELECT category_id FROM jobfia_job_to_category WHERE job_id='$job_id'");

		if (mysqli_num_rows($sql_job_cats) > 0)
		{
			while ($row_job_cats = mysqli_fetch_array($sql_job_cats))
			{
				$job_cats[] = $row_job_cats['category_id'];
			}

			$categories_list = array();
			$allcategories = array();
			$allcategories = CategoriesList(0);
			
			if (count($allcategories) > 0)
			{
				foreach ($allcategories as $category_id => $category_name)
				{
					if (is_array($job_cats) && in_array($category_id, $job_cats))
					{
						$categories_list[] = $category_name;
					}
				}
	
				foreach ($categories_list as $cat_name)
				{
					$cat_list .= "<span class='category_list'>".$cat_name."</span>";
				}

				return $cat_list;
			}
		}
		else
		{
			return false;
		}
	}
}

?>