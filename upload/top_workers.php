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
	if (SHOW_WORKERS_UNREG != 1)
	{
		require_once("inc/auth.inc.php");
	}	
	require_once("inc/pagination.inc.php");

	$top_workers_query = "SELECT jobfia_job_tasks.*, SUM(jobfia_job_tasks.job_price) as total_earnings, jobfia_users.* FROM jobfia_job_tasks jobfia_job_tasks, jobfia_users jobfia_users WHERE jobfia_job_tasks.worker_id=jobfia_users.user_id AND jobfia_job_tasks.status='confirmed' AND jobfia_job_tasks.added BETWEEN DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') AND LAST_DAY(CURRENT_DATE) AND jobfia_users.worker='1' AND jobfia_users.status='active' GROUP BY jobfia_users.user_id ORDER BY total_earnings DESC LIMIT 100";
	$top_workers_result = smart_mysql_query($top_workers_query);
	$top_workers_total = mysqli_num_rows($top_workers_result);
	$cc = 0;

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Top Workers";
	
	//$single_page = 1;

	require_once ("inc/header.inc.php");

	?>


	<section class="job-bg page job-list-page">
		<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="section">
							

	<div class="pull-right"><a class="label label-primary" href="<?php echo SITE_URL; ?>workers.php" class="btn btn-success"><i class="fa fa-users"></i> See All Workers</a></div>
	<h1><img src="<?php echo SITE_URL; ?>images/icon_top.png" align="absmiddle" /> Top Workers</h1>
	<p>Below you will find the best workers for last 30 days.</p>

	<?php if ($top_workers_total > 0) { ?>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="10%">&nbsp;</th>
                <th width="30%"><i class="fa fa-user-circle-o fa-lg"></i> Worker</th>
				<th width="15%">Avg Job Price</th>
				<th width="25%"><i class="fa fa-check-circle-o fa-lg"></i> Tasks Done</th>
				<th width="20%"><i class="fa fa-money fa-lg"></i> Earned</th>
				<th width="15%"></th>
              </tr>
		<?php while ($top_workers_row = mysqli_fetch_array($top_workers_result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="center">
					<a href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $top_workers_row['user_id']; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$top_workers_row['avatar']; ?>" width="<?php echo AVATAR_WIDTH; ?>" height="<?php echo AVATAR_HEIGHT; ?>" alt="" class="img-responsive" style="border-radius: 50%" border="0" /></a>					
					<?php if ($top_workers_row['premium'] == 1) { ?><span class="premium"><small>premium</small></span><?php } ?>
					<?php if (ifUserOnline($top_workers_row['user_id'])) { ?><br><span class="online"><i class="fa fa-circle Blink"></i> online</span><?php } ?>
					<?php echo GetWorkerRating($top_workers_row['user_id'], $show_stars = 1); ?>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<a class="user_title" href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $top_workers_row['user_id']; ?>"><h3 style="color: #777; margin: 0"><?php echo $top_workers_row['fname']." ".substr($top_workers_row['lname'], 0, 1)."."; ?></h3></a>
					<?php echo GetCountry($top_workers_row['country_id']); ?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo GetWorkerJobPrice($top_workers_row['user_id']); ?></td>
				  <td nowrap="nowrap" valign="middle" align="left">
					<span class="acount total jobfia_tooltip" title="Total"><?php echo GetWorkerJobsTotal($top_workers_row['user_id']); ?></span>
					<span class="acount success jobfia_tooltip" title="Successful"><?php echo GetWorkerPaidJobs($top_workers_row['user_id']); ?></span>
					<span class="acount unsuccess jobfia_tooltip" title="Unsuccessful"><?php echo GetWorkerDeclinedJobs($top_workers_row['user_id']); ?></span>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left"><b><?php echo DisplayMoney($top_workers_row['total_earnings']); ?></b></td>
				  <td nowrap="nowrap" valign="middle" align="left">
					<a class="btn btn-success" href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $top_workers_row['user_id']; ?>"><i class="fa fa-id-card"></i> Profile</a> <a class="btn btn-success" href="<?php echo SITE_URL; ?>hire.php?id=<?php echo $top_workers_row['user_id']; ?>"><i class="fa fa-handshake-o"></i> Hire</a><br/>
					<a class="btn btn-info" style="margin-top: 3px" href="<?php echo SITE_URL; ?>myworkers.php?id=<?php echo $top_workers_row['user_id']; ?>&act=add"><i class="fa fa-plus"></i> Add to My Workers</a>
				  </td>
                </tr>
		<?php } ?>
		</table>
			</div>

	<?php }else{ ?>
		<div class="alert alert-info text-center">
			<i class="fa fa-info-circle fa-lg"></i> No top workers at this time.
		</div>
		<br><br><br><br><br><br><br><br><br><br><br><br><br>
	<?php } ?>
	
	</div>
	</div>
	</div>
	</div>
	</section>


<?php require_once ("inc/footer.inc.php"); ?>