<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_adm.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/vn.inc.php");
	require_once("./inc/adm_functions.inc.php");


	$today = date("Y-m-d");
	$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));

	$orders_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_orders WHERE date(created)='$today'"));
	$orders_today = $orders_today['total'];
	if ($orders_today > 0) $orders_today = "+" . $orders_today;

	$users_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE date(created)='$today'"));
	$users_today = $users_today['total'];
	if ($users_today > 0) $users_today = "+" . $users_today;

	$users_yesterday = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE date(created)='$yesterday'"));
	$users_yesterday = $users_yesterday['total'];

	$users_7days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE date_sub(curdate(), interval 7 day) <= created"));
	$users_7days = $users_7days['total'];

	$users_30days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE date_sub(curdate(), interval 30 day) <= created"));
	$users_30days = $users_30days['total'];

	$all_users = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users"));
	$all_users = $all_users['total'];

	$jobs_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE date(added)='$today'"));
	$jobs_today = $jobs_today['total'];
	if ($jobs_today > 0) $jobs_today = "+" . $jobs_today;

	$jobs_yesterday = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs WHERE date(added)='$yesterday'"));
	$jobs_yesterday = $jobs_yesterday['total'];

	$all_jobs = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_jobs"));
	$all_jobs = $all_jobs['total'];

	$all_users = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users"));
	$all_users = $all_users['total'];

	$all_workers = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE worker='1'"));
	$all_workers = $all_workers['total'];

	$premium_workers = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE worker='1' AND premium='1'"));
	$premium_workers = $premium_workers['total'];

	$all_employers = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_users WHERE worker!='1'"));
	$all_employers = $all_employers['total'];

	$all_orders = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM jobfia_orders"));
	$all_orders = $all_orders['total'];

	$title = "Admin Home";
	require_once ("inc/header.inc.php");

