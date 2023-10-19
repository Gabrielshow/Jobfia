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

	$query = "SELECT jobs.*, DATE_FORMAT(jobs.added, '%M %e, %Y') AS date_added, DATE_FORMAT(jobs.updated, 'e %b %Y %h:%i %p') AS date_updated, jobs.status as job_status, users.*, DATE_FORMAT(users.created, '%M %Y') AS signup_date, DATE_FORMAT(users.last_login, '%M %e, %Y') AS last_login_date FROM jobfia_jobs jobs LEFT JOIN jobfia_users users ON jobs.user_id=users.user_id WHERE $where jobs.job_id='$job_id' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	if ($total > 0)
	{
		if (!$_SESSION['recently_viewed']) $_SESSION['recently_viewed'] = array();
		
		if (!in_array($job_id, $_SESSION['recently_viewed']))
			$_SESSION['recently_viewed'][] = $job_id;
		
		if (count($_SESSION['recently_viewed']) > 10) array_shift($_SESSION['recently_viewed']);
		if (isset($_GET['recently_clear']) && $_GET['recently_clear'] == 1) unset($_SESSION['recently_viewed']);
		
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
				
				<div class="job-details-info">
					<div class="row">
						<div class="col-sm-8">
							<div class="section job-description">
								<h2>I will <?php echo $row['title']; ?></h2>
								<a href="#feedbacks"><i class="fa fa-star fa-lg" style="color: #e59d0c"></i><i class="fa fa-star fa-lg" style="color: #e59d0c"></i><i class="fa fa-star fa-lg" style="color: #e59d0c"></i><i class="fa fa-star fa-lg" style="color: #e59d0c"></i><i class="fa fa-star fa-lg" style="color: #e59d0c"></i> (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)</a>
								<hr>			
								<div class="description-info">
									<h3>About This Gig</h3>
									<?php if ($row['image'] != "" && $row['image'] != "no_image.png") { ?><center><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="" style="border-radius: 10px"></center><br><?php } ?>
									<p><?php echo stripslashes($row['description']); ?></p>
									<?php if ($row['tags'] != "") { ?><p>Tags: <?php echo $row['tags']; ?></p><?php } ?>
								</div>
								
								<!--
								<div class="responsibilities">
									 Quantity 1-10 //dev
								</div>-->
				
								<?php if ($row['job_status'] == 'active' && isCompleteJob($job_id, $userid)) { ?>
									<div class="alert alert-success">
										<h4><i class="fa fa-check-circle-o" style="color: #01a650"></i> You have currently purchased this offer.</h4>
										<!--<a href="#" class="btn btn-info pull-right">+ submit testimonial</a>-->
									</div>
								<?php } ?>
															
							</div>

							<!--
							<div class="section job-recommend">
								<a name="feedbacks"></a>
								<h3 class="pull-left"><i class="fa fa-comments-o" style="color: #01a650"></i> Reviews 5.0 (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)</h3>
								<br><a href="" class="btn btn-info pull-right">+ submit testimonial</a>
								<div style="clear: both"></div>
							</div>
							-->
							
							<!-- //dev
							<div class="section job-recommend">
								<h3><i class="fa fa-star-o" style="color: #01a650"></i> Recommended For You</h3>
							</div>-->							
															
														
						</div>
						<div class="col-sm-4">
							<div class="section job-short-info">
								
								<div style="float: right;"><a class="report" style="color: #bb5e5e" href="<?php echo SITE_URL; ?>job_report.php?id=<?php echo $row['job_id']; ?>&offer=1"><i class="fa fa-flag"></i> report</a></div>
								
						<h1>Offer Details</h1>

							<div class="row">
								<div class="col-xs-6"><?php if ($row['duration'] != "") { ?><h4><i class="fa fa-clock-o fa-lg" aria-hidden="true" style="color: #449d43"></i> <?php echo ConvertDuration($row['duration']); ?></h4> Delivery Time<br><br><?php } ?></div>
								<div class="col-xs-6"><h4><i class="fa fa-shopping-cart fa-lg" aria-hidden="true" style="color: #449d43"></i> <?php echo GetOfferSalesTotal($row['job_id']); ?> <sup><?php echo GetOfferSalesTotal($row['job_id'], $today = 1); ?></sup></h4> Sales <sup>today</sup><br><br></div>
								<div class="col-xs-6"><h4><i class="fa fa-star-o fa-lg" style="color: #449d43"></i> <a href="#feedbacks"><?php echo GetJobWorkersTotal($row['job_id']); ?></a></h4> Feedbacks / Rating<br><br></div>
								<div class="col-xs-6"><h4><i class="fa fa-history fa-lg" style="color: #449d43"></i> <?php echo GetOrdersQueueTotal($row['job_id']); ?></h4> Orders in Queue</div>
							</div>
								
								<div class="button">
									<a href="<?php echo SITE_URL; ?>order.php?id=<?php echo $row['job_id']; ?>" class="btn btn-primary btn-block"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Order Now (<?php echo DisplayMoney($row['price']); ?>)</a><br>
								</div>
								<br>
								
								<ul>
								<?php if ($row['featured'] == 1) { ?><li><span class="label label-info jobfia_tooltip" title="Featured"><i class="fa fa-star"></i> Featured Offer</span></li><?php } ?>						
									<li><span class="icon"><i class="fa fa-list" aria-hidden="true"></i></span>Category: <?php echo GetJobCategory($row['job_id']); ?></li>
									<li><span class="icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>Posted: <?php echo relative_date(strtotime($row['added'])); //$row['date_added']; ?></li>
								<?php if ($row['updated'] != "0000-00-00 00:00:00") { ?><li><span class="icon"><i class="fa fa-clock-o" aria-hidden="true"></i></span>Updated: <?php echo relative_date(strtotime($row['updated'])); ?></li><?php } ?>
								
									<li><span class="icon"><i class="fa fa-share" aria-hidden="true"></i></span> Share this offer:</li>
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
						
								<!-- AddThis Button BEGIN -->
								<div class="addthis_toolbox addthis_default_style addthis_32x32_style" style="margin: 8px 0">
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
							<div class="section company-info">
								<h1>Seller</h1>
								
								<center><?php echo GetUserAvatar($row['user_id'], 70); ?></center>
								
								<h3 class="text-center">
									<?php echo GetEmployerInfo($row['user_id'], $username = 1); ?>
								</h3>
								<?php if (ifUserOnline($row['user_id'])) { ?>
									<center><span class="online"><i class="fa fa-dot-circle-o Blink jobfia_tooltip" title="user is online" aria-hidden="true"></i> online</span></center>
								<?php } ?>
								<br>
	
								<?php if ($row['user_id'] != 0) { ?>
								<ul>
									<li><i class="fa fa-map-marker" aria-hidden="true"></i> From: <span style="float: right"><?php echo GetCountry($row['country_id']); ?> <?php echo ($row['city'] != "") ? "(".$row['city'].")" : ""; ?></span></li>
									<li><i class="fa fa-user" aria-hidden="true"></i> Member Since: <span style="float: right"><?php echo $row['signup_date'];?></span></li>
									<?php if ($row['login_count'] > 0) { ?>
										<li><i class="fa fa-clock-o" aria-hidden="true"></i> Last Login: <span style="float: right"><?php echo relative_date(strtotime($row['last_login'])); ?></span></li>
									<?php } ?>
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
									// show user other 5 random active offers
									$other_query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_type='offer' AND job_id<>'$job_id' AND user_id='".(int)$row['user_id']."' AND status='active' ORDER BY RAND() LIMIT 5";
									$other_result = smart_mysql_query($other_query);
									$other_total = mysqli_num_rows($other_result);
			
									if ($other_total > 0) { 
							?>
							<div class="container">
							<div class="section">
								<h3><i class="fa fa-list" style="color: #01a650"></i>  Other gigs by <?php echo GetEmployerInfo($row['user_id'], $username = 1); ?></h3>	
								<div class="row">
								<?php while ($other_row = mysqli_fetch_array($other_result)) { ?>
									<div class="col-md-4">
										<div class="job-ad-item <?php if ($other_row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
											<div class="item-info">
												<div class="item-image-box2">
													<div class="text-center">
														<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $other_row['job_id']; ?>"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($other_row['image'] != "") ? $other_row['image'] : "no_image.png"; ?>" alt=""  height="150" style="margin: 5px 0;"></a>
													</div>
												</div>
			
												<div class="offer-info2 text-center">
													
													<?php if ($other_row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
													<a class="job_title" href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $other_row['job_id']; ?>">
														<span>I will <?php if (strlen($other_row['title']) > 75) $job_title = substr($other_row["title"], 0, 70)."..."; else $job_title = $other_row["title"]; echo $job_title; ?></span>
													</a>	
												  <br><i class="fa fa-star fa-lg" style="color: #ffc101"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($other_row['job_id']); ?>)
												  &nbsp;&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $other_row['job_id']; ?>&act=save"><i class="fa fa-heart jobfia_tooltip" title="Add to Saved" style="color: #ae3535"></i></a>
												  <hr>
												  <div class="pull-left"><a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $other_row['job_id']; ?>" class="btn btn-success"> more info</a></div>
												  <div class="pull-right" style="padding-top: 5px"><span style="font-size: 19px; color: #4ea204"><?php echo DisplayMoney($other_row['price']); ?></span></div>
												  <div style="clear: both"></div>
												 	<?php if ($other_row['country'] != "") { ?>					
														<div class="ad-meta">
															<ul>
																<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>San Francisco, CA, US </a></li>
															</ul>
														</div>
													<?php } ?>							
												</div>
											</div>
										</div>              
                
								</div>			
								<?php } ?>
								</div>
							</div>
							</div>
							<?php } ?>
							

				
					<?php
						// show 10 recently viewed offers
						if (is_array($_SESSION['recently_viewed']) && count($_SESSION['recently_viewed']) > 0)
						{
							unset($recently_ids);
							$recently_ids = $_SESSION['recently_viewed'];
							$recently_ids[] = "11111111111111111";
							$recently_ids = array_map('intval', $recently_ids);
							$recently_ids = array_unique($recently_ids);
									
							$other_query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_type='offer' AND job_id<>'$job_id' AND job_id IN (".implode(",",$recently_ids).") AND status='active' LIMIT 4";
							$other_result = smart_mysql_query($other_query);
							$other_total = mysqli_num_rows($other_result);
	
							if ($other_total > 0) { 
				?>
				<div class="container">
				<div class="section">
					<div class="pull-left"><h3><i class="fa fa-history" style="color: #449d43"></i>  Recently Viewed Gigs</h3></div>
					<p class="pull-right"><a href="?recently_clear=1"><i class="fa fa-remove"></i> Clear All</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="<?php echo SITE_URL; ?>recently_viewed.php"><i class="fa fa-list"></i> See All</a></p>	
					<div style="clear: both"></div>
					<div class="row">
					<?php while ($other_row = mysqli_fetch_array($other_result)) { ?>
						<div class="col-md-3 text-center">
							
								<div class="item-info2" style="border: 1px solid #eee; border-radius: 8px; padding: 10px;">
									<div class="item-image-box2">
										<div class="text-center">
											<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $other_row['job_id']; ?>"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($other_row['image'] != "") ? $other_row['image'] : "no_image.png"; ?>" alt=""  height="150" style="margin: 5px 0;"></a>
										</div>
									</div>

									<div class="ad-info">
									  <?php echo GetUserAvatar($other_row['user_id'], 40); ?>
									  by <?php echo GetEmployerInfo($other_row['user_id'], $username = 1); ?><br>
									  
									  <?php if ($other_row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
										<a class="job_title" href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $other_row['job_id']; ?>">
											<h4>I will <?php if (strlen($other_row['title']) > 75) $job_title = substr($other_row["title"], 0, 70)."..."; else $job_title = $other_row["title"]; echo $job_title; ?></h4>
										</a>
									  <i class="fa fa-star fa-lg" style="color: #eec80c"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($other_row['job_id']); ?>)<!--<i class="fa fa-star-half"></i>-->
									  &nbsp;&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $other_row['job_id']; ?>&act=save"><i class="fa fa-heart jobfia_tooltip" title="Add to Saved" style="color: #ae3535"></i></a>
									  <hr>
									  <div class="pull-left"><a href="<?php echo SITE_URL; ?>order.php?id=<?php echo $other_row['job_id']; ?>" class="btn btn-success"><i class="fa fa-shopping-cart"></i> order</a></div>
									  <div class="pull-right" style="padding-top: 5px"><span style="font-size: 11px; color: #555; text-transform: uppercase; text-align: right">starting at</span> <span style="font-size: 19px; color: #4ea204"><?php echo DisplayMoney($other_row['price']); ?></span></div>
									 	<?php if ($other_row['country'] != "") { ?>					
											<div class="ad-meta">
												<ul>
													<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>San Francisco, CA, US </a></li>
												</ul>
											</div>
										<?php } ?>
										<div style="clear: both"></div>						
									</div><!-- ad-info -->
								</div><!-- item-info -->							
							
						</div>
					<?php } ?>
					</div>
				</div>
				</div>						
				<?php } } ?>

	<?php }else{ ?>
		<h1>Offer not found</h1>
		<div class="alert alert-info">Sorry, no offer found. <a href="<?php echo SITE_URL; ?>offers.php">All Offers</a></div>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>