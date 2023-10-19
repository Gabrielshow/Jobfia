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


	// delete pending tasks
	if (isset($_GET['act']) && $_GET['act'] == "delete" && CANCEL_APPLICANTS == 1)
	{
		$job_task_id = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' AND worker_id='$userid' AND status='pending'");
		header("Location: jobs_finished.php?msg=deleted");
		exit();
	}

	$results_per_page = RESULTS_PER_PAGE;
	$cc = 0;

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;
	
	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_job_tasks WHERE worker_id='$userid' ORDER BY added DESC LIMIT $from, $results_per_page";
	$total_result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE worker_id='$userid' ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Finished Jobs";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
	<!--
    <ol class="breadcrumb breadcrumb-arrow">
		<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
		<li class="active"><span>My Finished Jobs</span></li>
	</ol>
	-->

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<h1 style="border-bottom: 1px solid #eee"><i class="fa fa-check-circle-o" style="color: #01a650"></i> Finished Jobs</h1>

	<?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<?php
					switch ($_GET['msg'])
					{
						case "updated": echo "Tasks have been successfully updated"; break;
						case "deleted": echo "Task have been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<div class="row">
				<div class="col-sm-3 col-xs-6 text-center" style="border-right: 1px solid #eee"><center><span class="count" style="background:#90d7e8;"><?php echo GetWorkerJobsTotal($userid); ?> <i class="fa fa-check-circle fa-lg" style="color: #fff"></i></span> submitted tasks</center></div>
				<div class="col-sm-3 col-xs-6 text-center" style="border-right: 1px solid #eee"><center><span class="count" style="background:#f4d58b;"> <?php echo GetWorkerPendingJobs($userid); ?> <i class="fa fa-clock-o fa-lg" style="color: #fff"></i> </span>waiting for employer's review</div>
				<div class="col-sm-3 col-xs-6 text-center" style="border-right: 1px solid #eee"><center><span class="count" style="background:#8bc993;"><?php echo GetWorkerPaidJobs($userid); ?> <i class="fa fa-money fa-lg" style="color: #fff"></i> </span>well done & paid</div>				
				<div class="col-sm-3 col-xs-6 text-center"><center><span class="count" style="background:#E55757;"><?php echo GetWorkerDeclinedJobs($userid); ?> <i class="fa fa-ban fa-lg" style="color: #fff"></i></span> declined</div>
			</tr>
			</div>
			<hr>

			<div class="table-responsive">
            <table width="100%" align="center" class="table table-striped table-hover" style="border-bottom: 1px solid #f5f5f5" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="3%">&nbsp;</th>
                <th width="35%">Job Title</th>
                <th width="17%">Payment</th>
                <th width="15%">Status</th>
				<th width="18%"><i class=" fa fa-clock-o"></i> Date/Time</th>
				<th width="8%">Actions</th>
              </tr>
			<?php $total_earnings = 0; while ($row = mysqli_fetch_array($result)) { $cc++; if ($row['status'] == "confirmed") $total_earnings += $row['job_price'];  ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
	              <td valign="middle" align="center"><i class="fa fa-briefcase fa-lg"></i></td>
                  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>jobs_finished_details.php?id=<?php echo $row['job_task_id']; ?>">
						<?php if (strlen($row['job_title']) > 75) $job_title = substr($row["job_title"], 0, 70)."..."; else $job_title = $row["job_title"]; echo $job_title; ?>
					</a>
				  </td>
				  <td valign="middle" align="left"><?php echo DisplayMoney($row['job_price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<?php
							switch ($row['status'])
							{
								case "notcomplete": echo "<span class='label label-default'><i class='fa fa-exclamation-triangle'></i> not complete</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;							
								case "declined": echo "<span class='label label-danger'><i class='fa fa-ban'></i> declined</span>"; break;
								case "confirmed": echo "<span class='label label-success'><i class='fa fa-money'></i> paid</span>"; break;
								default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
							}

							if ($row['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row['comment']."\"></i>";
					?>
				  </td>
				  <td valign="middle" align="left"><?php echo $row['date_complete']; ?></td>
				  <td valign="middle" align="left">
						<a href="<?php echo SITE_URL; ?>jobs_finished_details.php?id=<?php echo $row['job_task_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					<?php if ($row['status'] == "declined") { ?>
						<a href="<?php echo SITE_URL; ?>job_report.php?id=<?php echo $row['job_id']; ?>"><i class="fa fa-flag fa-lg jobfia_tooltip" title="Report" style="color: #e77808"></i></a>
					<?php } ?>
					<?php if ($row['status'] == "pending" && CANCEL_APPLICANTS == 1) { ?>
						<a href="<?php echo SITE_URL; ?>jobs_finished.php?id=<?php echo $row['job_task_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #eb4444"></i></a>
					<?php } ?>
				  </td>
                </tr>
			<?php } ?>
                <tr>
	                <td colspan="5" valign="middle" align="right"><h4>All Time Earnings:</h4></td>
	                <td colspan="1" valign="middle" align="right"><h4><?php echo DisplayMoney($total_earnings); ?></h4></td>
                </tr>			
			</table>
			</div>

				<?php echo ShowPagination("job_tasks",$results_per_page,"jobs_finished.php?","WHERE worker_id='$userid'"); ?>

	<?php }else{ ?>
				<div class="alert alert-info text-center">You did not complete job yet. <a class="btn btn-info" href="<?php echo SITE_URL; ?>jobs.php">Find available jobs</a></div>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>