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

	$cc = 0;
	
	$query	= "SELECT *, DATE_FORMAT(last_login, '%d %b %Y %h:%i %p') AS last_login_date FROM jobfia_users WHERE user_id='$userid' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total	= mysqli_num_rows($result);

	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
	}
	else
	{
		header ("Location: logout.php");
		exit();
	}	

	// update user activity time
	UpdateUserActivity($userid);

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "My Account";
	
			
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
	
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<div class="row">
			<div class="col-md-9">

			<h1>My Account</h1>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
				<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "welcome": echo "Congratulations! Your account is now active and you are currently signed in!"; break;
					}

				?>
				</div>
			<?php } ?>


			<?php if (isset($_GET['allow']) && $_GET['allow'] != "") { ?>
				<div class="alert alert-warning">
					<i class="fa fa-warning fa-lg"></i> 
				<?php
					switch ($_GET['allow'])
					{
						case "worker": echo "Sorry, this page is available to workers only"; break;
						case "employer": echo "Sorry, this page is available to employers only"; break;
					}
				?>				
				</div>
			<?php } ?>
			
			<?php if (GetPendingTasksTotal(0, $userid) > 0) { ?>
				<div class="alert alert-warning">
					<?php echo $_SESSION['FirstName']; ?>, you have <a href="<?php echo SITE_URL; ?>myjobs.php"><span class="label label-warning"><?php echo GetPendingTasksTotal(0, $userid); ?></span></a> pending complete tasks for your jobs. Please review them as soon as possible. They will be auto confirmed by our system after <?php echo AUTO_COMFIRM_DAYS; ?> days.
				</div>
			<?php } ?>

				<h4 class="pull-left">Welcome, <a href="#"><b><?php echo $_SESSION['FirstName']; ?></b></a>!</h4>
				<?php if ($row['login_count'] > 0) { ?><h5 class="pull-right" style="color: #999; font-size: 12px">You last logged in at: <?php echo $row['last_login_date']; ?></h5><?php } ?>
				<div style="clear: both"></div>
				
				<div class="row" style="background: #f7f9f6; padding: 15px; border-radius: 10px">
					<div class="col-sm-2 col-xs-6 text-center" style="border-right: 1px solid #eee"><h3><a href="<?php echo SITE_URL; ?>myjobs.php" style="color: #6f6f6f"><?php echo GetEmployerJobsTotal($userid); ?></a></h3> JOBS POSTED</div>
					<div class="col-sm-2 col-xs-6 text-center" style="border-right: 1px solid #eee"><h3><a href="<?php echo SITE_URL; ?>myoffers.php" style="color: #6f6f6f"><?php echo GetUserOffersTotal($userid); ?></a></h3> OFFFERS</div>
					<div class="col-sm-3 col-xs-6 text-center" style="border-right: 1px solid #eee"><h3><a href="<?php echo SITE_URL; ?>jobs_finished.php" style="color: #6f6f6f"><?php echo GetWorkerJobsTotal($userid); ?></a></h3> JOBS FINISHED</div>
					<div class="col-sm-2 col-xs-6 text-center" style="border-right: 1px solid #eee"><h3><a href="<?php echo SITE_URL; ?>myoffers.php" style="color: #6f6f6f"><?php echo GetUserSalesTotal($userid); ?></a></h3> OFFER SALES</div>
					<div class="col-sm-3 col-xs-12 text-center"><h3><a href="<?php echo SITE_URL; ?>mypayments.php"><?php echo GetWorkerEarnTotal($userid); ?></a></h3> MONEY EARNED</div>
				</div>
				<br>


			<?php
					$query1 = "SELECT *, DATE_FORMAT(added, '%d %b %Y <sup>%h:%i %p</sup>') AS date_complete FROM jobfia_job_tasks WHERE worker_id='$userid' ORDER BY added DESC LIMIT 10";
					$result1 = smart_mysql_query($query1);
					$total1 = mysqli_num_rows($result1);			
			?>
			<?php if ($total1 > 0) { ?><div class="pull-right" style="margin-top: 10px"><a class="badge" href="<?php echo SITE_URL; ?>jobs_finished.php"><i class="fa fa-list"></i> see all</a></div>
			<h3><i class="fa fa-list" style="color: #5cb85c"></i> Latest Jobs Completed</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table width="100%" align="center" class="table table-striped table-hover" style="border-bottom: 1px solid #f5f5f5" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="3%">&nbsp;</th>
                <th width="35%">Job</th>
                <th width="17%">Payment</th>
				<th width="18%"><i class=" fa fa-clock-o"></i> Date/Time</th>
                <th width="12%">Status</th>
				<th width="8%">Actions</th>
              </tr>
			<?php while ($row1 = mysqli_fetch_array($result1)) { $cc++; $total_earnings = 0; if ($row1['status'] == "confirmed") $total_earnings += $row1['job_price'];  ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
	              <td valign="middle" align="center"><i class="fa fa-briefcase" style="color: #6da889"></i></td>
                  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>jobs_finished_details.php?id=<?php echo $row1['job_task_id']; ?>">
						<?php if (strlen($row1['job_title']) > 75) $job_title = substr($row1["job_title"], 0, 70)."..."; else $job_title = $row1["job_title"]; echo $job_title; ?>
					</a>
				  </td>
				  <td valign="middle" align="left"><?php echo DisplayMoney($row1['job_price']); ?></td>
				  <td valign="middle" align="left"><?php echo $row1['date_complete']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<?php
							switch ($row1['status'])
							{
								case "notcomplete": echo "<span class='label label-default'><i class='fa fa-exclamation-triangle'></i> not complete</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;							
								case "declined": echo "<span class='label label-danger'><i class='fa fa-ban'></i> declined</span>"; break;
								case "confirmed": echo "<span class='label label-success'><i class='fa fa-money'></i> paid</span>"; break;
								default: echo "<span class='label label-primary'>".$row1['status']."</span>"; break;
							}

							if ($row1['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row1['comment']."\"></i>";
					?>
				  </td>
				  <td valign="middle" align="left">
						<a href="<?php echo SITE_URL; ?>jobs_finished_details.php?id=<?php echo $row1['job_task_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					<?php if ($row1['status'] == "declined") { ?>
						<a href="<?php echo SITE_URL; ?>job_report.php?id=<?php echo $row1['job_id']; ?>"><i class="fa fa-flag fa-lg jobfia_tooltip" title="Report" style="color: #e77808"></i></a>
					<?php } ?>
					<?php if ($row1['status'] == "pending" && CANCEL_APPLICANTS == 1) { ?>
						<a href="<?php echo SITE_URL; ?>jobs_finished.php?id=<?php echo $row1['job_task_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #eb4444"></i></a>
					<?php } ?>
				  </td>
                </tr>
			<?php } ?>
				</table>
				</div>
			<?php } ?>



			<?php
					$query2 = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_complete FROM jobfia_job_tasks WHERE job_id IN (SELECT job_id FROM jobfia_jobs WHERE job_type='job' AND user_id='$userid') ORDER BY added DESC LIMIT 10";
					$result2 = smart_mysql_query($query2);
					$total2 = mysqli_num_rows($result2);			
			?>			
			<?php if ($total2 > 0) { ?>
			<div class="pull-right" style="margin-top: 10px"><a class="badge" href="<?php echo SITE_URL; ?>myjobs.php"><i class="fa fa-list"></i> see all</a></div>
			<h3><i class="fa fa-users" style="color: #5cb85c"></i> Latest Jobs Applicants</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class=" fa fa-clock-o"></i> Date/Time</th>
				<th width="35%">Job</th>
				<th width="25%">Worker</th>
				<th width="22%">Proof</th>
                <th width="12%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php while ($row2 = mysqli_fetch_array($result2)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row2['date_complete']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>job_applicants_details.php?job_id=<?php echo $row2['job_id']; ?>&id=<?php echo $row2['job_task_id']; ?>"><?php echo $row2['job_title']; ?></a></td>
				  
                  <td nowrap="nowrap" valign="middle" align="left"><?php if ($row2['country_code'] != "") { ?><img src="<?php SITE_URL; ?>images/flags/<?php echo $row2['country_code']; ?>.png" width="16" height="11" /> <?php } ?> <i class="fa fa-user-circle-o"></i> <?php echo GetWorkerInfo($row2['worker_id'], $username = 2); ?></td>
                  <td valign="middle" align="left">
	                  <?php if ($row2['proof_file'] != "") { ?><a href="<?php echo substr(SITE_URL,0,-1).JOBFIA_PROOFS_URL.$row2['proof_file']; ?>" target="_blank"><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i></a> <?php } ?>
					<?php
							if (strlen($row2['job_proof']) > 75)
							{
								$job_proof = substr($row2["job_proof"], 0, 70);
								$job_proof .= "... <a href='".SITE_URL."job_applicants_details.php?job_id=".$row2['job_id']."&id=".$row2['job_task_id']."><img src='".SITE_URL."images/icon_more.png' align='absmiddle' /></a>";
							}
							else
							{
								$job_proof = $row2["job_proof"];
							}

							if (@$filter) echo highlight_word($job_proof, $filter, "#EF9013"); else echo $job_proof;
					?>
                  </td>  
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($row2['status'])
						{
							case "notcomplete": echo "<span class='label label-default'>not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-money'></i> paid</span>"; break;
							default: echo "<span class='label label-primary'>".$row2['status']."</span>"; break;
						}
						if ($row2['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row2['comment']."\"></i>";
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>job_applicants_details.php?job_id=<?php echo $row2['job_id']; ?>&id=<?php echo $row2['job_task_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
           </table>
           </div>			
		<?php } ?>

			
			
			<?php
					$query3 = "SELECT *, DATE_FORMAT(created, '%d %b %Y <sup>%h:%i %p</sup>') AS order_date FROM jobfia_orders WHERE offer_id IN (SELECT job_id FROM jobfia_jobs WHERE job_type='offer' AND user_id='$userid') ORDER BY created DESC LIMIT 10";
					$result3 = smart_mysql_query($query3);
					$total3 = mysqli_num_rows($result3);			
			?>
			<?php if ($total3 > 0) { ?>
			<div class="pull-right" style="margin-top: 10px"><a class="badge" href="<?php echo SITE_URL; ?>myoffers.php"><i class="fa fa-list"></i> see all</a></div>	
			<h3><i class="fa fa-shopping-cart" style="color: #5cb85c"></i> Recent Orders</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class=" fa fa-clock-o"></i> Order Date</th>
				<!--<th width="15%">Reference ID reference_id</th>-->
				<th width="35%">Offer</th>
				<th width="25%">Customer</th>
				<th width="22%">Amount</th>
                <th width="12%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			  <?php while ($row3 = mysqli_fetch_array($result3)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row3['order_date']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row3['offer_title']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><i class="fa fa-user-circle-o"></i> <?php echo GetEmployerInfo($row3['user_id'], $username = 1); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo DisplayMoney($row3['amount']); ?></td>      
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($row3['status'])
						{
							case "request": echo "<span class='label label-default'>request</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
							default: echo "<span class='label label-primary'>".$row3['status']."</span>"; break;
						}
						if ($row3['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row3['comment']."\"></i>";
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>order_details.php?offer_id=<?php echo $row3['offer_id']; ?>&id=<?php echo $row3['order_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
	           </table>
	           </div>				
				<br>			
		<?php } ?>

			
			
			
				
				<?php /* ?>
				<h2><i class="fa fa fa-bookmark-o"></i> Get Started</h2>
					
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>jobs.php"><i class=" fa fa-briefcase"></i> Find Jobs</a>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>workers.php"><i class="fa fa-search"></i> Find Workers</a>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>job_create.php"><i class="fa fa-plus"></i> Submit a Job</a>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>mypayments.php"><i class="fa fa-list"></i> Payments</a>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-headphones"></i> Support <?php if (GetMemberMessagesTotal() > 0) { ?><span class="label label-default"><?php echo GetMemberMessagesTotal(); ?></label><?php } ?></a>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>invite.php"><i class="fa fa-paper-plane-o"></i> Refer a Friend</a>

				<br><br>
				<?php */ ?>

				
				<!--
				<h3><i class="fa fa-newspaper-o"></i> Latest News</h3>
				<?php
						$news_query = "SELECT *, DATE_FORMAT(added, '%e %M, %Y') AS news_date FROM jobfia_news WHERE status='active' ORDER BY added DESC LIMIT 5";
						$news_result = smart_mysql_query($news_query);
						$news_total = mysqli_num_rows($news_result);

						if ($news_total > 0)
						{
							while ($news_row = mysqli_fetch_array($news_result))
							{
				?>
						<div class="news_date "><?php echo $news_row['news_date']; ?></div>
						<b><?php echo $news_row['news_title']; ?></b><br/>
							<?php
								if (strlen($news_row['news_description']) > 450) 
									$news_description = substr($news_row['news_description'], 0, 450)."...<a class='seemore' href='".SITE_URL."news.php?id=".$news_row['news_id']."'>read more</a>";
								else
									$news_description = $news_row['news_description'];
							?>
						<p align="justify"><?php echo $news_description; ?></p>
						<?php } ?>

				<?php }else{ ?>
						<p>No news at this time.</p>
				<?php } ?>
				-->

			
			
			</div>
			<div class="col-md-3">


		<div class="abalance text-center">		
			<h1><i class="fa fa-user-circle-o fa-2x" style="color: #e6e9ed"></i></h1>
			<h4>Account Balance</h4>
			<img src="<?php echo SITE_URL; ?>images/icon_money.png"><br/>
			<h2 class="text-center" style="color: #01a650"><span><?php echo GetUserBalance($userid); ?></span></h2>
			<a href="<?php echo SITE_URL; ?>deposit.php" class="btn btn-success"><i class="fa fa-upload"></i> deposit</a> <a href="<?php echo SITE_URL; ?>withdraw.php" class="btn btn-info"><i class="fa fa-download"></i> withdraw</a>
			
			<?php if (PREMIUM_ACCOUNTS == 1) { ?>
				<br><br>
				<h4>Account Type</h4>
				<?php if ($row['premium'] == 1) { ?><span class="premium"><i class="fa fa-user-circle fa-lg"></i> Premium account</span><?php }else{ ?><i class="fa fa-user-circle fa-lg"></i> Basic account (<a href="<?php echo SITE_URL; ?>upgrade_account.php">upgrade</a>)<?php } ?>
			<?php } ?>
			
			<Br><Br><p>
			<?php if (GetMemberMessagesTotal() > 0) { ?>
				<a href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-message"></i> <span class="label label-info"><?php echo GetMemberMessagesTotal(); ?></span> New Messages</a>
			<?php } ?>					

			<?php if (GetJobInvitesTotal($userid) > 0) { ?>
			&nbsp;
					<a href="<?php echo SITE_URL; ?>jobs_invited.php"><span class="label label-info"><?php echo GetJobInvitesTotal($userid); ?></span> New Job Invitations</a>
			<?php } ?>
			</p>					
			
        </div>
        
        </div>
        </div>


<?php require_once ("inc/footer.inc.php"); ?>