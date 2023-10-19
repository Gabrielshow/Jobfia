<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	require_once("inc/config.inc.php");

	$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE status='pending' AND date_sub(curdate(), interval ".AUTO_COMFIRM_DAYS." day) >= added");
	$total = mysqli_num_rows($result);

	if ($total > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$task_id = (int)$row['job_task_id'];
			ConfirmTask($task_id);
		}
	}

	smart_mysql_query("DELETE FROM jobfia_job_tasks WHERE status='notcomplete' AND date_sub(curdate(), interval ".DEL_NOTCOMPLETE_DAYS." day) >= added");

	//delete job invitations after 7 days
	smart_mysql_query("DELETE FROM jobfia_worker_invites WHERE date_sub(curdate(), interval 7 day) >= invited");

	// auto run paused jobs for daily tasks limit
	smart_mysql_query("UPDATE jobfia_jobs SET status='active' WHERE status='inactive' and paused!='0000-00-00 00:00:00' AND DAY(paused)>DAY(NOW())");

	// confirm refer a friend bonuses
	if (REFER_FRIEND_BONUS > 0) 
	{
		$check_bonus = smart_mysql_query("SELECT * FROM jobfia_transactions WHERE payment_type='friend_bonus' AND status='pending' AND user_id IN (SELECT user_id FROM jobfia_users WHERE balance>='".REFER_FRIEND_BONUS_AFTER."')");
		if (mysqli_num_rows($check_bonus) > 0)
		{
			while ($bonus_row = mysqli_fetch_array($check_bonus))
			{
				smart_mysql_query("UPDATE jobfia_transactions SET status='paid' WHERE transaction_id='".$bonus_row['transaction_id']."' LIMIT 1");
				smart_mysql_query("UPDATE jobfia_users SET balance=balance+".$bonus_row['amount']." WHERE user_id='".(int)$bonus_row['recipient_id']."' LIMIT 1");
				//send email//dev
			}
		}
	}

?>