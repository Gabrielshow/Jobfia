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

	$results_per_page = RESULTS_PER_PAGE;
	$cc = 0;

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;


	if (isset($_GET['act']) && $_GET['act'] == "delete")
	{
		$invite_id	= (int)$_GET['id'];

		$del_query = "DELETE FROM jobfia_worker_invites WHERE invite_id='$invite_id' AND worker_id='$userid'";
		if (smart_mysql_query($del_query))
		{
			header("Location: jobs_invited.php?msg=deleted");
			exit();
		}
	}

	$query = "SELECT jobfia_worker_invites.*, DATE_FORMAT(jobfia_worker_invites.invited, '%d %b %Y') AS date_invited, jobfia_jobs.* FROM jobfia_worker_invites jobfia_worker_invites, jobfia_jobs jobfia_jobs WHERE jobfia_worker_invites.worker_id='$userid' AND jobfia_worker_invites.job_id=jobfia_jobs.job_id AND jobfia_worker_invites.status='pending' AND jobfia_jobs.job_type='job' AND jobfia_jobs.status='active' ORDER BY jobfia_worker_invites.invited DESC";

	$total_result = smart_mysql_query("SELECT * FROM jobfia_worker_invites WHERE worker_id='$userid' AND status='pending' ORDER BY invited DESC"); //dev
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Job Invitations";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	
?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<h1><i class="fa fa-fire" style="color: #01a650"></i>  Job Invitations <?php echo ($total > 0) ? "<sup class='label label-default'>".$total."</sup>" : ""; ?></h1>
			
			<?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] == "deleted") { ?>
				<div class="alert alert-success alert-dismissible fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<i class="fa fa-check-circle-o fa-lg"></i> Job invitation has been declined
				</div>
			<?php } ?>

			<p>Here you can view jobs you were invited.</p>

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="13%">Invite Date</th>
                <th width="40%">Job Title</th>
				<th width="15%"><i class="fa fa-user-circle"></i> Employer</th>
				<th width="10%">Payment</th>
                <th width="15%">Work Done</th>
				<th width="10%">Action</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="left"><?php echo $row['date_invited']; ?></td>
                  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>">
						<?php if (strlen($row['title']) > 75) $job_title = substr($row["title"], 0, 70)."..."; else $job_title = $row["title"]; echo $job_title; ?>
					</a>
				  </td>
				  <td valign="middle" align="left">
					<?php echo GetEmployerInfo($row['employer_id']); ?>
					<?php if ($row['comment'] != "") { ?><p><i>"<?php echo $row['comment']; ?>"</i></p><?php } ?>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo GetJobWorkersTotal($row['job_id']); ?>/<sup><?php echo $row['positions']; ?></sup></td>
				  <td valign="middle" align="left">
						<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>"><i class="fa fa-handshake-o fa-lg jobfia_tooltip" title="Accept Invitation" style="color: #5cb85c"></i></a>&nbsp;
						<a href="<?php echo SITE_URL; ?>jobs_invited.php?id=<?php echo $row['invite_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Decline Invitation" style="color: #f03e3f"></i></a>
				  </td>
                </tr>
				<?php } ?>
			</table>
			</div>

					<?php echo ShowPagination("worker_invites",$results_per_page,"jobs_invited.php?","WHERE worker_id='$userid' AND status='active'"); ?>			

			<?php }else{ ?>
				<div class="alert alert-info text-center">No jobs invitations at this time.</div>
			<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>