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

		$user_id		= (int)getPostParameter('user_id');
		$report			= mysqli_real_escape_string($conn, nl2br(getPostParameter('report')));

		if (!($report))
		{
			$errs[] = "Please enter your reason";
		}
		else
		{
			if ($user_id == $userid)
			{
				$errs[] = "Sorry, you can not report for yourself";
			}

			$check_query = smart_mysql_query("SELECT * FROM jobfia_reports WHERE reporter_id='$userid' AND user_id='$user_id'");
			if (mysqli_num_rows($check_query) != 0)
			{
				$errs[] = "You have currently reported for this user";
			}
		}

		if (count($errs) == 0)
		{
			$query = "INSERT INTO jobfia_reports SET reporter_id='$userid', user_id='$user_id', report='$report', viewed='0', status='active', added=NOW()";
			$result = smart_mysql_query($query);
		
			header("Location: user_report.php?id=$user_id&msg=1");
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
		$user_id = (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	$query = "SELECT * FROM jobfia_users WHERE user_id='$user_id' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Report User";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<h1><i class="fa fa-flag" style="color: #ca3d3d"></i> Report User</h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
		<div class="alert alert-success">Thank you! Your report has been sent to us.</div>
	<?php }else{ ?>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<h3><i class="fa fa-user-circle"></i> <b style="color: #2791b7"><?php echo $row['username']; ?></b> (<?php echo $row['fname']." ".substr($row['lname'], 0, 1)."."; ?>)</h3>
			<div class="row">
			<div class="col-md-9">
				<form action="" method="post">
				<textarea name="report" rows="8" class="form-control" placeholder="Please describe why you are reporting this user"><?php echo getPostParameter('report'); ?></textarea>
				<input type="hidden" name="user_id" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="action" value="report" /><br/>
				<input type="submit" class="btn btn-success btn-lg" value="Submit" />
				<input type="submit" class="btn btn-default btn-lg" value="Cancel" onclick="history.go(-1);return false;" />
				</form>
			</div>
			</div>

		<?php }else{ ?>
			<div class="alert alert-warning text-center">
				<h3><i class="fa fa-info-circle fa-3x"></i></h3>
				<h3>Sorry, no user found.<h3>
			</div>
			<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>"><i class="fa fa-angle-left"></i> Go Back</a></p>
		<?php } ?>

	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>