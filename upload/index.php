<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	if (file_exists("./install.php"))
	{
		header ("Location: install.php");
		exit();
	}

	session_start();
	//require_once("inc/iflogged.inc.php");
	require_once("inc/config.inc.php");
	require_once("cron.php");

	// save referral's link //
	if (isset($_GET['ref']) && is_numeric($_GET['ref']))
	{
		$ref_id = (int)$_GET['ref'];
		setReferral($ref_id);

		// count link clicks
		if (!isLoggedIn()) smart_mysql_query("UPDATE jobfia_users SET ref_clicks=ref_clicks+1 WHERE user_id='$ref_id' LIMIT 1");

		header("Location: index.php");
		exit();
	}
	
	if (isset($_POST['action']) && $_POST['action'] == "newsletter")
	{
		$email = mysqli_real_escape_string($conn, getPostParameter('email'));
		//dev	
	}

	$content = GetContent('home');

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= SITE_HOME_TITLE;
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];

	require_once("inc/header.inc.php");

?>

	<div class="banner-job">
		<div class="banner-overlay"></div>
		<div class="container text-center">
			<h1 class="title">Fast and Easy Way to Post &amp; Do Jobs</h1>
			<h3>We offer a lot of jobs & offers for you right now</h3>
			<div class="banner-form">
				<form action="<?php echo SITE_URL; ?>jobs.php" method="get">
					<input type="text" name="searchtext" class="form-control" placeholder="Type your key word">
					<div class="dropdown category-dropdown">						
						<a data-toggle="dropdown" href="#"><span class="change-text">Jobs</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change">
							<li><a href="#">Search Jobs</a></li>
							<li><a href="#">Search Offers</a></li>
						</ul>								
					</div><!-- category-change -->
					<button type="submit" class="btn btn-primary" value="Search">Search</button>
					<input type="hidden" name="action" value="search">
				</form>
			</div><!-- banner-form -->
			
			<ul class="banner-socail list-inline">
				<li><a href="<?php echo FACEBOOK_PAGE; ?>" title="Facebook"><i class="fa fa-facebook"></i></a></li>
				<li><a href="<?php echo TWITTER_PAGE; ?>" title="Twitter"><i class="fa fa-twitter"></i></a></li>
				<li><a href="<?php echo GOOGLEPLUS_PAGE; ?>" title="Google Plus"><i class="fa fa-google-plus"></i></a></li>
			</ul><!-- banner-socail -->
		</div><!-- container -->
	</div><!-- banner-section -->

	<div class="page">
		<div class="container">
			
			<!--<div class="section"><?php echo $content['text']; ?></div>-->
			<!-- latest finished // john finished eee earn 5 4 minutes ago //dev -->

						<?php				
								$cresult = smart_mysql_query("SELECT * FROM jobfia_categories WHERE parent_id='0' ORDER BY name");
								$ctotal = mysqli_num_rows($cresult);
										
								if ($ctotal > 0) {
						?>		
							<div class="section category-items job-category-items text-center">
								<ul class="category-list">		
								<?php
										while ($crow = mysqli_fetch_array($cresult)) { $category_id	= $crow['category_id']; $category_name	= $crow['name'];					
								?>
									<li class="category-item">
											<a href="<?php echo SITE_URL; ?>jobs.php?cat=<?php echo $category_id; ?>">
											<div class="category-icon"><img src="<?php echo SITE_URL; ?>images/category1.png" alt="images" class="img-responsive"></div>
											<span class="category-title"><?php echo $category_name; ?></span>
											<span class="category-quantity"><?php echo (CategoryTotalJobs($category_id) > 0) ? "(".CategoryTotalJobs($category_id).")" : "" ; ?></span>
											</a>
									</li>
								<?php } ?>
								</ul>				
							</div>									
													
						<?php } ?>

			<?php if (!isLoggedIn()) { ?>
			<div class="section cta cta-two">				
				<!--<p>Welcome to our micro jobs site. We are an innovative online platform that connect employers and workers from all around the world. Our platform helps online freelancers to make money by completing a variety of tasks and employers can find the best workers to get work done!</p>-->				
				<div class="row">
					<div class="col-sm-6">
						<div class="workshop">
							<center><img src="<?php echo SITE_URL; ?>images/icon_employer.png" alt="Employer" class="img-responsive"></center>
							<h2 class="text-center" style="color: #000">Are you an employer?</h2>
							<ul id="home_benefits">
								<li>Post jobs</li>
								<li>Find great workers</li>
								<li>Pay only when you are satisfied</li>
								<li>Get amazing results</li>
								<li>Boost your business</li>
							</ul><br>
							<p align="center"><a class="btn btn-success btn-lg" href="<?php echo SITE_URL; ?>signup.php">+ Post a Job</a></p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="workshop">
							<center><img src="<?php echo SITE_URL; ?>images/icon_worker.png" alt="Worker" class="img-responsive"></center>
							<h2 class="text-center" style="color: #000">Are you looking for work?</h2>
							<ul id="home_benefits">
								<li>Find jobs of your choice</li>
								<li>Complete simple tasks</li>
								<li>Earn money</li>
								<li>Get Sign Up bonus</li>
								<li>Invite your friends and earn more</li>
							</ul><br>
							<p align="center"><a class="btn btn-success btn-lg" href="<?php echo SITE_URL; ?>signup.php"><i class="fa fa-check-square-o"></i> Get Started</a></p>
						</div>
					</div>
				</div>
			</div><!-- workshop-traning -->
			<?php } ?>	
					

			<?php if (GetJobsTotal() > 0) { ?>
			<div class="section latest-jobs-ads">
				<div class="section-title tab-manu">
					<h4>Latest Jobs</h4>
					 <!-- Nav tabs -->      
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation"><a href="#recent-jobs" data-toggle="tab">Recent Jobs</a></li>
						<li role="presentation" class="active"><a href="#popular-jobs" data-toggle="tab">Popular Jobs</a></li>
					</ul>
				</div>

				<div class="tab-content">

					<div role="tabpanel" class="tab-pane fade in" id="recent-jobs">
					<?php
						$r_query = "SELECT * FROM jobfia_jobs WHERE job_type='job' AND status='active' ORDER BY views DESC LIMIT ".HOMEPAGE_JOBS_LIMIT;
						$r_result = smart_mysql_query($r_query);
						$r_total = mysqli_num_rows($r_result);
						$cc = 0;
			
						if ($r_total > 0) {
					?>			
						<?php while ($r_row = mysqli_fetch_array($r_result)) { $cc++; ?>		
						<div class="job-ad-item">
							<div class="item-info">
								<div class="item-image-box">
									<div class="item-image">
										<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $r_row['job_id']; ?>"><img src="images/job/1.png" alt="Image" class="img-responsive"></a>
									</div>
								</div>
								<div class="ad-info">
									<span><a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $r_row['job_id']; ?>" class="title"><?php if (strlen($r_row['title']) > 100) $job_title = substr($r_row["title"], 0, 100)."..."; else $job_title = $r_row["title"]; echo $job_title; ?></a></span>
									<?php echo substr(stripslashes($r_row['description']), 0, 150); ?><br><br>
									<div class="ad-meta">
										<ul>
											<!--<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>New York, US </a></li>-->
											<li><a href="#"><i class="fa fa-money" aria-hidden="true"></i><?php echo DisplayMoney($r_row['price']); ?></a></li>
											<li><a href="#"><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ConvertDuration($r_row['duration']); ?></a></li>
											<li><i class="fa fa-users" aria-hidden="true"></i><?php echo GetJobWorkersTotal($r_row['job_id']); ?> / <sup><?php echo $r_row['positions']; ?></sup></li>
											<li><i class="fa fa-tags" aria-hidden="true"></i><?php echo GetJobCategory($r_row['job_id']); ?></li>
											<li><a href="#"><i class="fa fa-calendar" aria-hidden="true"></i><?php echo relative_date(strtotime($r_row['added'])); ?></a></li>
										</ul>
									</div>									
								</div>
								<div class="button">
									<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $r_row['job_id']; ?>" class="btn btn-primary">Apply Now</a>
								</div>
							</div>
						</div><!-- ad-item -->
						<?php } ?>	
					<?php }else{ ?>
						No jobs at this time.
					<?php } ?>
					</div><!-- tab-panel end -->


					<div role="tabpanel" class="tab-pane fade in active" id="popular-jobs">
<?php
						$p_query = "SELECT * FROM jobfia_jobs WHERE job_type='job' AND status='active' ORDER BY added DESC LIMIT ".HOMEPAGE_JOBS_LIMIT;
						$p_result = smart_mysql_query($p_query);
						$p_total = mysqli_num_rows($p_result);
						$cc = 0;
			
						if ($p_total > 0) {
					?>			
						<?php while ($p_row = mysqli_fetch_array($p_result)) { $cc++; ?>		
						<div class="job-ad-item">
							<div class="item-info">
								<div class="item-image-box">
									<div class="item-image">
										<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $p_row['job_id']; ?>"><img src="images/job/1.png" alt="Image" class="img-responsive"></a>
									</div>
								</div>
								<div class="ad-info">
									<span><a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $p_row['job_id']; ?>" class="title"><?php if (strlen($p_row['title']) > 100) $job_title = substr($p_row["title"], 0, 100)."..."; else $job_title = $p_row["title"]; echo $job_title; ?></a></span>
									<?php echo substr(stripslashes($p_row['description']), 0, 150); ?><br><br>
									<div class="ad-meta">
										<ul>
											<!--<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>New York, US </a></li>-->
											<li><a href="#"><i class="fa fa-money" aria-hidden="true"></i><?php echo DisplayMoney($p_row['price']); ?></a></li>
											<li><a href="#"><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ConvertDuration($p_row['duration']); ?></a></li>
											<li><i class="fa fa-users" aria-hidden="true"></i><?php echo GetJobWorkersTotal($p_row['job_id']); ?> / <sup><?php echo $p_row['positions']; ?></sup></li>
											<li><i class="fa fa-tags" aria-hidden="true"></i><?php echo GetJobCategory($p_row['job_id']); ?></li>
											<li><a href="#"><i class="fa fa-calendar" aria-hidden="true"></i><?php echo relative_date(strtotime($p_row['added'])); ?></a></li>
										</ul>
									</div>									
								</div>
								<div class="button">
									<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $p_row['job_id']; ?>" class="btn btn-primary">Apply Now</a>
								</div>
							</div>
						</div><!-- ad-item -->
						<?php } ?>	
					<?php }else{ ?>
						No jobs at this time.
					<?php } ?>
					</div><!-- tab-panel -->
				</div><!-- tab-content -->
			</div><!-- trending jobs -->	
			<?php } ?>	



			<?php
					$oquery = "SELECT * FROM jobfia_jobs WHERE $last_sql job_type='offer' AND status='active' ORDER BY views DESC LIMIT 8"; //sales DATE(WEEK) 
					$oresult = smart_mysql_query($oquery);
					$ototal = mysqli_num_rows($oresult);
					$cc = 0;
							
					if ($ototal > 0) { 
			?>
			<br>
			<div class="section">
			
			<div class="pull-right" style="padding-top: 25px"><a class="badge" href="<?php echo SITE_URL; ?>offers.php">All Offers</a></div>
			<h3><i class="fa fa-list" style="color: #5cb85c"></i> Popular Offers This Week</h3>			
			<div class="row">
			<?php while ($orow = mysqli_fetch_array($oresult)) { $cc++; ?>
				<div class="col-md-3">

							<div class="job-ad-item <?php if ($orow['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
								<div class="item-info">
									<div class="item-image-box2">
										<div class="text-center">
											<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $orow['job_id']; ?>"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($orow['image'] != "") ? $orow['image'] : "no_image.png"; ?>" alt="" height="150" style="margin: 5px 0;"></a>
										</div>
									</div>

									<div class="offer-info">
									  <?php echo GetUserAvatar($orow['user_id'], 33); ?>		  
									  by <?php echo GetEmployerInfo($orow['user_id'], $username = 1); ?><br><br>
										<?php if ($orow['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
										<a class="job_title" href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $orow['job_id']; ?>">
											<h4>I will <?php if (strlen($orow['title']) > 75) $job_title = substr($orow["title"], 0, 70)."..."; else $job_title = $orow["title"]; echo $job_title; ?></h4>
										</a>	
									  <br><i class="fa fa-star fa-lg" style="color: #ffc101"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($orow['job_id']); ?>)
									  &nbsp;&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $orow['job_id']; ?>&act=save"><i class="fa fa-heart jobfia_tooltip" title="Add to Saved" style="color: #ae3535"></i></a>
									  <hr>
									  <div class="pull-left"><a href="<?php echo SITE_URL; ?>order.php?id=<?php echo $orow['job_id']; ?>" class="btn btn-success"><i class="fa fa-shopping-cart"></i> order</a></div>
									  <div class="pull-right" style="padding-top: 5px"><span style="font-size: 11px; color: #555; text-transform: uppercase; text-align: right">starting at</span> <span style="font-size: 19px; color: #4ea204"><?php echo DisplayMoney($orow['price']); ?></span></div>
									  <div style="clear: both"></div>
									 	<?php if ($orow['country'] != "") { ?>					
											<div class="ad-meta">
												<ul>
													<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>San Francisco, CA, US </a></li>
												</ul>
											</div>
										<?php } ?>							
									</div>
								</div><!-- item-info -->
							</div><!-- job-ad-item -->                
                
				</div>
			<?php } ?>
			</div>
			</div>
			</div>
			<?php } ?>


			<?php if (SHOW_SITE_STATS == 1) { ?>
			<div class="section cta cta-two text-center">
				<div class="row">
					<div class="col-sm-3 col-xs-6">
						<div class="single-cta">
							<div class="cta-icon icon-jobs">
								<!--<img src="images/worker.png" alt="worker" class="img-responsive">-->
								<i class="fa fa-briefcase fa-4x" style="color: #5cb85c"></i>
							</div>
							<h3><?php echo GetJobsTotal(); ?></h3>
							<h4>Jobs Posted</h4>							
							<!--<span class="badge" style="font-size: 13px"><b><?php echo GetActiveJobsTotal(); ?></b> jobs available</span>-->
						</div>
					</div>

					<div class="col-sm-3 col-xs-6">
						<div class="single-cta">
							<div class="cta-icon icon-company">
								<i class="fa fa-check-circle-o fa-4x" style="color: #5cb85c"></i>
							</div>
							<h3><?php echo GetDoneTasksTotal(); ?></h3>
							<h4>Tasks Done</h4>
						</div>
					</div>

					<div class="col-sm-3 col-xs-6">
						<div class="single-cta">
							<div class="cta-icon icon-candidate">
								<i class="fa fa-users fa-4x" style="color: #5cb85c"></i>
							</div>
							<h3><?php echo GetWorkersTotal(); ?></h3>
							<h4>Active Workers</h4>
							<?php if (GetUsersOnline() > 0) { ?>
								<p><span class="badge" style="font-size: 13px; color: #fff"><span class="online2"><i class="fa fa-circle Blink"></i> <?php echo GetUsersOnline(); ?></span> users online</span></p>
							<?php } ?>
						</div>
					</div>
					
					<div class="col-sm-3 col-xs-6">
						<div class="single-cta">
							<div class="cta-icon icon-candidate">
								<i class="fa fa-money fa-4x" style="color: #5cb85c"></i>
							</div>
							<h3><!--5,798,298--><?php echo GetWorkersEarnTotal(); ?></h3>
							<h4>Total Paid</h4>
						</div>
					</div><!-- single-cta -->					
					
				</div><!-- row -->
			</div><!-- cta -->
			<?php } ?>		

		</div><!-- conainer -->
	</div><!-- page -->
	


	
	
	<!-- download -->
	<section id="download" class="clearfix parallax-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2>Browse our jobs, offers and workers</h2>
				</div>
			</div><!-- row -->

			<!-- row -->
			<div class="row">
				<!-- download-app -->
				<div class="col-sm-4">
					<a href="<?php echo SITE_URL; ?>jobs.php" class="download-app">
						<!--<img src="images/icon/1.png" alt="Image" class="img-responsive">-->
						<i class="fa fa-briefcase fa-3x" style="margin-left: 15px"></i>
						<span class="pull-left">
							<span>browse</span>
							<strong>All Jobs</strong>
						</span>
					</a>
				</div><!-- download-app -->

				<!-- download-app -->
				<div class="col-sm-4">
					<a href="<?php echo SITE_URL; ?>offers.php" class="download-app">
						<i class="fa fa-magic fa-3x" style="margin-left: 15px"></i>
						<span class="pull-left">
							<span>browse</span>
							<strong>All Offers</strong>
						</span>
					</a>
				</div><!-- download-app -->

				<!-- download-app -->
				<div class="col-sm-4">
					<a href="<?php echo SITE_URL; ?>workers.php" class="download-app">
						<i class="fa fa-users fa-3x" style="margin-left: 15px"></i>
						<span class="pull-left">
							<span>browse</span>
							<strong>All Workers</strong>
						</span>
					</a>
				</div><!-- download-app -->
			</div><!-- row -->
		</div><!-- contaioner -->
	</section><!-- download -->


	<?php if (!isLoggedIn()) { ?>
	<section id="something-sell" class="clearfix parallax-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2 class="title">Sign up and start to post and do job right now.</h2>
					<h4>Create your account and start to use our jobs site.</h4>
					<a href="<?php echo SITE_URL; ?>signup.php" class="btn btn-primary">Sign up</a>
				</div>
			</div>
		</div>
	</section>
	<?php } ?>


	<?php /*if (SHOW_HOMEPAGE_JOBS == 1 && (isLoggedIn() || SHOW_JOBS_UNREG != 0)) { ?>
	
		<?php
			
			if (HOMEPAGE_JOBS_TYPE == 1) $last_sql =  "homepage='1' AND";
			$last_query = "SELECT * FROM jobfia_jobs WHERE $last_sql job_type='job' AND status='active' ORDER BY featured DESC, added DESC LIMIT ".HOMEPAGE_JOBS_LIMIT;
			$last_result = smart_mysql_query($last_query);
			$last_total = mysqli_num_rows($last_result);
			$cc = 0;

			if ($last_total > 0)
			{
		?>
			<br>
			<div class="section">
			
			<div class="pull-right" style="padding-top: 25px"><a class="badge" href="<?php echo SITE_URL; ?>jobs.php">All jobs</a></div>
			<h3><i class="fa fa-briefcase" style="color: #5cb85c"></i> Recent Jobs</h3>

			<div class="table-responsive">
			<table class="table table-striped table-hover brd" align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<th width="40%">Job Title</th>
				<th width="10%">Reward</th>
				<th width="12%">Completion Time</th>
				<th width="10%">Job Done</th>
				<th width="12%">Success Rate</th>
				<th width="10%">Started</th>
			  </tr>
			<?php while ($last_row = mysqli_fetch_array($last_result)) { $cc++; ?>
			<tr class="<?php if ($last_row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
				  <td valign="middle" align="left">
					<h4><?php if ($last_row['worldwide'] == 1) { ?><img src="<?php echo SITE_URL; ?>images/worldwide.png" class="jobfia_tooltip" align="absmiddle" title="Worldwide" /><?php }else{ ?><img src="<?php echo SITE_URL; ?>images/worldwide_not.png" class="jobfia_tooltip" align="absmiddle" title="Only for selected countries" /><?php } ?>
					<?php if ($last_row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
					<?php if ($last_row['premium'] == 1) { ?><span class="premium jobfia_tooltip" title="for premium workers only"><img src="<?php echo SITE_URL; ?>images/user_premium.png" align="absmiddle" /></span><?php } ?>
					<a class="job_title" href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $last_row['job_id']; ?>">
						<?php if (strlen($last_row['title']) > 75) $job_title = substr($last_row["title"], 0, 70)."..."; else $job_title = $last_row["title"]; echo $job_title; ?>
					</a>
					</h4>
				  </td>
				  <td valign="middle" align="left"><?php echo DisplayMoney($last_row['price']); ?></td>
				  <td valign="middle" align="left"><?php echo ConvertDuration($last_row['duration']); ?></td>
				  <td valign="middle" align="center">
					<?php echo GetJobWorkersTotal($last_row['job_id']); ?>/<sup><?php echo $last_row['positions']; ?></sup>
					<div class="progress" style="height: 15px">
						  <div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($last_row['job_id'])/$last_row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($last_row['job_id'])/$last_row['positions'])*100); ?>%"></div>
					</div>				  
				  </td>
				  <td valign="middle" align="left" style="padding-left: 30px"><?php echo GetJobSuccess($last_row['job_id']); ?></td>
				  <td valign="middle" align="left"><?php echo relative_date(strtotime($last_row['added'])); ?></td>
			</tr>
			<?php } ?>
			</table>
			</div>
			</div>
		<?php } ?>
	<?php }*/ ?>


<?php require_once("inc/footer.inc.php"); ?>