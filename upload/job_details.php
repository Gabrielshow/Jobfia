<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	
	require_once("inc/config.inc.php");
	
	if (SHOW_JOB_INFO_UNREG != 1)
	{
		require_once("inc/auth.inc.php");
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_id	= (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	// hide jobs for user's from other countries
	/*if (isLoggedIn() && HIDE_NOT_COUNTRY_JOBS == 1 && !isOwner($job_id, $userid) && !CheckTargeting($job_id, $_SESSION['country']) && !isWorked($job_id, $userid))
	{
		header ("Location: index.php");
		exit();
	}*/ //dev

	$where = " job_type='job' AND ";
	$query = "SELECT jobs.*, DATE_FORMAT(jobs.added, '%M %e, %Y') AS date_added, DATE_FORMAT(jobs.updated, 'e %b %Y %h:%i %p') AS date_updated, jobs.status as job_status, users.*, DATE_FORMAT(users.created, '%M %e, %Y') AS signup_date, DATE_FORMAT(users.last_login, '%M %e, %Y') AS last_login_date FROM jobfia_jobs jobs LEFT JOIN jobfia_users users ON jobs.user_id=users.user_id WHERE $where jobs.job_id='$job_id' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	if ($total > 0)
	{
		smart_mysql_query("UPDATE jobfia_jobs SET views=views+1 WHERE job_id='$job_id' LIMIT 1");
		$row = mysqli_fetch_array($result);
		$ptitle = $row['title'];
	}
	else
	{
		$ptitle = "Job not found";
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $ptitle;
	$PAGE_DESCRIPTION	= $row['meta_description'];
	$PAGE_KEYWORDS		= $row['meta_keywords'];

	require_once ("inc/header.inc.php");

?>

	<?php if ($total > 0) { ?>


<section class="job-bg page job-details-page">
		<div class="container">
			<!--
			<div class="breadcrumb-section">
				<ol class="breadcrumb">
					<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
					<li><a href="#"><?php echo GetJobCategory($row['job_id']); ?></a></li>
				</ol>					
				<h2 class="title"><?php echo GetJobCategory($row['job_id']); ?></h2>
			</div>
			-->

			<div class="banner-form banner-form-full job-list-form hidden-xs">
				<form action="<?php echo SITE_URL; ?>jobs.php">
					<!-- category-change -->
					<div class="dropdown category-dropdown">						
						<a data-toggle="dropdown" href="#"><span class="change-text">Job Category</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change">
							<li><a href="#">Facebook</a></li>
							<li><a href="#">Forums</a></li>
							<li><a href="#">Like me</a></li>
							<li><a href="#">Sign up</a></li>
							<li><a href="#">Surveys</a></li>
						</ul>								
					</div><!-- category-change -->
					
					<!-- language-dropdown -->
					<div class="dropdown category-dropdown language-dropdown">
						<a data-toggle="dropdown" href="#"><span class="change-text">Job Location</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change language-change">
							<!--
							<li><a href="#">Location 1</a></li>
							<li><a href="#">Location 2</a></li>
							-->
							<?php
			
								$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
								$rs_country = smart_mysql_query($sql_country);
								$total_country = mysqli_num_rows($rs_country);
			
								if ($total_country > 0)
								{
									while ($row_country = mysqli_fetch_array($rs_country))
									{
										if ($country == $row_country['country_id'])
											echo "<li value='".$row_country['country_id']."' selected><a href='#'>".$row_country['name']."</a></li>\n";
										else
											echo "<li value='".$row_country['country_id']."'><a href='#'>".$row_country['name']."</a></li>\n";
									}
								}
			
							?>	
						</ul>								
					</div><!-- language-dropdown -->
					<input type="text" name="searchtext" class="form-control" placeholder="Type your keyword" value="<?php if (isset($stext)) echo $stext; ?>">
					<button type="submit" class="btn btn-primary" value="Search">Search</button>
					<input type="hidden" name="action" value="search">
				</form>
			</div><!-- banner-form -->

			<div class="job-details">
				<div class="section job-ad-item">
					<div class="item-info">
						<div class="item-image-box">
							<div class="item-image">
								<img src="images/job/1.png" alt="Image" class="img-responsive">
							</div><!-- item-image -->
						</div>

						<div class="job_status pull-right" style="font-size: 20px">
										<?php
											switch ($row['status'])
											{
												case "active": echo "<span class='label label-success'>open</span>"; break;
												case "inactive": echo "<span class='label label-default'>closed</span>"; break;
												case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;
												case "declined": echo "<span class='label label-danger'>declined</span>"; break;
												case "complete": echo "<span class='label label-info'>complete</span>"; break;
												default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
											}
										?>								
						</div>

						<div class="ad-info">
							<span><span><a href="#" class="title"><?php echo $row['title']; ?></a></span></span>
							<div class="ad-meta">
								<ul>
									<!--<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>New York, US</a></li>
									<li><a href="#"><i class="fa fa-clock-o" aria-hidden="true"></i>Completion Time</a></li>-->
									<li><i class="fa fa-money" aria-hidden="true"></i>Reward: <?php echo DisplayMoney($row['price']); ?></li>
									<li><a href="#"><i class="fa fa-tags" aria-hidden="true"></i><?php echo GetJobCategory($row['job_id']); ?></a></li>
									<li><i class="fa fa-hourglass-start" aria-hidden="true"></i>Started: <?php echo $row['date_added']; ?></li>
								</ul>
							</div><!-- ad-meta -->									
						</div><!-- ad-info -->
					</div><!-- item-info -->
					
					
					<div class="social-media">
						<div class="button">
							<a href="#jobapply" class="btn btn-primary"><i class="fa fa-briefcase" aria-hidden="true"></i>Apply For This Job</a>
							<!--<a href="#" class="btn btn-primary bookmark"><i class="fa fa-bookmark-o" aria-hidden="true"></i>Add to Saved</a>-->
						</div>
						<!--
						<ul class="share-social">
							<li>Share this job</li>
							<li><a href="#"><i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-twitter-square fa-lg" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-google-plus-square fa-lg" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-linkedin-square fa-lg" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-pinterest-square fa-lg" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-tumblr-square fa-lg" aria-hidden="true"></i></a></li>
						</ul>-->	
					</div>
				</div><!-- job-ad-item -->


				<?php if ($row['premium'] == 1 && isLoggedIn()) { ?>
					<?php if (!isPremium($userid)) { ?>
						<div class="alert alert-info text-center">
							<h4><b><i class="fa fa-warning"></i> This job can accept only <span style="color: #F49C04"><i class="fa fa-user"></i> premium workers</span>.
							Please <a href="<?php echo SITE_URL; ?>upgrade_account.php">upgrade your account</a> to premium.</b></h4>
						</div>
					<?php } ?>
				<?php } ?>
				
				
				<?php if (HIDE_NOT_COUNTRY_JOBS != 1 && !CheckTargeting($job_id, $_SESSION['country'])) { ?>
					<div class="alert alert-warning">
						<p align="center"><img src="<?php echo SITE_URL; ?>images/icon_blocked.png" /></p>
						<p align="center">Sorry, this job does not accept workers from your country.</p>
						<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back</a></p>
					</div>
				<?php }else{ ?>			
				
				<div class="job-details">
				<div class="section job-ad-item" style="background: #e8f1e9">
					<div class="item-info">
		
						<div class="row">
							
							<div class="col-md-3 col-xs-6 text-center">
								<div class="boxio" style="border-top: 5px solid #6dba08"> 
								  <i class="fa fa-money fa-3x round_icon" aria-hidden="true" style="background: #6dba08"></i>
						          <span class="num"><?php echo DisplayMoney($row['price']); ?></span>
						          <p> The fund to be added to your account</p>
		        				</div>
							</div>
							
							<?php if ($row['duration'] != "") { ?>
								<div class="col-md-3 col-xs-6 text-center">
									<div class="boxio" style="border-top: 5px solid #e36159">
										<i class="fa fa-clock-o fa-3x round_icon" aria-hidden="true" style="background: #e36159"></i>
										<span class="num"><?php echo ConvertDuration($row['duration']); ?></span>
										<p>Time to Complete Job</p>
									</div>
								</div>
							<?php } ?>
							
							<div class="col-md-3 col-xs-6 text-center">
								<div class="boxio" style="border-top: 5px solid #2baab1">
									<i class="fa fa-users fa-3x round_icon" aria-hidden="true" style="background: #2baab1"></i> 
									<span class="num"><?php echo GetJobAvailablePositions($row['job_id']); ?></span>
									<p>Available Positions</p>
								</div>
							</div>
							
							<div class="col-md-3 col-xs-6 text-center">
								<div class="boxio" style="border-top: 5px solid #7fbc04">
									<i class="fa fa-dashboard fa-3x round_icon" aria-hidden="true" style="background: #7fbc04"></i> 				
									<span class="num"><?php echo GetJobWorkersTotal($row['job_id']); ?> / <sup><?php echo $row['positions']; ?></sup></span>
									<div class="progress" style="height: 22px">
										  <div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>%"></div>
									</div>
									<p>Job Done</p>
									<?php if (GetJobWorkersTotal($row['job_id']) > 0) { ?>( <i class="fa fa-bar-chart"></i> success rate: <?php echo GetJobSuccess($row['job_id'], $show_bar = 1); ?> )<?php } ?>
								</div>
							</div>
							
						</div>
				
					</div>
				</div></div>
				
				
				<div class="job-details-info">
					<div class="row">
						<div class="col-sm-8">
							<div class="section job-description">
								<div class="description-info">
									<h3>Job Description</h3>
									<p class="description"><span>
									<?php
										$desc = stripslashes($row['description']);
										$desc = str_replace("{WORKER_ID}", $userid, $desc);
										$desc = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '<a href="'.SITE_URL.'out.php?url=$0" target="_blank" class="external" rel="nofollow">$0</a>', $desc);
										echo $desc;
									?>
									</span></p>
									<?php if ($row['tags'] != "") { ?><p><i class="fa fa-tags" aria-hidden="true"></i> Tags: <?php echo $row['tags']; ?></p><?php } ?>
								</div>
								<div class="responsibilities">
									<a name="jobapply"></a>
									<h3>How to prove you done it</h3>
									<p><?php echo stripslashes($row['proof']); ?></p>
								</div>
					
				<div style="clear: both"></div>
								
				<?php if ($row['worldwide'] != 1) { ?>
					<h3><i class="fa fa-globe" style="color: #f0ad4e"></i> Job Targeting</h3>
					<p>You can accept this job if you are from any of these countries:</p>
					<p><?php echo GetJobCountriesList($row['job_id']); ?></p>
					<br>
				<?php }else{ ?>
					<h3><i class="fa fa-globe" style="color: #04a8df"></i> Job Targeting</h3>
					<p><span class="label label-primary">WORLDWIDE</span><br> (this job is available for workers from all countries)</p>
				<?php } ?>

				<div style="clear: both"></div>

				<hr>
				<div class="text-center">
				<?php if ($row['job_status'] == 'active') { ?>
				<?php if (!isCompleteJob($job_id, $userid) || $row['few_times'] == 1) { ?>
					<?php if (isLoggedIn()) { ?>
						<?php if (isOwner($job_id, $userid)) { ?>
							<div class="alert alert-info"><h4><i class="fa fa-info-circle fa-lg"></i> Sorry, you can not apply to your own job.</h4></div>
						<?php }elseif (PIN_VERIFICATION == 1 && !isPinVerified($userid)) { ?>
							<div class="alert alert-info"><h4>Sorry, you can not apply to jobs. You need to <a href="<?php echo SITE_URL; ?>pin_verification.php">verify</a> your address.</h4></div>
						<?php }else{ ?>
							<a href="<?php echo SITE_URL; ?>job_apply.php?id=<?php echo $job_id; ?>" class="btn btn-success btn-lg">Apply for Job</a>
							<?php if (isLoggedIn()) { ?>
								&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>jobs.php?id=<?php echo $job_id; ?>&act=hide" class="btn btn-warning btn-lg">Not interested</a>
							<?php } ?>
						<?php } ?>
					<?php }else{ ?>
						<a href="<?php echo SITE_URL; ?>job_apply.php?id=<?php echo $job_id; ?>" class="btn btn-success btn-lg">Apply for Job</a>
						<?php if (isLoggedIn()) { ?>
							&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>jobs.php?id=<?php echo $job_id; ?>&act=hide" class="btn btn-warning btn-lg">Not interested</a>
						<?php } ?>
					<?php } ?>
				<?php }else{ ?>
					<div class="alert alert-success"><h4>You have currently completed this job.</h4> <a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back</a></div>
				<?php } ?>
				<?php } ?>
				</div>
															
							</div>							
						</div>
						<div class="col-sm-4">
							<div class="section job-short-info">
								
								<div style="float: right;"><a class="report" style="color: #bb5e5e" href="<?php echo SITE_URL; ?>job_report.php?id=<?php echo $row['job_id']; ?>"><i class="fa fa-flag"></i> report</a></div>
								
						<h1>Job Details</h1>
						
						<ul>
							<?php if ($row['featured'] == 1) { ?><li><span class="label label-info jobfia_tooltip" title="Featured"><i class="fa fa-star"></i> Featured Job</span></li><?php } ?>
							<?php if ($row['premium'] == 1) { ?><li><span class="label label-warning jobfia_tooltip" title="this job for premium workers only"><i class="fa fa-user"></i> for premium workers</span></li><?php } ?>							
							<li><span class="icon"><i class="fa fa-list" aria-hidden="true"></i></span>Category: <?php echo GetJobCategory($row['job_id']); ?></li>
							<li><span class="icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>Posted: <?php echo relative_date(strtotime($row['added'])); //$row['date_added']; ?></li>
							<?php if ($row['updated'] != "0000-00-00 00:00:00") { ?><li><span class="icon"><i class="fa fa-calendar-o" aria-hidden="true"></i></span>Updated: <?php echo relative_date(strtotime($row['updated'])); ?></li><?php } ?>
							
							<li><span class="icon"><i class="fa fa-share" aria-hidden="true"></i></span> Share this job:</li>
						</ul>
						
						<input type="text" style="border:2px dotted #eee; background:#f3f3f3; color:#444444;" id="bn1" class="form-control" READONLY onfocus="this.select();" onclick="this.focus();this.select();" value="<?php echo SITE_URL."job_details.php?id=".$row['job_id']; ?>" />
								<!--
								<ul class="share-social">
									<li><a href="#"><i class="fa fa-facebook-official fa-2x" aria-hidden="true"></i></a></li>
									<li><a href="#"><i class="fa fa-twitter-square fa-2x" aria-hidden="true"></i></a></li>
									<li><a href="#"><i class="fa fa-google-plus-square fa-2x" aria-hidden="true"></i></a></li>
									<li><a href="#"><i class="fa fa-linkedin-square fa-2x" aria-hidden="true"></i></a></li>
								</ul>
								-->

								<div style="padding: 15px; width: 100%; text-align: center">
									<!-- AddThis Button BEGIN -->
									<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
										 <a class="addthis_button_preferred_1"></a>
										 <a class="addthis_button_preferred_2"></a>
										 <a class="addthis_button_preferred_3"></a>
										 <a class="addthis_button_preferred_4"></a>
										 <a class="addthis_button_preferred_5"></a>
										 <a class="addthis_button_preferred_6"></a>
										 <a class="addthis_button_compact"></a>
									</div>
									<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=<?php echo ADDTHIS_ID; ?>"></script>
									<!-- AddThis Button END -->
								</div>									 
																		
								<!--<ul>
									<li><span class="icon"><i class="fa fa-bolt" aria-hidden="true"></i></span>Posted: 1 day ago</li>
									<li><span class="icon"><i class="fa fa-user-plus" aria-hidden="true"></i></span> Job poster: <a href="#">Lance Ladaga</a></li>
									<li><span class="icon"><i class="fa fa-industry" aria-hidden="true"></i></span>Industry: <a href="#">Marketing and Advertising</a></li>
									<li><span class="icon"><i class="fa fa-line-chart" aria-hidden="true"></i></span>Experience: <a href="#">Entry level</a></li>
									<li><span class="icon"><i class="fa fa-key" aria-hidden="true"></i></span>Job function: Advertising,Design, Art/Creative</li>
								</ul>-->
							</div>
							<div class="section company-info">
								<h1 class="text-center">About the Employer</h1>
								
								<h3 class="text-center">
									<?php echo GetUserAvatar($row['user_id'], $width=70); ?>
									<?php echo GetEmployerInfo($row['user_id'], $as_username = 1); ?>							
								</h3>
								
									<?php $user_online = 0; if (ifUserOnline($row['user_id'])) { $user_online = 1; ?>
										<span class="online" style="margin-left: 33%"><i class="fa fa-dot-circle-o Blink jobfia_tooltip" title="user is online" aria-hidden="true" style="color: #64cb54"></i> online</span><br><br>
									<?php } ?>									
	
								<?php if ($row['user_id'] != 0) { ?>
								<ul>
									<li><i class="fa fa-map-marker" aria-hidden="true"></i> From: <span style="float: right"><?php echo GetCountry($row['country_id']); ?> <?php echo ($row['city'] != "") ? "(".$row['city'].")" : ""; ?></span></li>
									<li><i class="fa fa-user" aria-hidden="true"></i> Member Since: <span style="float: right"><?php echo $row['signup_date']; ?></span></li>
									<?php if ($row['login_count'] > 0 && $user_online != 1) { ?>
										<li><i class="fa fa-clock-o" aria-hidden="true"></i> Last Login: <span style="float: right"><?php echo relative_date(strtotime($row['last_login'])); ?></span></li>
									<?php } ?>					
									<li><i class="fa fa-briefcase" aria-hidden="true"></i> Jobs Posted: <span style="float: right"><?php echo GetEmployerJobsTotal($row['user_id']); ?></span></li>
									<li><i class="fa fa-money" aria-hidden="true"></i> Total Spend: <span style="float: right"><?php echo GetEmployerSpendTotal($row['user_id']); ?></span></li>
									<li><i class="fa fa-star" aria-hidden="true"></i> Rating: <span style="float: right"><?php echo GetEmployerRating($row['user_id']); ?></span></li>
								</ul>
								<?php } ?>
							
							</div>
						</div>
					</div><!-- row -->					
				</div><!-- job-details-info -->				
			</div><!-- job-details -->
		</div><!-- container -->
	</section><!-- job-details-page -->
	
	

				<?php
						// show employer's other 5 random active jobs
						$other_query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_id<>'$job_id' AND job_type='job' AND user_id='".(int)$row['user_id']."' AND status='active' ORDER BY RAND() LIMIT 5";
						$other_result = smart_mysql_query($other_query);
						$other_total = mysqli_num_rows($other_result);

						if ($other_total > 0) { 
				?>
				<div class="container">
				<div class="section">
					<h3><i class="fa fa-briefcase"></i> Other open jobs by this employer</h3>	
					<ul id="other_jobs">
					<?php while ($other_row = mysqli_fetch_array($other_result)) { ?>
						<li><h4><img src="/images/job/1.png" width="40"> <a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $other_row['job_id']; ?>"><?php echo $other_row['title']; ?></a> <!--@ <span style="color: #6dba08"><?php echo DisplayMoney($other_row['price']); ?></span>--></h4></li>
					<?php } ?>
					</ul>
				</div>
				</div>
				<?php } ?>	
	

		<?php } ?>


	<?php }else{ ?>
		<section class="page job-details-page">
			<div class="container">	
		
			<h1><i class="fa fa-briefcase"></i> Job not found</h1>
			<div class="alert alert-info text-center"><i class="fa fa-info-circle fa-lg"></i> Sorry, no job found. <a href="<?php echo SITE_URL; ?>jobs.php">See all jobs</a></div>
			
			</div>
		</section>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>