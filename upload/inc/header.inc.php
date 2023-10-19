<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Jobfia">
	<?php if ($PAGE_DESCRIPTION !="") { ?><meta name="description" content="<?php echo $PAGE_DESCRIPTION; ?>" /><?php } ?>
	<?php if ($PAGE_KEYWORDS !="") { ?><meta name="keywords" content="<?php echo $PAGE_KEYWORDS; ?>" /><?php } ?>

    <title><?php echo $PAGE_TITLE." | ".SITE_TITLE; ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/bootstrap.min.css" >
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>css/icofont.css"> 
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/slidr.css">     
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/main.css">  
	<link id="preset" rel="stylesheet" href="<?php echo SITE_URL; ?>css/preset1.css">	
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/responsive.css">
	
	<?php if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
		<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#appId=<?php echo FACEBOOK_APPID; ?>&amp;xfbml=1"></script>
	<?php } ?>
		
	<!-- fonts -->
	<link href='https://fonts.googleapis.com/css?family=Ubuntu:400,500,700,300' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Signika+Negative:400,300,600,700' rel='stylesheet' type='text/css'>

	<!-- icons -->
	<link rel="icon" href="<?php echo SITE_URL; ?>favicon.ico">	
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo SITE_URL; ?>images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo SITE_URL; ?>images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo SITE_URL; ?>images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo SITE_URL; ?>images/ico/apple-touch-icon-57-precomposed.png">
    <!-- icons -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
	  	<a href="#" class="scrollup">Top</a>

<header id="header" class="clearfix">
		<!-- navbar -->
		<nav class="navbar navbar-default">
			<div class="container">
				<!-- navbar-header -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echo SITE_URL; ?>"><img class="img-responsive" src="<?php echo SITE_URL; ?>images/logo.png" alt="<?php echo SITE_TITLE; ?>"></a>
				</div>
				<!-- /navbar-header -->
				
				<div class="navbar-left" style="margin-top: 10px">
					<div class="collapse navbar-collapse" id="navbar-collapse">
						<ul class="nav navbar-nav">
							<li><a href="<?php echo SITE_URL; ?>"><i class="fa fa-home"></i> Home</a></li>
							<li<?php /*class="active"*/ ?>><a href="<?php echo SITE_URL; ?>jobs.php"> Jobs</a></li>
							<?php /*if (GetJobsTotal("offer") > 0) {*/ ?>
								<li><a href="<?php echo SITE_URL; ?>offers.php">Offers</a></li>
							<?php /*}*/ ?>							
							<li><a href="<?php echo SITE_URL; ?>workers.php"><i class="fa fa-users"></i> Find Workers</a></li>
							
							<?php if (isLoggedIn()) { ?>
							<li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user-circle-o"></i> My Account<span class="caret"></span></a>
								<ul class="dropdown-menu">
									<?php require_once ("inc/usermenu.inc.php"); ?>
								</ul>
							</li>
							<?php }else{ ?>
								<li><a href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-user-circle-o"></i> My Account</a></li>
							<?php } ?>
							<li><a href="<?php echo SITE_URL; ?>contact.php">Contact</a></li>
						</ul>
					</div>
				</div><!-- navbar-left -->
				
				<!-- nav-right -->
				<div class="nav-right" style="padding-top: 10px;">	
					
				
					<!-- language-dropdown -->
					<?php /* ?>					
					<div style="margin-left:10px;" class="dropdown language-dropdown">
						<a data-toggle="dropdown" href="#"><span class="change-text"><img src="<?php echo SITE_URL; ?>images/flags/us.png" /> <!--english--></span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu language-change">
							<li><a href="<?php echo SITE_URL; ?>?lang=english"><img src="<?php echo SITE_URL; ?>images/flags/us.png" /> english</a></li>
							<li><a href="<?php echo SITE_URL; ?>?lang=german"><img src="<?php echo SITE_URL; ?>images/flags/de.png" /> german</a></li>
						</ul>								
					</div>
					<?php */ ?>
															
				 <?php if (isLoggedIn()) { ?>
					<i class="fa fa-user"></i><!-- Welcome, --> <a href="<?php echo SITE_URL; ?>myaccount.php" style="color: #777"><?php echo substr($_SESSION['FirstName'], 0, 15); ?></a> &middot; 			
					<!--Balance: --><a href="<?php echo SITE_URL; ?>mypayments.php"><span class="label label-success"><?php echo GetUserBalance($userid); ?></span></a> &middot; 
					<?php if (GetReferralsTotal($userid) > 0) { ?><i class="fa fa-users"></i><a href="<?php echo SITE_URL; ?>invite.php#refs_list"><span class="badge" style="background: #6eb8c9"><?php echo GetReferralsTotal($userid); ?></span></a> Referrals &middot; <?php } ?>
					<?php if (GetMemberMessagesTotal() > 0) { ?><a href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-envelope Blink" style="color: #5bc0de"></i> <sup><?php echo GetMemberMessagesTotal(); ?></sup></a> &middot; <?php } ?>					
					<?php if (GetPendingTasksTotal(0, $userid) > 0) { ?><a href="<?php echo SITE_URL; ?>myjobs.php"><i class="fa fa-bell-o Blink jobfia_tooltip" data-placement="bottom" title="Pending Tasks" style="color: #ff790a"></i> <span class="badge"><?php echo GetPendingTasksTotal(0, $userid); ?></span></a> &middot; <?php } ?>
					<?php if (GetJobInvitesTotal($userid) > 0) { ?><a href="<?php echo SITE_URL; ?>jobs_invited.php"><i class="fa fa-fire Blink jobfia_tooltip" data-placement="bottom" title="Job Invitations" style="color: #ff790a"></i> <span class="badge"><?php echo GetJobInvitesTotal($userid); ?></span></a> &middot; <?php } ?>	
					<a href="<?php echo SITE_URL; ?>logout.php" style="color: #777">Logout <i class="fa fa-sign-out jobfia_tooltip" data-placement="bottom" title="Logout"></i> </a>
				<?php }else{ ?>														
					<ul class="sign-in">
						<li><i class="fa fa-user"></i></li>
						<li><a href="<?php echo SITE_URL; ?>signup.php">Sign up</a></li> &nbsp;
						<li><i class="fa fa-sign-in"></i> <a href="<?php echo SITE_URL; ?>login.php">Log in</a></li>
					</ul>				
					<a href="<?php echo SITE_URL; ?>job_create.php" class="btn hidden-xs">+ Post a Job</a>				
					<?php /*if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
									<p><a href="javascript: void(0);" onclick="facebook_login();" class="connect-f"><img src="<?php echo SITE_URL; ?>images/jobfia_facebook.png" /></a></p>
					<?php }*/ ?>									
				<?php } ?>
			
								
				</div>
				<!-- nav-right -->
			</div><!-- container -->
		</nav><!-- navbar -->
	</header><!-- header -->

				
				<?php if (isset($single_page) && $single_page == 1) { ?>
					<section class="clearfix user-page"><div class="container">
					<div class="row"><div class="section">
				<?php } ?>
				