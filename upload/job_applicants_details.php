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


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$job_task_id	= (int)$_REQUEST['id'];
		$job_id			= (int)$_REQUEST['job_id'];
		
		// check if viewing job poster //
		$job_result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='$job_id' AND user_id='$userid' LIMIT 1");
		if (mysqli_num_rows($job_result) == 0)
		{
			header ("Location: index.php");
			exit();
		}
		else
		{
			$job_row = mysqli_fetch_array($job_result);
		}
		//////////////////////////////////
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}


	if (isset($_POST['action']) && $_POST['action'] == "modify")
	{
		unset($errs);
		$errs = array();

		$job_task_id	= (int)getPostParameter('wid');;
		$comment		= mysqli_real_escape_string($conn, nl2br(getPostParameter('comment')));
		$status			= mysqli_real_escape_string($conn, getPostParameter('mark_as'));

		if (!($status))
		{
			$errs[] = "Please select status";
		}

		if (count($errs) == 0)
		{
			smart_mysql_query("UPDATE jobfia_job_tasks SET comment='$comment' WHERE job_task_id='$job_task_id' AND status<>'confirmed' LIMIT 1");

			switch($status)
			{
				case "confirm": ConfirmTask($job_task_id); break;
				case "decline": DeclineTask($job_task_id); break;
				case "incomplete": NotCompleteTask($job_task_id); break;
			}

			header("Location: job_applicants.php?msg=updated&id=".$job_id);
			exit();
		}
		else
		{
			foreach ($errs as $errorname)
			{
				$errormsg .= "&#155; ".$errorname."<br/>\n";
			}
		}
	}


	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete, DATE_FORMAT(updated, '%d %b %Y %h:%i %p') AS date_updated FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' AND job_id='$job_id' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Task Details";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div style="background: #fcfcfc; padding: 10px">

	    <ol class="breadcrumb breadcrumb-arrow">
			<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
			<li><a href="<?php echo SITE_URL; ?>myjobs.php">My Jobs</a></li>
			<li class="active"><span>Task Details</span></li>
		</ol>
		
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>


	<?php

		if ($total > 0) { $row = mysqli_fetch_array($result);

	?>
					
			<div class="row">
				<div class="col-sm-3 text-center" style="border-right: 1px solid #eee"><h3><?php echo $row['date_complete']; ?></h3>date &amp; time</div>				
				<div class="col-sm-3 text-center" style="border-right: 1px solid #eee">
					<h3>
						<?php echo GetUserAvatar($row['worker_id'], $width= 33); ?>
						<?php echo GetWorkerInfo($row['worker_id'], $username = 2); ?>
						<?php if (ifUserOnline($row['worker_id'])) { ?><br><i class="fa fa-user-circle Blink jobfia_tooltip" title="user is online" aria-hidden="true" style="color: #64cb54"> online</i><?php } ?>
					</h3>
						<center><?php echo GetWorkerRating($row['worker_id'], $show_stars = 1); ?></center>
						<?php if (ispremium($row['worker_id'])) { ?><span class="premium"><i class="fa fa-user"></i> premium </span> <?php } ?>
						worker
				</div>
				<div class="col-sm-3 text-center" style="border-right: 1px solid #eee"><center><span class="count" style="background:#fcfcfc; color: #5cb85c"><?php echo DisplayMoney($row['job_price']); ?></span> reward</center></div>
				<div class="col-sm-3 text-center">
					<center><span class="count">
					<?php
						switch ($row['status'])
						{
							case "notcomplete": echo "<span class='label label-default'>not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
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
			

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>
			
			<form action="" method="post">
			<table style="background: #fcfcfc" width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td width="150" align="left" valign="middle"><i class="fa fa-briefcase"></i> Job Title:</td>
				<td align="left" valign="middle"><a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $row['job_id']; ?>"><b><?php echo $row["job_title"]; ?></b></a></td>
			</tr>
			<tr>
				<td height="40" nowrap="nowrap" align="left" valign="middle">Worker:</td>
				<td align="left" valign="middle"><i class="fa fa-user-circle"></i> <?php echo GetWorkerInfo($row['worker_id'], $username = 2); ?> ( <?php echo GetWorkerInfo($row['worker_id']); ?> )</td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="left" valign="middle">Proof submitted:</td>
				<td align="left" valign="top">
					<div style="background: #FFF; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px; min-height: 120px;"><?php echo $row['job_proof']; ?></div>
				</td>
			</tr>
			<?php if ($row['proof_file'] != "") { ?>
			<tr>
				<td height="40" nowrap="nowrap" align="left" valign="middle"><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i> Proof File:</td>
				<td align="left" valign="middle">
					<a href="<?php echo substr(SITE_URL,0,-1).JOBFIA_PROOFS_URL.$row['proof_file']; ?>" target="_blank"> <?php echo $row['proof_file']; ?></a>
				</td>
			</tr>
			<?php } ?>		
			<?php if ($row['updated'] != '0000-00-00 00:00:00') { ?>
			<tr>
				<td nowrap="nowrap" align="left" valign="middle">Proof updated date:</td>
				<td align="left" valign="middle"><?php echo $row['date_updated']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['status'] != "confirmed" || $row['comment'] != "") { ?>
			<tr>
				<td nowrap="nowrap" align="left" valign="top">Comment to worker:<br><small>(optional)</small></td>
				<td align="left" valign="top">
					<?php if ($row['comment'] != "") { ?>
						<div class="comment"><?php echo strip_tags($row['comment']); ?></div>
					<?php }elseif($row['status'] != "confirmed") { ?>
						<textarea name="comment" cols="45" rows="5" class="form-control" /><?php echo getPostParameter('comment'); ?></textarea>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<?php if ($row['status'] != "confirmed") { ?>
			<tr>
				<td nowrap="nowrap" align="left" valign="middle">Mark as:</td>
				<td align="left" valign="middle">
						<select name="mark_as" class="form-control">
							<option value="">--------------</option>
							<option value="confirm" style="background:#82ED09;color:#FFF;">Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">Not Complete</option>
							<option value="decline" style="background:#FC2020;color:#FFF;">Not Satisfied</option>
						</select>
				</td>
			</tr>
			<tr>
				<td align="right" valign="top">&nbsp;</td>
				<td align="left" valign="top">
					<br>
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="wid" value="<?php echo $row['job_task_id']; ?>" />
						<input type="hidden" name="id" value="<?php echo $row['job_id']; ?>" />
						<input type="hidden" name="action" value="modify" />
						<input type="submit" class="btn btn-success btn-lg" value="Update Task" />
				</td>
			</tr>
			<?php } ?>
           </table>
		   </form>

		   <hr>
		  <p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $row['job_id']; ?>"><i class="fa fa-chevron-left"></i> Go to job tasks</a></p>

	<?php }else{ ?>
		<div class="alert alert-info">
			<p>Sorry, task not found.</p>
			<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-chevron-left"></i> Go Back</a></p>
		</div>
	<?php } ?>
	
		 </div>


<?php require_once ("inc/footer.inc.php"); ?>