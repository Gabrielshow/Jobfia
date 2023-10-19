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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/adm_functions.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pn			= (int)$_GET['pn'];
		$job_id		= (int)$_GET['id'];

		if (isset($_GET['action']) && $_GET['action'] == "approve") ApproveJob($job_id);
		if (isset($_GET['action']) && $_GET['action'] == "decline") DeclineJob($job_id);

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_added, DATE_FORMAT(updated, '%d %b %Y %h:%i %p') AS date_updated, DATE_FORMAT(reviewed, '%d %b %Y %h:%i %p') AS date_reviewed FROM jobfia_jobs WHERE job_id='$job_id'";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Job Details";
	require_once ("inc/header.inc.php");

?>

	 <?php if ($total > 0) {
	
		 $row = mysqli_fetch_array($result);

	 ?>

		<h2><i class="fa fa-briefcase" style="color: #5cb85c"></i> Job Details - <?php echo $row['title']; ?></h2>


		<table width="100%" style="border: 1px dotted #F5F5F5;" cellpadding="2" cellspacing="2" border="0" align="center">
		<tr>
			<td height="80" width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num" style="color: #5cb85c"><?php echo DisplayMoney($row['price']); ?></span> <i class="fa fa-money fa-lg"></i> Reward</td>
			<?php if ($row['duration'] != "") { ?><td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo ConvertDuration($row['duration']); ?></span> <i class="fa fa-clock-o fa-lg"></i>  Time to complete job</td><?php } ?>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo $row['positions']; ?></span> <i class="fa fa-users fa-lg"></i> Tasks needed <?php if ($row['daily_limit'] > 0) { ?><br/><span class="badge">Daily limit: <b><?php echo $row['daily_limit']; ?></b> tasks per day</span><?php } ?></td>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo GetJobAvailablePositions($row['job_id']); ?></span> <i class="fa fa-users fa-lg" style="color: #42c620"></i> Available positions</td>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><a href="job_tasks.php?id=<?php echo $row['job_id']; ?>"><?php echo GetJobWorkersTotal($row['job_id']); ?> / <sup><?php echo $row['positions']; ?></sup></a></span> Job Done
			
					<div class="progress">
						  <div class="progress-bar progress-bar-striped progress-bar-success progress-bar-animated" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>%"></div>
					</div>
							
				<?php if (GetJobWorkersTotal($row['job_id']) > 0) { ?>
					<span style="color: #FFF; background: #F4C869; padding: 12px 14px; border-radius: 7px;" class="jobfia_tooltip" title="awaiting review"><?php echo GetJobPendingTasks($row['job_id']); ?></span>
					<span style="color: #FFF; background: #92D811; padding: 12px 14px; border-radius: 7px;" class="jobfia_tooltip" title="satisfied"><?php echo GetJobPaidTasks($row['job_id']); ?></span>
					<span style="color: #FFF; background: #F9AA98; padding: 12px 14px; border-radius: 7px;" class="jobfia_tooltip" title="declined"><?php echo GetJobDeclinedTasks($row['job_id']); ?></span>
				<br/><br/><i class="fa fa-bar-chart"></i> Success Rate: <?php echo GetJobSuccess($row['job_id']); ?><br/><br/>
				<?php } ?>
			</td>
		</tr>
		</table>

		<div style="position: absolute; right: 10px; text-align: right; padding: 5px 10px;">
			<?php if ($row['homepage'] == 1) { ?><img src="images/icons/home.png" align="absmiddle" /> <span style="color: #777">homepage job</span><br/><?php } ?>
			<?php if ($row['featured'] == 1) { ?><img src="images/icons/featured.png" align="absmiddle" /> <span style="color: #777">featured job</span><br/><?php } ?>
			<?php if ($row['highlighted'] == 1) { ?><span style="color: #71B4DD">highlighted job</span><br/><?php } ?>
			<?php if ($row['premium'] == 1) { ?><span class="premium" style="color:#777"><img src="images/icons/user_premium.png" align="absmiddle" /> only for premium workers</span><br/><?php } ?>
			<?php if ($row['few_times'] == 1) { ?><i class="fa fa-refresh"></i> <span style="color: #777">worker can complete job few times</span><br/><?php } ?>
		</div>

		
        <table style="background: #FCFCFC;border-bottom: 2px solid #F7F7F7" width="100%" cellpadding="3" cellspacing="6" border="0" align="center">
			<tr>
				<td valign="middle" align="left" class="tb1"><br>Employer:</td>
				<td align="left" valign="middle"><br><?php echo GetUsername($row['user_id'], 1); ?></td>
			</tr>
			<?php if ($row['user_id'] > 0) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Employer's Balance:</td>
				<td align="left" valign="middle"><?php echo GetUserBalance($row['user_id']); ?></td>
			</tr>
			<?php } ?>
			<?php if (GetJobCategory($row['job_id']) != "---") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Category:</td>
				<td align="left" valign="middle"><?php echo GetJobCategories($row['job_id']); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td width="170" valign="middle" align="left" class="tb1">Job available for:</td>
				<td align="left" valign="middle"><?php if ($row['worldwide'] == 1) echo "<i class='fa fa-globe fa-lg' style='color: #5ac0de'></i>  International (All Workers)"; else echo GetJobCountriesList($row['job_id']); ?></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Job Description:</td>
				<td align="left" valign="middle">
					<div style="width: 97%; min-height: 80px; background: #FFFFFF; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px;">
					<?php 
						$desc = stripslashes($row['description']);
						$desc = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '<a href="'.SITE_URL.'out.php?url=$0" target="_blank" class="external" rel="nofollow">$0</a>', $desc);
						echo $desc;
					?>
					</div>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Required Proof:</td>
				<td align="left" valign="middle">
					<div style="width: 97%; min-height: 80px; background: #FFFFFF; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px;">
					<?php echo stripslashes($row['proof']); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-paperclip fa-lg"></i> Required Proof File:</td>
				<td align="left" valign="middle"><?php echo ($row['is_proof_file'] == 1) ? "Yes" : "No"; ?></td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Auto Rate Tasks:</td>
				<td align="left" valign="middle">
					<?php
						switch ($row['auto_rate'])
						{
							case "0": echo "Do not verify or rate"; break;
							case "1": echo "Do not verify or rate"; break;
							case "2": echo "Verify only"; break;
							case "3": echo "Verify + Rate satisfied"; break;
						}
					?>
				</td>
            </tr>
			<?php if ($row['vcode'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Proof Verification Code:</td>
				<td align="left" valign="middle"><span style="background: #F9F9F9; padding: 4px 2px;"><?php echo $row['vcode']; ?></span></td>
			</tr>
			<?php } ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Job Cost:</td>
				<td align="left" valign="middle"><b><?php echo DisplayMoney($row['job_price']); ?></b></td>
			</tr>
			<?php if ($row['tags'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Tags:</td>
				<td align="left" valign="middle"><?php echo $row['tags']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['views'] > 0) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Views:</td>
				<td align="left" valign="middle"><i class="fa fa-eye"></i> <?php echo number_format($row['views']); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Posted:</td>
				<td align="left" valign="middle"><?php echo $row['date_added']; ?></td>
			</tr>
			<?php if ($row['updated'] != "0000-00-00 00:00:00") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Updated:</td>
				<td align="left" valign="middle"><?php echo $row['date_updated']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['reviewed'] != "0000-00-00 00:00:00") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Reviewed:</td>
				<td align="left" valign="middle"><?php echo $row['date_reviewed']; ?></td>
			</tr>
			<?php } ?>
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td align="left" valign="middle">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='label label-success'>open</span>"; break;
							case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
							case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "complete": echo "<span class='label label-success'>complete</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}

						if ($row['status'] == "inactive" && $row['paused'] != "0000-00-00 00:00:00")
						{
							echo "<span class=\"jobfia_tooltip\" title=\"auto paused by daily tasks limit, will run automatically tomorrow\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}
					?>
				</td>
            </tr>
			<?php if ($row['status'] == "declined" && $row['reason'] != "") { ?>
            <tr>
				<td valign="middle" align="left" class="tb1">Decline Reason:</td>
				<td align="left" valign="top"><?php echo $row['reason']; ?></td>
            </tr>
			<?php } ?>
          </table>      

			<p align="center">
			<?php if ($row['status'] == "pending" && $row['reviewed'] == "0000-00-00 00:00:00") { ?> 
				<a class="btn btn-success" style="margin:0; background:#65cf00;" href="job_details.php?id=<?php echo $job_id ?>&pn=<?php echo $pn; ?>&action=approve">Approve Job</a>
				<a class="btn btn-danger" style="margin:0; background:#F91F1B;" href="#" onclick="if (confirm('Are you sure you really want to decline this job?') )location.href='job_details.php?id=<?php echo $job_id; ?>&pn=<?php echo $pn; ?>&action=decline'">Decline</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
				<input type="button" class="btn btn-success" name="edit" value="Edit Job" onClick="javascript:document.location.href='job_edit.php?id=<?php echo $row['job_id']; ?>&page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='jobs.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-danger" style="position: absolute; right: 10px;" name="delete" value="Delete Job" onclick="if (confirm('Are you sure you really want to delete this job?') )location.href='job_delete.php?id=<?php echo $row['job_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
			</p>

	  <?php }else{ ?>
			<h2>Job Details</h2>
			<div class="alert alert-info">Sorry, no job found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>