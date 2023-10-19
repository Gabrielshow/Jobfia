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


	if (isset($_POST['action']) && $_POST['action'] == "update")
	{
		unset($errs);
		$errs = array();

		$job_task_id	= (int)getPostParameter('wid');
		$job_proof		= mysqli_real_escape_string($conn, nl2br(getPostParameter('job_proof')));

		if (!(isset($job_proof) && $job_proof != ""))
		{
			$errs[] = "Please enter job proof";
		}

		if (count($errs) == 0)
		{
			$i_query = "UPDATE jobfia_job_tasks SET job_proof='$job_proof', status='pending', updated=NOW() WHERE job_task_id='$job_task_id' AND worker_id='$userid' AND status='notcomplete' LIMIT 1";
			if (smart_mysql_query($i_query))
			{
				header("Location: jobs_finished.php?msg=updated");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_task_id = (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete, DATE_FORMAT(updated, '%d %b %Y %h:%i %p') AS date_updated FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' AND worker_id='$userid' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Finished Job Details";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div style="background: #fcfcfc; padding: 10px">
		
    <ol class="breadcrumb breadcrumb-arrow">
		<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
		<li><a href="<?php echo SITE_URL; ?>jobs_finished.php">Finished Jobs</a></li>
		<li class="active"><span>Job Details</span></li>
	</ol>
	
	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<h2 style="border-bottom: 1px solid #eee"><i class="fa fa-check-circle-o" style="color: #01a650"></i> Finished Job Details</h2>

	<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<?php if (isset($allerrors)) { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>
			
			<div style="text-align: right"><a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>" target="_blank">open job details <i class="fa fa-external-link"></i></a></div>

			<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>"><h3 style="color: #777"><?php echo $row["job_title"]; ?></h3></a>
			<hr>
			
			<div class="row">
				<div class="col-sm-4 text-center" style="border-right: 1px solid #eee"><h3><?php echo $row['date_complete']; ?></h3> <i class="fa fa-clock-o fa-lg"></i> date/time</div>
				<div class="col-sm-4 text-center" style="border-right: 1px solid #eee"><center><h3 style="color: #01a650"><?php echo DisplayMoney($row['job_price']); ?></h3> <i class="fa fa-money fa-lg"></i> payment</center></div>
				<div class="col-sm-4 text-center">
					<center><span class="count">
					<?php
						switch ($row['status'])
						{
							case "notcomplete": echo "<span class='label label-default'><i class='fa fa-exclamation-triangle'></i> not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'><i class='fa fa-ban'></i> declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-money'></i> paid</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}
					?>
					</span>
					status
					</center>
				</div>
			</div>
			<hr>

			<form action="" method="post">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php if ($row['comment'] != "") { ?>
			<tr>
				<td align="left">
					<h4><i class="fa fa-comments-o"></i> Comment from employer:</h4>
					<div class="comment"><?php echo $row['comment']; ?></div><br>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td align="left">
					<h4>Proof Submitted:</h4>
					<?php if ($row['status'] != "notcomplete") { ?>
						<div style="background: #F5F5F5; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px; min-height: 120px;"><?php echo strip_tags($row['job_proof']); ?></div>
					<?php }else{ ?>
						<textarea name="job_proof" rows="8" class="form-control"><?php echo strip_tags($row['job_proof']); ?></textarea>
					<?php } ?>
					<?php if ($row['proof_file'] != "") { ?><br/><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i>  Proof File: <a target="_blank" href="<?php echo SITE_URL; ?>download.php?id=<?php echo $row['job_task_id']; //echo substr(SITE_URL,0,-1).JOBFIA_PROOFS_URL.<?php echo $row['proof_file'] ?>"><?php echo substr($row['proof_file'], -20); ?></a><?php } ?>
				</td>
			</tr>
			<?php if ($row['updated'] != '0000-00-00 00:00:00') { ?>
			<tr>
				<td align="left">
					<b>Updated:</b> <?php echo $row['date_updated']; ?>
				</td>
			</tr>
			<?php } ?>
			<?php if ($row['status'] == "notcomplete") { ?>
			<tr>
				<td align="left">
					<input type="hidden" name="wid" value="<?php echo $row['job_task_id']; ?>" />
					<input type="hidden" name="action" value="update" />
					<input type="submit" class="btn btn-success btn-lg" value="Update" />
				</td>
			</tr>
			<?php } ?>
           </table>
		   </form>

		   <hr>
		   <p align="center">
			   <a class="btn btn-default" href="<?php echo SITE_URL; ?>jobs_finished.php"><i class="fa fa-angle-left"></i> Go to finished jobs</a>
			   	<?php if ($row['status'] == "pending" && CANCEL_APPLICANTS == 1) { ?>
					&nbsp;&nbsp;<a class="btn btn-danger" href="<?php echo SITE_URL; ?>jobs_finished.php?id=<?php echo $row['job_task_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #fff"></i> Delete Task</a>
				<?php } ?>
		   </p>

	<?php }else{ ?>
			<div class="alert alert-info text-center">
				<p>Sorry, your finished job's task not found. <a class="btn btn-info" href="<?php echo SITE_URL; ?>jobs_finished.php">My finished jobs &#155;</a></p>
			</div>
	<?php } ?>	
			   
	</div>


<?php require_once ("inc/footer.inc.php"); ?>