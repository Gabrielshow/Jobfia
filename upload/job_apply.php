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

	if (PIN_VERIFICATION == 1 && !isPinVerified($userid))
	{
		header ("Location: index.php");
		exit();
	}

	if (isset($_POST['action']) && $_POST['action'] == "job_complete")
	{
		unset($errs);
		$errs = array();

		$job_id		= (int)getPostParameter('job_id');
		$job_proof	= mysqli_real_escape_string($conn, nl2br(getPostParameter('job_proof')));
		$worker_ip	= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!(isset($job_proof) && $job_proof != ""))
		{
			$errs[] = "Please enter required proof";
		}
		else
		{
			$check_job = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE $where job_id='$job_id' AND status='active' LIMIT 1");
			if (mysqli_num_rows($check_job) == 0)
			{
				//job not found
				header ("Location: index.php");
				exit();
			}
			else
			{
				$job_row = mysqli_fetch_array($check_job);
			}

			if (!isAvaliableJob($job_id))
			{
				$errs[] = "Sorry, no available positions for this job at this time.";
			}

			if (isCompleteJob($job_id, $userid) && $job_row['few_times'] != 1)
			{
				$errs[] = "You can complete this job once. <a href='".SITE_URL."jobs.php'>Find other jobs</a>";
			}

			if (isOwner($job_id, $userid))
			{
				$errs[] = "Sorry, you can not apply to your job.";
			}

			if ($job_row['premium'] == 1 && !isPremium($userid))
			{
				$errs[] = "This job can accept only premium workers. Please <a href='".SITE_URL."upgrade_account.php'>upgrade your account</a> to premium.";
			}

			if ($job_row['is_proof_file'] == 1 && !$_FILES['proof_file']['tmp_name'])
			{
				$errs[] = "Please upload proof file";
			}

			if (GetWorkerRating($userid) < MIN_WORKER_RATING)
			{
				$errs[] = "Sorry, you can not apply to job, because your rating is too low.";
			}

			if (DAILY_APPLICATIONS > 0)
			{
				$arow = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) as total FROM jobfia_job_tasks WHERE worker_id='".(int)$userid."' GROUP BY job_id"));
				if (DAILY_APPLICATIONS <= $arow['total'])
				{
					$errs[] = "Sorry, you can not apply to job. Daily tasks limit ".DAILY_APPLICATIONS;
				}
			}
		}

		if (count($errs) == 0)
		{
			if ($_FILES['proof_file']['tmp_name'])
			{
				if (is_uploaded_file($_FILES['proof_file']['tmp_name']))
				{
					list($width, $height, $type) = getimagesize($_FILES['proof_file']['tmp_name']);

					if ($_FILES['proof_file']['size'][$key] > PROOF_MAX_SIZE)
					{
						$errs[] = "The $img_id image file size is too big. It exceeds 2Mb.";
					}
					elseif (preg_match('/\\.(gif|jpg|png|jpeg)$/i', $_FILES['proof_file']['name']) != 1)
					{
						$errs[] = "Please upload ".strtoupper(str_replace("|", ", .", PROOF_ALLOWED_FILES))." file only";
						unlink($_FILES['proof_file']['tmp_name']);
					}
					else
					{
						$ext				= substr(strrchr($_FILES['proof_file']['name'], "."), 1);
						$jobfia_proof_name	= md5(substr($worker_ip, 0, -5).mt_rand(1,10000).time()).".".$ext;
						$upload_path		= PUBLIC_HTML_PATH.JOBFIA_PROOFS_URL.$jobfia_proof_name;
						
						// upload file
						move_uploaded_file($_FILES['proof_file']['tmp_name'], $upload_path);
						
						$proof_file			= $jobfia_proof_name;
					}
				}
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}


		if (count($errs) == 0)
		{
			$job_row		= mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='$job_id' AND status='active' LIMIT 1"));
			$job_title		= mysqli_real_escape_string($conn, $job_row['title']);
			$job_price		= mysqli_real_escape_string($conn, $job_row['price']);
			$task_status	= "pending";

			// proof vcode verification
			if (PROOF_VCODE == 1)
			{
				if ($job_row['auto_rate'] == 2)
				{
					if (preg_match("/$job_proof/i", $job_row['vcode']))
					{
						header ("Location: job_apply.php?id=$job_id&msg=wrong");
						exit();
					}
				}
				elseif ($job_row['auto_rate'] == 3)
				{
					if (preg_match("/$job_proof/i", $job_row['vcode']))
					{
						$task_status = "confirmed";
						$ctasks_sql = ",satisfied_tasks=satisfied_tasks+1";
					}
					else
					{
						header ("Location: job_apply.php?id=$job_id&msg=wrong");
						exit();
					}
				}
			}

			smart_mysql_query("INSERT INTO jobfia_job_tasks SET worker_id='$userid', job_id='$job_id', job_title='$job_title', job_price='$job_price', job_proof='$job_proof', proof_file='$proof_file', country_code='".mysqli_real_escape_string($conn, country_ip($$worker_ip))."', worker_ip='$worker_ip', status='$task_status', added=NOW()");
			smart_mysql_query("UPDATE jobfia_users SET worker='1' $ctasks_sql WHERE user_id='$userid' LIMIT 1");

			if (!isAvaliableJob($job_id))
			{
				// all job positions filled, complete job
				smart_mysql_query("UPDATE jobfia_jobs SET status='complete' WHERE job_id='$job_id' AND status='active' LIMIT 1");

				// send notification to employer
				SendJobNotification($job_id);
			}
			else
			{
				// send new task notification to employer
				SendTaskNotification($job_id);
			}

			if (CheckDailyLimit($job_id))
			{
				smart_mysql_query("UPDATE jobfia_jobs SET status='inactive', paused=NOW() WHERE job_id='$job_id' AND status='active' LIMIT 1");
			}

			if ($task_status == "confirmed") $work_status = "paid"; else $work_status = "pending";

			header("Location: job_complete.php?status=$work_status");
			exit();
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
		$job_id = (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	// hide jobs for user's from other countries
	/*
		if (!CheckTargeting($job_id, $_SESSION['country']))
	{
		header ("Location: index.php");
		exit();
	}*/ //dev

	$DAILY_TASKS = 0;
	
	if (isPremium($userid) && PREMIUM_DAILY_APPLICATIONS > 0) 
		$DAILY_TASKS = PREMIUM_DAILY_APPLICATIONS;
	else if (DAILY_APPLICATIONS > 0)
		$DAILY_TASKS = DAILY_APPLICATIONS;		

	$query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE $where job_id='$job_id' AND job_type='job' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	if (isOwner($job_id, $userid))
	{
		header ("Location: index.php");
		exit();
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Apply to Job";

	require_once ("inc/header.inc.php");

?>


<section class="job-bg ad-details-page">
		<div class="container">
			<!-- 
			<div class="breadcrumb-section">
				<ol class="breadcrumb">
					<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
					<li><a href="<?php echo SITE_URL; ?>jobs.php">Jobs</a></li>
					<li>Apply to Job</li>
				</ol>						
				<h2 class="title">Apply to Job</h2>
			</div>-->

			<div class="job-postdetails">
				<div class="row">	
					<div class="col-md-12">
						<div class="section postdetails">


	<h1 style="border-bottom: 1px solid #f5f5f5"><i class="fa fa-briefcase" style="color: #01a650"></i>  Apply to Job</h1>

	<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>
	
			<h2><?php echo $row['title']; ?></h2>

			<?php if (isset($allerrors)) { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>

			<?php if ($row['premium'] == 1 && !isPremium($userid)) { $hide_job_details = 1; ?>
			<div class="alert alert-warning">
				<b>This job can accept only <span style="color: #F49C04"><i class="fa fa-user"></i> premium</span> workers</b>.
				Please <a href="<?php echo SITE_URL; ?>upgrade_account.php">upgrade your account</a> to premium.
			</div>
			<?php } ?>

			<div class="alert alert-info alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<b>PLEASE NOTE!</b><br>
				<i class="fa fa-star fa-lg"></i> Your current rating: <span class="label label-info" style="font-size: 15px"><b><?php echo GetWorkerRating($userid); ?></b></span><br/>
				Accepting a job and submitting a false proof will decrease your rating.<br/>
				If your rating goes below <b><?php echo MIN_WORKER_RATING; ?></b> you will not be able to submit tasks anymore.
				<?php if ($DAILY_TASKS > 0) { ?><br/>You can apply for <b><?php echo $DAILY_TASKS; ?></b> jobs per day.<?php } ?>			
			</div>

			<h3>Job Description</h3>
			<p class="description">
			<?php
				$desc = stripslashes($row['description']);
				$desc = str_replace("{WORKER_ID}", $userid, $desc);
				$desc = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '<a href="'.SITE_URL.'out.php?url=$0" target="_blank" class="external" rel="nofollow">$0</a>', $desc);
				echo $desc;
			?>				
			</p>
			
			<!--<h3>Have you finished this job?</h3>-->
			<div class="row">	
			<form action="" method="post" enctype="multipart/form-data">
			<?php if (isset($_GET['msg']) && $_GET['msg'] == "wrong") { ?><div class="alert alert-danger">Your proof is wrong!</div><?php } ?>
			 <div class="col-md-9">
				 <h3>Your Proof</h3>
				 <textarea name="job_proof" cols="100" rows="9" class="form-control" placeholder="<?php echo stripslashes(strip_tags($row['proof'])); ?>" required><?php echo getPostParameter('job_proof'); ?></textarea>
				<?php if (ALLOWS_PROOFS_UPLOAD == 1 && $row['is_proof_file'] == 1) { ?>
					<label><b><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i> Proof file</b>:</label>
					<input name="proof_file" type="file" />
					<small>Files allowed: <?php echo strtoupper(str_replace("|", ", .", PROOF_ALLOWED_FILES)); ?>. Max file size: <?php echo formatBytes(PROOF_MAX_SIZE); ?></small><br><br>
				<?php } ?>
			<p>
				<input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>" />
				<input type="hidden" name="action" value="job_complete" />
				<br><button type="submit" class="btn btn-success btn-lg"><i class="fa fa-check-square-o"></i> Apply to Job</button> &nbsp; <a class="btn btn-default btn-lg" href="<?php echo SITE_URL; ?>jobs.php"><i class="fa fa-times"></i> Cancel</a>
			</p>							 
			</div>
			<div class="col-md-3">
				<div style="margin-top: 60px; padding: 10px 10px 10px 35px; background: #F7F7F7 url('<?php echo SITE_URL; ?>images/arrow-left.png') left center no-repeat; border: 1px dotted #eee; border-radius: 7px;"><?php echo stripslashes($row['proof']); ?></div>
			</div>

			</form>
			</div>

	<?php }else{ ?>
		<h1>Job not found</h1>
		<div class="alert alert-info"><p>Sorry, no job found. <a href="<?php echo SITE_URL; ?>jobs.php">All jobs</a></p></div>
	<?php } ?>
	
	</div>
	</div>
	</div>
	</div>
	</div>
	</section>


<?php require_once ("inc/footer.inc.php"); ?>