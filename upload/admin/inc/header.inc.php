<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?> | Jobfia Admin Panel</title>
	<meta name="author" content="Jobfia.com" />
    <link rel="stylesheet" href="css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/font-awesome.min.css">
	<link href="css/jobfia.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Ubuntu:400,500,700,300" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Oswald:400" rel="stylesheet">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1">-->
</head>
<body>

<div id="wrapper">

	<div id="header">
		<div id="logo"><a href="http://www.jobfia.com" target="_blank"><img src="images/case.png" width="70" border="0" /> <img src="images/logo.png" border="0" /></a></div>
		<div id="right_header"><i class="fa fa-user-circle"></i> Welcome, <b>Admin</b>! <a href="<?php echo SITE_URL; ?>" target="_blank">View Site</a> | <a class="logout" href="logout.php">Logout</a></div>
	</div>

	<div id="content-wrapper">

		<div id="sidebar">
			<ul>
				<li><a href="index.php"><i class="fa fa-home fa-lg"></i> Home</a></li>
				<li><a href="users.php"><i class="fa fa-users fa-lg"></i> Users</a></li>
			<?php if (GetUserReportsTotal() > 0) { ?>
				<li><a href="user_reports.php"><i class="fa fa-flag fa-lg"></i> Users Reports <span class="new_count" style="background:#ED7474;"><?php echo GetUserReportsTotal(); ?></span></a></li>
			<?php } ?>
				<li><a href="jobs.php"><i class="fa fa-briefcase fa-lg"></i> Jobs <?php if (GetPendingJobsTotal() > 0) { ?><span class="new_count" style="background:#FF9F05;"><?php echo GetPendingJobsTotal(); ?></span><?php } ?></a></li>
				<li><a href="applicants.php"><i class="fa fa-check-circle fa-lg"></i> Jobs Applicants</a></li>
			<?php if (GetJobReportsTotal() > 0) { ?>
				<li><a href="job_reports.php"><i class="fa fa-flag fa-lg"></i> Jobs Reports <span class="new_count" style="background:#ED7474"><?php echo GetJobReportsTotal(); ?></span></a></li>
			<?php } ?>
				<li><a href="offers.php"><i class="fa fa-list fa-lg"></i> Offers <?php if (GetPendingJobsTotal("offer") > 0) { ?><span class="new_count" style="background:#FF9F05;"><?php echo GetPendingJobsTotal("offer"); ?></span><?php } ?></a></li>
				<li><a href="categories.php"><i class="fa fa-list-alt fa-lg"></i> Categories</a></li>
				<li><a href="countries.php"><i class="fa fa-globe fa-lg"></i> Countries</a></li>
				<li><a href="user_money.php"><i class="fa fa-money fa-lg"></i> Credit Users</a></li>
				<li><a href="payments.php"><i class="fa fa-refresh fa-lg"></i> Payments <?php if (GetPendingDepositsTotal() > 0) { ?><span class="new_count" style="background:#FF9F05;"><?php echo GetPendingDepositsTotal(); ?></span><?php } ?></a></li>
				<li><a href="payout_requests.php"><i class="fa fa-download fa-lg"></i> Withdraw Requests <?php if (GetRequestsTotal() > 0) { ?><span class="new_count" style="background:#FF9F05;"><?php echo GetRequestsTotal(); ?></span><?php } ?></a></li>
				<li><a href="pmethods.php"><i class="fa fa-usd fa-lg"></i> Payment Methods</a></li>
				<li><a href="same_ip_users.php"><i class="fa fa-flag fa-lg"></i> Same IP Accounts</a></li>
				<li><a href="messages.php"><i class="fa fa-support fa-lg"></i> Support Tickets <?php if (GetMessagesTotal() > 0) { ?><span class="new_count" style="background:#96d3ff"><?php echo GetMessagesTotal(); ?></span><?php } ?></a></li>
				<li><a href="news.php"><i class="fa fa-newspaper-o fa-lg"></i> News</a></li>
				<li><a href="content.php"><i class="fa fa-file-o fa-lg"></i> Content</a></li>
				<li><a href="etemplates.php"><i class="fa fa-at fa-lg"></i> Email Templates</a></li>
				<li><a href="email2users.php"><i class="fa fa-envelope-o fa-lg"></i> Email Members</a></li>
				<li><a href="settings.php"><i class="fa fa-cogs fa-lg"></i> Settings</a></li>
				<li><a href="logout.php" class="last"><i class="fa fa-sign-out fa-lg"></i> Log Out</a></li>
			</ul>
		</div>

		<div id="content">
