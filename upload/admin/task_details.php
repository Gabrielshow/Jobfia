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
	require_once("./inc/adm_functions.inc.php");


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$job_task_id	= (int)$_REQUEST['id'];
		$job_id			= (int)$_REQUEST['job_id'];
		$pn				= (int)$_REQUEST['pn'];

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_job_tasks WHERE job_task_id='$job_task_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	if (isset($_POST['action']) && $_POST['action'] == "modify")
	{
		unset($errs);
		$errs = array();

		$job_task_id	= (int)getPostParameter('id');;
		$comment		= mysqli_real_escape_string($conn, nl2br(getPostParameter('comment')));
		$status			= mysqli_real_escape_string($conn, getPostParameter('mark_as'));

		if (isset($comment) && $comment != "")
		{		
			smart_mysql_query("UPDATE jobfia_job_tasks SET comment='$comment' WHERE job_task_id='$job_task_id' LIMIT 1");
		}

		if (count($errs) == 0)
		{
			switch($status)
			{
				case "confirm": ConfirmTask($job_task_id); break;
				case "decline": DeclineTask($job_task_id); break;
				case "incomplete": NotCompleteTask($job_task_id); break;
			}

			header("Location: job_tasks.php?msg=updated&id=".$job_id);
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

	$title = "Applicant Details";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<h2><i class="fa fa-check-circle-o"></i> Applicant Details #<?php echo $row['job_task_id']; ?> - <a href="job_details.php?id=<?php echo $row['job_id']; ?>"><?php echo $row["job_title"]; ?></a></h2>

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

			<table width="100%" cellpadding="3" cellspacing="2" border="0" align="center">
			<tr>
				<td height="80" width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo DisplayMoney($row['job_price']); ?></span> <i class="fa fa-money fa-lg"></i> reward</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo GetUsername($row['worker_id'], $username=1); ?></span> <i class="fa fa-user-o fa-lg"></i> worker</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo $row['date_complete']; ?></span> <i class="fa fa-clock-o fa-lg"></i> date/time</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num">
				<?php
					switch ($row['status'])
					{
						case "notcomplete": echo "<span class='label label-default'>".$row['status']."</span>"; break;
						case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> ".$row['status']."</span>"; break;							
						case "declined": echo "<span class='label label-danger'>".$row['status']."</span>"; break;
						case "confirmed": echo "<span class='label label-success'>".$row['status']."</span>"; break;
						default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
					}
				?>				
				</span> status
				</td>
			</tr>
			</table>

			<form action="" method="post">
			<table width="100%" style="background: #fcfcfc" align="center" border="0" cellspacing="0" cellpadding="4">
			<?php if ($row['comment'] != "") { ?>
			<tr>
				<td colspan="2" align="left" valign="top">
					<h3>Comment from employer:</h3>
					<textarea name="comment" cols="55" rows="5" class="form-control" /><?php echo strip_tags($row['comment']); ?></textarea>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" align="left" valign="top"><br><h3 style="margin:0;">Proof submitted:</h3><div style="color:#757575; background:#FFF; border:1px dotted #EEE; padding:10px; margin: 10px 0; min-height: 100px;"><?php echo $row['job_proof']; ?></div>
				<?php if ($row['proof_file'] != "") { ?><h3><i class="fa fa-paperclip"></i> Proof File:</h3> <i class="fa fa-download fa-lg"></i>  <a href="<?php echo substr(SITE_URL, 0, -1).JOBFIA_PROOFS_URL.$row['proof_file']; ?>"><?php echo $row['proof_file']; ?></a><br><span class="badge"><?php echo $row['proof_downloads']; ?></span> downloads <?php if ($row['proof_downloads'] == 0) echo "&nbsp; (not viewed by employer yet)"; ?><?php } ?></td>
			</tr>
			<?php if ($row['status'] != "confirmed") { ?>
			<tr>
				<td width="100" align="left"><b>Mark as:</b></td>
				<td align="left">
						<select name="mark_as">
							<option value="">--------------</option>
							<option value="confirm" style="background:#82ED09;color:#FFF;">Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">Not Complete</option>
							<option value="decline" style="background:#FC2020;color:#FFF;">Not Satisfied</option>
						</select>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" align="left">
					<p style="padding-left: 100px">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="id" value="<?php echo $row['job_task_id']; ?>" />
					<input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>" />
					<input type="hidden" name="action" value="modify" />
					<?php if ($row['status'] != "confirmed") { ?>
						<input type="submit" class="btn btn-success" value="Update Task" />
						<input type="button" class="btn btn-default" name="goback" value="Cancel" onclick="history.go(-1);return false;" />
						</p>
					<?php }else{ ?>
						<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;" /></p>
					<?php } ?>
				</td>
			</tr>
           </table>
		   </form>

	  <?php }else{ ?>
	  	<h2><i class="fa fa-check-circle-o"></i> Applicant Details</h2>
			<div class="alert alert-info text-center">Sorry, submitted task not found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>