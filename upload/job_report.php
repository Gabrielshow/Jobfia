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


	if (isset($_POST['action']) && $_POST['action'] == "report")
	{
		unset($errs);
		$errs = array();

		$job_id			= (int)getPostParameter('job_id');
		$member_id		= (int)getPostParameter('member_id');
		$report			= mysqli_real_escape_string($conn, nl2br(getPostParameter('report')));

		if (!($report))
		{
			$errs[] = "Please enter your reason";
		}
		else
		{
			if (isOwner($job_id, $userid))
			{
				$errs[] = "Sorry, you can not report your own job";
			}

			$check_query = smart_mysql_query("SELECT * FROM jobfia_reports WHERE reporter_id='$userid' AND job_id='$job_id'");
			if (mysqli_num_rows($check_query) != 0)
			{
				$errs[] = "You have currently reported for this job";
			}
		}


		if (count($errs) == 0)
		{
			$query = "INSERT INTO jobfia_reports SET reporter_id='$userid', job_id='$job_id', report='$report', viewed='0', status='active', added=NOW()";
			$result = smart_mysql_query($query);
		
			header("Location: job_report.php?id=$job_id&msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>\n";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_id = (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	// hide jobs for user's from other countries
	if (HIDE_NOT_COUNTRY_JOBS == 1 && !isOwner($job_id, $userid) && !CheckTargeting($job_id, $_SESSION['country']))
	{
		header ("Location: index.php");
		exit();
	}

	$query = "SELECT * FROM jobfia_jobs WHERE job_id='$job_id' LIMIT 1"; 
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);
	
	$type = "Job";
	if (isset($_GET['offer']) && $_GET['offer'] == 1) $type = "Offer";


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Report ".$type;
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<h1><i class="fa fa-flag" style="color: #ea4156"></i> Report <?php echo $type; ?></h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle-o fa-lg"></i> Thank you! Your report has been sent to us.</div>
		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>"><i class="fa fa-chevron-left"></i> Go home</a></p>
	<?php } ?>

	<?php if (isset($allerrors) && $allerrors != "") { ?>
		<div class="alert alert-danger"><?php echo $allerrors; ?></div>
	<?php } ?>

	<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

		<?php if (!(isset($_GET['msg']) && $_GET['msg'] == 1)) { ?>		
			<h3><?php echo $row['title']; ?></h3>
			<div class="row">
			<div class="col-md-9">					
				<form action="" method="post">
				<textarea name="report" cols="55" rows="7" class="form-control" placeholder="What is wrong with this <?php echo strtolower($type); ?>? Please describe."><?php echo getPostParameter('report'); ?></textarea>
				<input type="hidden" name="job_id" value="<?php echo (int)$row['job_id']; ?>" />
				<input type="hidden" name="action" value="report" /><br/>
				<input type="submit" class="btn btn-success btn-lg" value="Submit" />
				<input type="submit" class="btn btn-default btn-lg" value="Cancel" onclick="history.go(-1);return false;" />
				</form>
			</div>
			</div>
		<?php } ?>

	<?php }else{ ?>
		<div class="alert alert-warning text-center">
			<h3><i class="fa fa-info-circle fa-3x"></i></h3>
			<h3>Sorry, no <?php echo $type; ?> found!</h3>
		</div>
		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>"><i class="fa fa-angle-left"></i> Go Back</a></p>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>