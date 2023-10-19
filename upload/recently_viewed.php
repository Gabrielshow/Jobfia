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
	require_once("inc/pagination.inc.php");


	$where = " job_type='offer' AND ";


	$total = 0;
	
	if (isset($_GET['recently_clear']) && $_GET['recently_clear'] == 1) unset($_SESSION['recently_viewed']);
	
	if (is_array($_SESSION['recently_viewed']) && count($_SESSION['recently_viewed']) > 0)
	{
							unset($$recently_ids);
							$recently_ids = $_SESSION['recently_viewed'];
							$recently_ids[] = "11111111111111111";
							$recently_ids = array_map('intval', $recently_ids);
							$recently_ids = array_unique($recently_ids);

	
		$where .= " status='active'";
	
		$query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_type='offer' AND job_id<>'$job_id' AND job_id IN (".implode(",",$recently_ids).") AND status='active' LIMIT 10";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Recenty Viewed Offers";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<?php if (isLoggedIn() || SHOW_JOBS_UNREG == 1) { ?>

		<h1><i class="fa fa-history" style="color: #01a650"></i>  <?php echo $PAGE_TITLE; ?></h1>
			
		<?php if ($total > 0) { ?>
			
			<p>Here you see your recently viewed offers.</p>

			<div class="row">
			<p class="pull-right"><a href="?recently_clear=1"><i class="fa fa-remove"></i> Clear All</a></p>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<div class="col-md-3 text-center">
							<div class="job-ad-item <?php if ($row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
								<div class="item-info2" style="border: 1px solid #eee; border-radius: 8px; padding: 10px;">
									<div class="item-image-box2">
										<div class="text-center">
											<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $row['job_id']; ?>"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="" height="150" style="margin: 5px 0;"></a>
										</div>
									</div>

									<div class="offer-info2 text-center">
									  <?php echo GetUserAvatar($row['user_id'], 40); ?>
									  by <?php echo GetEmployerInfo($row['user_id'], $username = 1); ?><br>
									  
									  <?php if ($row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
										<a class="job_title" href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $row['job_id']; ?>">
											<h4>I will <?php if (strlen($row['title']) > 75) $job_title = substr($row["title"], 0, 70)."..."; else $job_title = $row["title"]; echo $job_title; ?></h4>
										</a>
									  <i class="fa fa-star fa-lg" style="color: #eec80c"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)<!--<i class="fa fa-star-half"></i>-->
									  &nbsp;&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $row['job_id']; ?>&act=save"><i class="fa fa-heart jobfia_tooltip" title="Add to Saved" style="color: #ae3535"></i></a>
									  <hr>
									  <div class="pull-left"><a href="<?php echo SITE_URL; ?>order.php?id=<?php echo $row['job_id']; ?>" class="btn btn-success"><i class="fa fa-shopping-cart"></i> order</a></div>
									  <div class="pull-right" style="padding-top: 5px"><span style="font-size: 11px; color: #555; text-transform: uppercase; text-align: right">starting at</span> <span style="font-size: 19px; color: #4ea204"><?php echo DisplayMoney($row['price']); ?></span></div>
									  <div style="clear: both"></div>
									 	<?php if ($row['country'] != "") { ?>					
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
			</div>
			
			<?php } ?>

					<?php
							//$params = "";
							//echo ShowPagination("jobs",$results_per_page,"recently_viewed.php?".$params."column=$rrorder&order=$rorder&show=$results_per_page&","WHERE ".$where);
					?>

			<?php }else{ ?>
					<div class="alert alert-info text-center">You have not seen any offers yet.</div>
			<?php } ?>

	<?php }else{ ?>
		<h1><i class=" fa fa-list"></i> Recently Viewed Offers</h1>
		<div class="alert alert-info text-center"><i class="fa fa fa-eye-slash fa-5x"></i><br> You need <a href="<?php echo SITE_URL; ?>signup.php">signup</a> or <a href="<?php echo SITE_URL; ?>login.php">login</a> to see offers list.</div>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>