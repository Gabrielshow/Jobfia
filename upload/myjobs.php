<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");
	require_once("inc/pagination.inc.php");
	require_once("inc/csv_functions.inc.php");


	$results_per_page = RESULTS_PER_PAGE;
	$cc = 0;

	$job_id	= (int)$_GET['id'];

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;


	if (isset($_GET['act']) && $_GET['act'] != "" && isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_id = (int)$_GET['id'];
		
		$pagem = "updated";

		switch ($_GET['act'])
		{
			case "run":			$act_query = "UPDATE jobfia_jobs SET status='active' WHERE user_id='$userid' AND job_id='$job_id' AND status='inactive'"; break;
			case "pause":		$act_query = "UPDATE jobfia_jobs SET status='inactive' WHERE user_id='$userid' AND job_id='$job_id' AND status='active'"; break;
			case "delete":		$act_query = "DELETE FROM jobfia_jobs WHERE user_id='$userid' AND job_id='$job_id' AND status='pending'"; $pagem="deleted"; break;
		}

		if (smart_mysql_query($act_query))
		{
			header("Location: myjobs.php?msg=".$pagem);
			exit();
		}
	}

	$jobs_finished_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE job_type='job' AND user_id='$userid' AND status='complete' ORDER BY added DESC";
	$jobs_finished_result = smart_mysql_query($jobs_finished_query);
	$jobs_finished_total = mysqli_num_rows($jobs_finished_result);

	$livejobs_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE user_id='$userid' AND job_type='job' AND status='active' ORDER BY added DESC"; 
	$livejobs_result = smart_mysql_query($livejobs_query);
	$livejobs_total = mysqli_num_rows($livejobs_result);

	$jobs_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE user_id='$userid' AND job_type='job' ORDER BY status ASC, added DESC LIMIT $from, $results_per_page";
	$jobs_result = smart_mysql_query($jobs_query);
	$jobs_total = mysqli_num_rows($jobs_result);

	$total_result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_type='offer' AND user_id='$userid'");
	$total = mysqli_num_rows($total_result);

	if (isset($_GET['export']) && isset($job_id) && is_numeric($job_id) && isOwner($job_id, $userid))
	{
		$csv_result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_id='$job_id' ORDER BY added DESC");
		if (mysqli_num_rows($csv_result) > 0)
		{
			unset($csv_array); $csv_array = array();
			while ($csv_row = mysqli_fetch_array($csv_result))
			{
				$csv_array[] = $csv_row['job_proof'];
			}
			
			$csv_array = array($csv_array);
	
			download_send_headers("job_".$job_id."_tasks_export_".date("Y-m-d").".csv");
			echo array2csv($csv_array);
			die();
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "My Jobs";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<div class="pull-right"><a href="<?php echo SITE_URL; ?>job_create.php" class="btn btn-info">+ Post a Job</a></div>

	<h1 class="brd"><i class="fa fa-list"></i> My Jobs</h1>

		  <?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<i class="fa fa-check-circle-o fa-lg"></i> 
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Job has been submitted"; break;
						case "updated": echo "Jobs has been updated successfully"; break;
						case "deleted": echo "Jobs has been deleted successfully"; break;
					}
				?>
			</div>
		<?php } ?>
		
		<?php if ($jobs_total > 0) { ?>
		<div class="well2">
			<p>Here you can review your created jobs as well as pay/rate the workers performace on your job. <br/>
			Please review and rate all pending tasks for your jobs. After <b><?php echo AUTO_COMFIRM_DAYS; ?> days</b> they will be marked as "satisfied" automatically.</p>
			<?php if (GetPendingTasksTotal(0,$userid) > 0) {?><p>You have <span class="label label-warning"><b><?php echo (GetPendingTasksTotal(0,$userid)); ?></b></span> pending tasks. Please review and rate your pending tasks.</p><?php } ?>
		</div>

		<h3><i class="fa fa-dot-circle-o Blink" aria-hidden="true" style="color: #24c715"></i> Jobs Running <?php /*echo ($livejobs_total > 0) ? "<span class='label label-success'>".$livejobs_total."</span>" : "";*/ ?></h3>

			<?php if ($livejobs_total > 0) { $cc = 0; ?>

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr class="live_jobs">
	            <th width="3%">&nbsp;</th>
				<th width="10%">Date</th>
                <th width="40%">Job Title</th>
				<th width="10%">Payment</th>
                <th width="15%">Job Done</th>
				<th width="15%"><i class="fa fa-hourglass-1 Blink"></i> Pending Tasks</th>
				<th width="15%">Action</th>
              </tr>
				<?php while ($livejobs_row = mysqli_fetch_array($livejobs_result)) { $cc++; ?>
				<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td nowrap="nowrap" valign="middle" align="center"><i class="fa fa-briefcase fa-2x" style="color: #5cb85c"></i></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $livejobs_row['date_added']; ?></td>
                  <td valign="middle" align="left">
					<?php if ($livejobs_row['featured'] == 1) { ?><span class="featured" alt="Featured Job" title="Featured Job"></span><?php } ?>
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $livejobs_row['job_id']; ?>">
						<?php if (strlen($livejobs_row['title']) > 75) $job_title = substr($livejobs_row["title"], 0, 70)."..."; else $job_title = $livejobs_row["title"]; echo $job_title; ?>
					</a>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($livejobs_row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="center">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $livejobs_row['job_id']; ?>">
						<?php echo GetJobWorkersTotal($livejobs_row['job_id']); ?>/<sup><?php echo $livejobs_row['positions']; ?></sup>
						<div class="progress" style="height: 19px">
							  <div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($livejobs_row['job_id'])/$livejobs_row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($livejobs_row['job_id'])/$livejobs_row['positions'])*100); ?>%"><?php echo round((GetJobWorkersTotal($livejobs_row['job_id'])/$livejobs_row['positions'])*100); ?>%</div>
						</div>
					</a>					
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 20px">
					<?php if (GetPendingTasksTotal($livejobs_row['job_id']) > 0) { ?>
						<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $livejobs_row['job_id']; ?>"><span class="label label-warning"><?php echo GetPendingTasksTotal($livejobs_row['job_id']); ?></span></a>
					<?php }else{ ?>
						<span class="label label-default">0</span>
					<?php } ?>
                  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $livejobs_row['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					&nbsp; <a href="<?php echo SITE_URL; ?>myjobs.php?id=<?php echo $livejobs_row['job_id']; ?>&act=pause"><i class="fa fa-pause fa-lg jobfia_tooltip" title="Pause" style="color: #777"></i></a>
				  </td>
                </tr>
				<?php } ?>
			</table>
			</div>

			<?php }else{ ?>
				<div class="well">You currently have no jobs running.</div>
			<?php } ?>
			<bR>

		<?php } ?>


		<?php if ($jobs_finished_total > 0) { $cc = 0; ?>

			<h3><i class="fa fa-check-circle-o"></i> Jobs Finished <?php /*echo ($jobs_finished_total > 0) ? "<span class='label label-info'>".$jobs_finished_total."</span>" : "";*/ ?></h3>

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr class="finished_jobs">
	            <th width="3%">&nbsp;</th>
				<th width="10%">Date</th>
                <th width="40%">Job Title</th>
				<th width="10%">Payment</th>
                <th width="15%">Job Done</th>
				<th width="15%"><i class="fa fa-hourglass-1"></i> Pending Tasks</th>
				<th width="15%">Action</th>
              </tr>
			<?php while ($jobs_finished_row = mysqli_fetch_array($jobs_finished_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td nowrap="nowrap" valign="middle" align="center"><i class="fa fa-briefcase fa-2x" style="color: #bbb"></i></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $jobs_finished_row['date_added']; ?></td>
                  <td valign="middle" align="left">
					<?php if ($jobs_finished_row['featured'] == 1) { ?><span class="featured" alt="Featured Job" title="Featured Job"></span><?php } ?>
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_finished_row['job_id']; ?>">
						<?php if (strlen($jobs_finished_row['title']) > 75) $job_title = substr($jobs_finished_row["title"], 0, 70)."..."; else $job_title = $jobs_finished_row["title"]; echo $job_title; ?>
					</a>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($jobs_finished_row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_finished_row['job_id']; ?>"><?php echo GetJobWorkersTotal($jobs_finished_row['job_id']); ?>/<sup><?php echo $jobs_finished_row['positions']; ?></sup></a>
                  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_finished_row['job_id']; ?>"><span class="label label-warning"><?php echo GetPendingTasksTotal($jobs_finished_row['job_id']); ?></span></a>
				  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_finished_row['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
				  </td>
                </tr>
				<?php } ?>
			</table>
			</div>
			<bR>

		<?php } ?>

			
			<?php if ($jobs_total > 0) { $cc = 0; ?>

			<h3>All Jobs <?php echo ($jobs_total > 0) ? "<span class='label label-default'>".$jobs_total."</span>" : ""; ?></h3>

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="3%">&nbsp;</th>
				<th width="10%">Date</th>
                <th width="40%">Job Title</th>
				<th width="10%">Payment</th>
                <th width="15%">Job Done</th>
				<th width="15%">Status</th>
				<th width="15%">Action</th>
              </tr>
			<?php while ($jobs_row = mysqli_fetch_array($jobs_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td nowrap="nowrap" valign="middle" align="center"><i class="fa fa-briefcase fa-2x" style="color: #bbb"></i></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $jobs_row['date_added']; ?></td>
                  <td valign="middle" align="left">
					<?php if ($jobs_row['featured'] == 1) { ?><span class="featured" alt="Featured Job" title="Featured Job"></span><?php } ?>
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_row['job_id']; ?>">
						<?php if (strlen($jobs_row['title']) > 75) $job_title = substr($jobs_row["title"], 0, 70)."..."; else $job_title = $jobs_row["title"]; echo $job_title; ?>
					</a>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($jobs_row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="center">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_row['job_id']; ?>"><?php echo GetJobWorkersTotal($jobs_row['job_id']); ?>/<sup><?php echo $jobs_row['positions']; ?></sup>
						<div class="progress" style="height: 19px">
							  <div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($jobs_row['job_id'])/$jobs_row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($jobs_row['job_id'])/$jobs_row['positions'])*100); ?>%"><?php echo round((GetJobWorkersTotal($jobs_row['job_id'])/$jobs_row['positions'])*100); ?>%</div>
						</div>					
					</a>
                  </td>
				  <td nowrap valign="middle" align="left">
					<?php
							switch ($jobs_row['status'])
							{
								case "active": echo "<span class='label label-success'>active</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaitng review</span>"; break;	
								case "inactive": echo "<span class='label label-default'>paused</span>"; break;
								case "declined": echo "<span class='label label-danger'>declined</span>"; break;
								case "complete": echo "<span class='label label-success'>complete</span>"; break;
								default: echo "<span class='label label-primary'>".$jobs_row['status']."</span>"; break;
							}

							if ($jobs_row['status'] == "declined" && $jobs_row['reason'] != "")
							{
								echo " <span class=\"jobfia_tooltip\" title=\"".$jobs_row['reason']."\"><i class='fa fa-info-circle'></i></span>";
							}

							if ($jobs_row['status'] == "inactive" && $jobs_row['paused'] != "0000-00-00 00:00:00")
							{
								echo " <span class=\"jobfia_tooltip\" title=\"job was auto paused by daily tasks limit, will run automatically tomorrow\"><i class='fa fa-info-circle'></i></span>";
							}
					?>
				  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $jobs_row['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>					
					<?php if ($jobs_row['status'] == "inactive") { ?>
						&nbsp; <a href="<?php echo SITE_URL; ?>myjobs.php?id=<?php echo $jobs_row['job_id']; ?>&act=run"><i class="fa fa-play fa-lg jobfia_tooltip" title="Start" style="color: #4bb218"></i></a> &nbsp;
					<?php }elseif ($jobs_row['status'] == "pending") { ?>
						<a href="<?php echo SITE_URL; ?>myjobs.php?id=<?php echo $jobs_row['job_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #f03e3f"></i></a>
					<?php } ?>
					<?php if (GetJobWorkersTotal($jobs_row['job_id']) > 0) { ?>
						 &nbsp; <a href="<?php echo SITE_URL; ?>myjobs.php?id=<?php echo $jobs_row['job_id']; ?>&export"><i class="fa fa-file-text-o fa-lg jobfia_tooltip" title="Export tasks to CSV" style="color: #777"></i></a>
					<?php } ?>					
				  </td>
                </tr>
				<?php } ?>
				</table>
			</div>

						<?php echo ShowPagination("jobs",$results_per_page,"myjobs.php?","WHERE job_type='job' AND user_id='$userid'"); ?>			

			<?php }else{ ?>
				<div class="alert alert-info text-center">No jobs have been posted yet. <a href="<?php echo SITE_URL; ?>job_create.php">Post a Job</a>.</div>
			<?php } ?>



<?php require_once ("inc/footer.inc.php"); ?>