?>

	<h2><i class="fa fa-user-circle-o"></i> Admin Home</h2>

	<?php if (file_exists("../install.php")) { ?>
		<div class="alert alert-danger">You must now delete "install.php" from your server. Failing to delete this file is a serious security risk!</div>
	<?php } ?>


	 <table align="center" width="100%" border="0" cellpadding="2" cellspacing="2">
	 <tr>
		<td width="40%" align="left" valign="top">
			<table align="center" width="95%" border="0" cellpadding="6" cellspacing="2">
			<tr>
				<td nowrap="nowrap" align="left" valign="middle" class="tb2"><a href="http://jobfia.com" target="_blank" style="color: #000"><b>Jobfia</b></a> version:</td>
				<td align="right" valign="middle"><?php echo $jobfia_version; ?></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">License Key:</td>
				<td nowrap="nowrap" align="right" valign="middle"><?php echo GetSetting('license'); ?></td>
			</tr>
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>
		</td>
		<td width="30%" align="left" valign="top">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr>
				<td align="left" valign="middle" class="tb2">Users Today:</td>
				<td align="right" valign="middle" class="stat_s"><a href="users.php"><font color="#2F97EB"><?php echo $users_today; ?></font></a></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">Users Yesterday:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_yesterday; ?></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">Last 7 Days Users:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_7days; ?></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">Last 30 Days Users:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_30days; ?></td>
			</tr>
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>
		</td>
		<td width="30%" align="left" valign="top">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr>
				<td align="left" valign="middle" class="tb2">Orders Today:</td>
				<td align="right" valign="middle" class="stat_s"><a href="jobs.php"><font color="#2F97EB"><?php echo $orders_today; ?></font></a></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">Offers Orders:</td>
				<td align="right" valign="middle" class="stat_s"><a href="jobs.php"><font color="#2F97EB"><?php echo $all_orders; ?></font></a></td>
			</tr>							
			<tr>
				<td align="left" valign="middle" class="tb2">Today Jobs Posted:</td>
				<td align="right" valign="middle" class="stat_s"><a href="jobs.php"><font color="#2F97EB"><?php echo $jobs_today; ?></font></a></td>
			</tr>
			<tr>
				<td align="left" valign="middle" class="tb2">Yesterday Jobs Posted:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $jobs_yesterday; ?></td>
			</tr>
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<!--<h2>Site Stats</h2>-->
	<table id="stats" style="background: #F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
	<tr>
		<td width="16%" align="center" valign="top"><span class="stats"><?php echo $all_jobs; ?></span><i class="fa fa-briefcase fa-lg" style="color: #6da889"></i> jobs<td>
		<td width="16%" align="center" valign="top"><span class="stats"><?php echo GetDoneTasksTotal(); ?></span><i class="fa fa-check-circle fa-lg" style="color: #6da889"></i> tasks done<td>
		<td width="16%" align="center" valign="top"><span class="stats"><?php echo GetActiveJobsTotal(); ?></span><i class="fa fa-briefcase fa-lg" style="color: #6da889"></i> live jobs<td>
		<td width="16%" align="center" valign="top"><span class="stats"><?php echo $all_users; ?></span><i class="fa fa-users fa-lg" style="color: #6da889"></i> users<td>
		<td width="16%" align="center" valign="top"><span class="stats"><?php echo GetUsersOnline(); ?></span><i class="fa fa-users fa-lg" style="color: #6da889"></i> users online<td>
		<td width="16%" align="center" valign="top"><span class="stats" style="color: #5cb85c"><?php echo GetWorkersEarnTotal(); ?></span><i class="fa fa-money fa-lg" style="color: #6da889"></i> workers earned<td>
	</tr>
	</table><br><br>
	
	
			<?php
					$query1 = "SELECT *, DATE_FORMAT(added, '%d %b %Y <sup>%h:%i %p</sup>') AS date_added FROM jobfia_jobs ORDER BY added DESC LIMIT 10";
					$result1 = smart_mysql_query($query1);
					$total1 = mysqli_num_rows($result1);			
			?>
			<?php if ($total1 > 0) { ?><div class="pull-right" style="margin-top: 10px"><a class="badge" href="jobs.php"><i class="fa fa-list"></i> see all</a></div><?php } ?>
			<h3><i class="fa fa-briefcase" style="color: #5cb85c"></i> Latest Jobs</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table width="100%" align="center" class="table table-striped table-hover" style="border-bottom: 1px solid #f5f5f5" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="3%">&nbsp;</th>
                <th width="35%">Job</th>
                <th width="14%">Payment</th>
                <th width="20%">Created by</th>
				<th width="15%"><i class=" fa fa-clock-o"></i> Date/Time</th>
                <th width="12%">Status</th>
				<th width="8%">Actions</th>
              </tr>
			<?php if ($total1 > 0) { ?>
			<?php while ($row1 = mysqli_fetch_array($result1)) { $cc++; $total_earnings = 0; if ($row1['status'] == "confirmed") $total_earnings += $row1['job_price'];  ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
	              <td valign="middle" align="center"><i class="fa fa-briefcase" style="color: #6da889"></i></td>
                  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row1['job_id']; ?>">
						<?php if (strlen($row1['title']) > 75) $job_title = substr($row1["title"], 0, 70)."..."; else $job_title = $row1["title"]; echo $job_title; ?>
					</a>
				  </td>
				  <td valign="middle" align="left"><?php echo DisplayMoney($row1['price']); ?></td>
				  <td valign="middle" align="left"><?php echo GetUsername($row1['user_id'], $username = 1); ?></td>
				  <td valign="middle" align="left"><?php echo $row1['date_added']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<?php
							switch ($row1['status'])
							{
								case "active": echo "<span class='label label-success'>open</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;							
								case "declined": echo "<span class='label label-danger'><i class='fa fa-ban'></i> declined</span>"; break;
								case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
								default: echo "<span class='label label-primary'>".$row1['status']."</span>"; break;
							}
					?>
				  </td>
				  <td valign="middle" align="left">
						<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row1['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
				  </td>
                </tr>
			<?php } ?>
			<?php }else{ ?>
				<tr>
				<td colspan="7" align="center">
					<p align="center">No jobs at this time.</p>
				</td>
				</tr>
			<?php } ?>		
			</table>
			</div>	
	
	
			<?php
					$query2 = "SELECT *, DATE_FORMAT(added, '%d %b %Y <sup>%h:%i %p</sup>') AS date_complete FROM jobfia_job_tasks ORDER BY added DESC LIMIT 10";
					$result2 = smart_mysql_query($query2);
					$total2 = mysqli_num_rows($result2);			
			?>			
			<?php if ($total2 > 0) { ?><div class="pull-right" style="margin-top: 10px"><a class="badge" href="applicants.php"><i class="fa fa-list"></i> see all</a></div><?php } ?>
			<h3><i class="fa fa-users" style="color: #5cb85c"></i> Latest Completed Tasks</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" style="border-bottom: 1px solid #f5f5f5" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class=" fa fa-clock-o"></i> Date/Time</th>
				<th width="35%">Job</th>
				<th width="25%">Worker</th>
				<th width="22%">Proof</th>
                <th width="12%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php if ($total2 > 0) { ?>
			<?php while ($row2 = mysqli_fetch_array($result2)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row2['date_complete']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left"><i class="fa fa-briefcase" style="color: #6da889"></i> <a href="task_details.php?job_id=<?php echo $row2['job_id']; ?>&id=<?php echo $row2['job_task_id']; ?>"><?php echo $row2['job_title']; ?></a></td>
				  
                  <td nowrap="nowrap" valign="middle" align="left"><?php if ($row2['country_code'] != "") { ?><img src="<?php SITE_URL; ?>images/flags/<?php echo $row2['country_code']; ?>.png" width="16" height="11" /> <?php } ?> <?php echo GetWorkerInfo($row2['worker_id'], $username = 2); ?></td>
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
				  <td nowrap="nowrap" valign="middle" align="left"><a href="task_details.php?job_id=<?php echo $row2['job_id']; ?>&id=<?php echo $row2['job_task_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
		<?php }else{ ?>
				<tr>
				<td colspan="6" align="center">
					<p align="center">No applicants at this time.</p>
				</td>
				</tr>
		<?php } ?>
           </table>
           </div>	
	
	

			<?php
					$query3 = "SELECT *, DATE_FORMAT(created, '%d %b %Y <sup>%h:%i %p</sup>') AS order_date FROM jobfia_orders WHERE offer_id IN (SELECT job_id FROM jobfia_jobs WHERE job_type='offer') ORDER BY created DESC LIMIT 10";
					$result3 = smart_mysql_query($query3);
					$total3 = mysqli_num_rows($result3);			
			?>
			<?php if ($total3 > 0) { ?><div class="pull-right" style="margin-top: 10px"><a class="badge" href="offers.php"><i class="fa fa-list"></i> see all</a></div><?php } ?>	
			<h3><i class="fa fa-shopping-cart" style="color: #5cb85c"></i> Recent Orders</h3>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" style="border-bottom: 1px solid #f5f5f5" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class=" fa fa-clock-o"></i> Order Date</th>
				<!--<th width="15%">Reference ID reference_id</th>-->
				<th width="35%">Offer</th>
				<th width="25%">Customer</th>
				<th width="22%">Amount</th>
                <th width="12%">Status</th>
				<th width="10%">Actions</th>
              </tr>
              <?php if ($total3 > 0) { ?>
			  <?php while ($row3 = mysqli_fetch_array($result3)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row3['order_date']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>order_details.php?offer_id=<?php echo $row3['offer_id']; ?>&id=<?php echo $row3['order_id']; ?>"><?php echo $row3['offer_title']; ?></a></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo GetUsername($row3['user_id'], $username = 1); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo DisplayMoney($row3['amount']); ?></td>      
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($row3['status'])
						{
							case "request": echo "<span class='label label-default'>request</span>"; break;
							case "pending": echo "<span class='label label-warning'>in process</span>"; break;							
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
		<?php }else{ ?>
				<tr>
				<td colspan="6" align="center">
					<p align="center">No recent orders at this time.</p>
				</td>
				</tr>
		<?php } ?>
           </table>
           </div>				
			<br>	



<?php require_once ("inc/footer.inc.php"); ?>