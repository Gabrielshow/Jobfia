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
	if (SHOW_USERS_UNREG != 1)
	{
		require_once("inc/auth.inc.php");
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

	$query = "SELECT *, DATE_FORMAT(last_activity, '%M %e, %Y %h:%i %p') AS last_activity_date, DATE_FORMAT(created, '%M %e, %Y') AS signup_date FROM jobfia_users WHERE user_id='$user_id' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
		$PAGE_TITLE = $row['fname']." ".substr($row['lname'], 0, 1).".";
	}
	else
	{
		$PAGE_TITLE = "User Profile";
	}

	require_once ("inc/header.inc.php");

?>

	<?php if ($total > 0) { ?>


		<section class=" job-bg page  ad-profile-page">
		<div class="container">
			
			<div class="job-profile section">	
				<div class="user-profile">
					<div class="user-images">
						<img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="<?php echo AVATAR_WIDTH; ?>" height="<?php echo AVATAR_HEIGHT; ?>" alt="<?php echo $row['username']; ?>" title="<?php echo $row['username']; ?>" style="border-radius: 20%" border="0" />
						<?php if ($row['premium'] == 1) { ?><br><span class="premium"><i class="fa fa-user"></i> premium</span><?php } ?>
						
						<?php if (ifUserOnline($row['user_id'])) { ?><br><span class="online"><i class="fa fa-circle Blink"></i> online</span><?php } ?>											
					</div>
					<div class="user">
						<h2><a href="#"><?php echo $row['fname']." ".substr($row['lname'], 0, 1)."."; ?></a></h2>
						<h3><?php echo $row['username']; ?></h3>
						<?php if ($row['login_count'] > 0) { ?>
							<p style="color: #999">Last active: <?php echo relative_date(strtotime($row['last_activity'])); ?></p>
						<?php } ?>
							<!-- AddThis Button BEGIN -->
							<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
								 <a class="addthis_button_preferred_1"></a>
								 <a class="addthis_button_preferred_2"></a>
								 <a class="addthis_button_preferred_3"></a>
								 <a class="addthis_button_preferred_4"></a>
								 <a class="addthis_button_preferred_5"></a>
								 <a class="addthis_button_compact"></a>
							</div>
							<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=<?php echo ADDTHIS_ID; ?>"></script>
							<!-- AddThis Button END -->
					
							<?php if ($row['worker'] == 1) { ?>
								<a class="btn btn-info" style="margin-top: 5px" href="<?php echo SITE_URL; ?>hire.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-handshake-o"></i> Hire me</a>
								<a class="btn btn-success" style="margin-top: 5px" href="<?php echo SITE_URL; ?>myworkers.php?id=<?php echo $row['user_id']; ?>&act=add"><i class="fa fa-plus"></i> Add to My Workers</a>
								<?php if (SEND_BONUS == 1) { ?>
									<a class="btn btn-info" style="margin-top: 5px" href="<?php echo SITE_URL; ?>send_money.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-dollar"></i> Send Bonus</a>
								<?php } ?>
							<?php } ?>
							<a class="btn btn-danger" style="margin-top: 5px" href="<?php echo SITE_URL; ?>user_report.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-flag"></i> Report user</a>
							<br><br>					
					</div>

					<div class="favorites-user">
						<?php if ($row['worker'] == 1) { ?>
						<div class="favorites">
							<a href="#"><?php echo WorkerInFavoritesTotal($row['user_id']); ?><small>Favorites</small></a>
						</div>
						<?php } ?>
					</div>								
				</div><!-- user-profile -->
			</div><!-- ad-profile -->

			<div class="resume-content">

				<div class="personal-deatils section">
				    <div class="icons">
				        <i class="fa fa-id-card-o" aria-hidden="true"></i>
				    </div>  
				    <div class="personal-info">
				    	<h3>Personal Details</h3>
				        <ul class="address">
				            <li><h5>User ID </h5> <span>:</span><?php echo $row['user_id']; ?></li>
				            <?php if ($row['company'] != "") { ?><li><h5>Company </h5> <span>:</span><?php echo $row['company']; ?></li><?php } ?>
				            <li><h5>Location </h5> <span>:</span><?php echo ($row['city'] != "") ? $row['city'].", " : ""; ?><?php echo GetCountry($row['country_id']); ?></li>
				            <li><h5>Member Since </h5> <span>:</span><?php echo $row['signup_date']; ?></li>
				            <?php if ($row['login_count'] > 0) { ?>
				            	<li><h5>Last Activity </h5> <span>:</span><?php echo $row['last_activity_date']; ?> <!--(<?php echo relative_date(strtotime($row['last_activity'])); ?>)--></li>
				            <?php } ?>
				        </ul>    	
				    </div>                               
				</div><!-- personal-details -->	
				
				<?php if (GetWorkerSkills($row['user_id'])) { ?>
				<div class="special-qualification: section">
					<div class="icons">
					    <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
					</div>	
					<div class="qualification">
						<h3>Skills</h3>
						<?php echo GetWorkerSkills($row['user_id']); ?>
					</div>				
				</div>
				<?php } ?>			

		
				<?php if ($row['about'] != "") { ?>
				<div class="career-objective section">
			        <div class="icons">
			            <i class="fa fa-black-tie" aria-hidden="true"></i>
			        </div>   
			        <div class="career-info">
			        	<h3>About Me</h3>
			        	<p><span><?php echo $row['about']; ?></span></p>
			        </div>                                 
				</div><!-- about-me -->
				<?php } ?>

				<div class="row">
					<div class="col-md-6">
						
				<div class="personal-deatils section">
				    <div class="icons">
				        <i class="fa fa-user-o" aria-hidden="true"></i>
				    </div>  
				    <div class="personal-info">
				    	<h3>Worker Statistics</h3>
				        <ul class="address">
							<?php if (GetWorkerJobsTotal($row['user_id']) > 0) { ?><li><h5>Rating </h5> <span>:</span> <?php echo GetWorkerRating($row['user_id']); ?></li><?php } ?>
							<li><h5>Tasks Complete </h5> <span>:</span> <?php echo GetWorkerJobsTotal($row['user_id']); ?></li>
							<li><h5>Satisfied </h5> <span>:</span> <?php echo GetWorkerPaidJobs($row['user_id']); ?></li>
							<li><h5>Not Satisfied </h5> <span>:</span> <?php echo GetWorkerDeclinedJobs($row['user_id']); ?></li>
							<li><h5>Average Job Price </h5> <span>:</span> <?php echo GetWorkerJobPrice($row['user_id']); ?></li>
							<?php if ($row['rate'] != "0.0000") { ?><li><h5>Min Job Price </h5> <span>:</span> <?php echo DisplayMoney($row['rate']); ?></li><?php } ?>
							<li><h5>Total Earned </h5> <span>:</span> <?php echo GetWorkerEarnTotal($row['user_id']); ?></li>
				        </ul>    	
				    </div>                               
				</div><!-- personal-deatils -->	
				
					</div>
					<div class="col-md-6">				
				
				<div class="personal-deatils section">
				    <div class="icons">
				        <i class="fa fa-user-circle-o" aria-hidden="true"></i>
				    </div>  
				    <div class="personal-info">
				    	<h3>Employer Statistics</h3>
				        <ul class="address">
							<li><h5>Rating </h5> <span>:</span> <?php echo GetEmployerRating($row['user_id']); ?></li>
							<li><h5>Jobs Posted </h5> <span>:</span> <?php echo GetEmployerJobsTotal($row['user_id']); ?></li>
							<li><h5>Average Job Price </h5> <span>:</span> <?php echo GetEmployerJobPrice($row['user_id']); ?></li>
							<li><h5>Total Spend </h5> <span>:</span> <?php echo GetEmployerSpendTotal($row['user_id']); ?></li>
							<li>&nbsp;</li>
							<li>&nbsp;</li>
							<li>&nbsp;</li>
				        </ul>    	
				    </div>                               
				</div><!-- personal-deatils -->	
				
					</div>
				</div>				


				<?php
					// show employer's latest active jobs //
					$other_query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_type='job' AND user_id='".(int)$row['user_id']."' AND status='active' ORDER BY added DESC LIMIT 7";
					$other_result = smart_mysql_query($other_query);
					$other_total = mysqli_num_rows($other_result);

					if ($other_total > 0) { 
				?>
				<div class="educational-background section">
			        <div class="icons">
				        <i class="fa fa-briefcase" aria-hidden="true"></i>
			        </div>				
					<div class="educational-info">
						<h3>Latest Open Jobs</h3>
						<ul>
							<?php while ($other_row = mysqli_fetch_array($other_result)) { ?>
							<li>
								<h4><a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $other_row['job_id']; ?>"><?php echo $other_row['title']; ?></a></h4>
								<ul>
									<li style="width: 130px">Reward: <span><?php echo DisplayMoney($other_row['price']); ?></span></li>
									<li style="width: 130px">Job Done: <span><?php echo GetJobWorkersTotal($other_row['job_id']); ?> / <sup><?php echo $other_row['positions']; ?></span></li>
									<li style="width: 160px">Success Rate: <span><?php echo GetJobSuccess($other_row['job_id']); ?></span></li>
									<li style="width: 190px">Started: <span><?php echo relative_date(strtotime($other_row['added'])); ?></span></li>
								</ul>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
				

				<!-- //dev
				<div class="educational-background section">
			        <div class="icons">
				        <i class="fa fa-comments-o" aria-hidden="true"></i>
			        </div>				
					<div class="educational-info">
						<h3>Reviews</h3>
					</div>
				</div>-->
				

				<?php if ($row['worker'] == 1) { ?>				
				<div class="buttons">
					<a href="<?php echo SITE_URL; ?>myworkers.php?id=<?php echo $row['user_id']; ?>&act=add" class="btn">+ Add to My Workers</a>
				</div>
				<?php } ?>
				<!-- //dev
				<div class="download-button resume">
					<a href="#" class="btn"><i class="fa fa-envelope"></i> Contact Me</a>
				</div>
				-->
			</div><!-- resume-content -->						
		</div><!-- container -->
	</section><!-- ad-profile-page -->




	<?php }else{ ?>
		<h1>User not found</h1>
		<div class="alert alert-warning">
			<h4>Sorry, no user found.</h4>
			<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>"><i class="fa fa-angle-left"></i> Go Back</a></p>
		</div>
		
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>