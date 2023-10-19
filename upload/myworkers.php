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
	require_once("inc/pagination.inc.php");

	$results_per_page = 10;
	$cc = 0;
	
	//workers_group //dev WORKER GROUP Create A Group // Group Name	Total Worker(s)	*Delete Group // hfghhf	0  	// Delete Group and Workers  

	$worker_id	= (int)$_GET['id'];

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	if (isset($_GET['act']) && $_GET['act'] == "add" && $worker_id != $userid)
	{
		$check_query = smart_mysql_query("SELECT * FROM jobfia_my_workers WHERE employer_id='$userid' AND worker_id='$worker_id' LIMIT 1");
		$check_query2 = smart_mysql_query("SELECT * FROM jobfia_users WHERE user_id='$worker_id' AND user_id<>'$userid' AND status='active' LIMIT 1");
		
		if (mysqli_num_rows($check_query) == 0 && mysqli_num_rows($check_query2) == 1)
		{
			smart_mysql_query("INSERT INTO jobfia_my_workers SET employer_id='$userid', worker_id='$worker_id', status='active', added=NOW()");
		}

		header("Location: myworkers.php?msg=added");
		exit();
	
	}
	else if (isset($_GET['act']) && $_GET['act'] == "del")
	{
		$del_query = "DELETE FROM jobfia_my_workers WHERE employer_id='$userid' AND worker_id='$worker_id'";
		if (smart_mysql_query($del_query))
		{
			header("Location: myworkers.php?msg=deleted");
			exit();
		}
	}

	$query = "SELECT jobfia_my_workers.*, jobfia_users.*, DATE_FORMAT(jobfia_users.last_activity, '%b %e, %Y') AS last_activity_date FROM jobfia_my_workers jobfia_my_workers, jobfia_users jobfia_users WHERE jobfia_my_workers.employer_id='$userid' AND jobfia_my_workers.worker_id=jobfia_users.user_id AND jobfia_users.status='active' ORDER BY jobfia_users.satisfied_tasks DESC, jobfia_my_workers.added DESC LIMIT $from, $results_per_page";
	$result = smart_mysql_query($query);
	$myworkers_total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "My Workers";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	
?>
	
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<h1 class="brd"><i class="fa fa-users" style="color: #01a650"></i> My Workers <?php echo ($myworkers_total > 0) ? "<sup class='label label-default'>".$myworkers_total."</sup>" : ""; ?></h1>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<i class="fa fa-check-circle-o fa-lg"></i> 
				<?php
					switch ($_GET['msg'])
					{
						case "added": echo "Worker has been added to your workers list!"; break;
						case "deleted": echo "Worker has been deleted from your workers list!"; break;
					}
				?>
			</div>
		<?php } ?>


	<?php if ($myworkers_total > 0) { ?>

		<p>Below you can find your favorite workers.</p>

			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
				<th width="10%">&nbsp;</th>
				<th width="40%"><i class="fa fa-user-circle-o"></i> Worker</th>
				<th width="15%"><i class="fa fa-star" style="color: #5cb85c"></i> Rating</th>
				<th width="15%">Last Active</th>
				<th width="15%">Actions</th>
            </tr>
		<?php while ($row = mysqli_fetch_array($result)) { $cc++ ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
              <td nowrap="nowrap" valign="middle" align="center">
				<img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="<?php echo AVATAR_WIDTH; ?>" height="<?php echo AVATAR_HEIGHT; ?>" alt="" class="imgs" border="0" />
				<?php if (isPremium($row['worker_id'])) { ?><br/><span class="premium"><i class="fa fa-user"></i> premium</span><br><?php } ?>
				<?php $user_online = 0; if (ifUserOnline($row['worker_id'])) { $user_online = 1; ?><br/><span class="online"><i class="fa fa-circle Blink"></i> online</span><?php } ?>				
				<?php echo GetWorkerRating($row['worker_id'], $show_stars = 1); ?>
              </td>
              <td nowrap="nowrap" valign="middle" align="left">
				<a href="user_profile.php?id=<?php echo $row['worker_id']; ?>"><h4><?php echo $row['username']; ?></h4></a>
				<b><?php echo $row['fname']." ".$row['lname']; ?></b><br>
				<?php echo GetCountry($row['country_id']); ?>
              </td>
              <td nowrap="nowrap" valign="middle" align="left"><?php echo GetWorkerRating($row['worker_id']); ?></td>
              <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 10px;">
					<?php if ($user_online == 1) { ?>
	                	<span class="online"><i class="fa fa-circle Blink"></i> online</span>
					<?php }elseif ($row['login_count'] > 0) { ?>
					<?php /*echo $row['last_activity_date'];*/ ?><?php echo relative_date(strtotime($row['last_activity'])); ?>
				<?php } ?>				  
			  </td>
              <td nowrap="nowrap" valign="middle" align="left" style="line-height: 24px">
				<a class="badge" style="font-size: 13px" href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $row['worker_id']; ?>"><i class="fa fa-id-card"></i> view profile</a> 
				<?php if (SEND_BONUS == 1) { ?><br/><a class="badge" style="background: #2995c4; font-size: 13px" href="<?php echo SITE_URL; ?>send_money.php?id=<?php echo $row['worker_id']; ?>"><i class="fa fa-dollar"></i> send bonus</a><?php } ?>
				<br/><a class="badge" style="background: #b84e4e; font-size: 13px" href="#" onclick="if (confirm('Are you sure you really want to delete this worker from your workers list?') )location.href='<?php echo SITE_URL; ?>myworkers.php?act=del&id=<?php echo $row['worker_id']; ?>'" title="Delete"><i class="fa fa-remove"></i> delete</a>
              </td>
            </tr>
		<?php } ?>
			</table>
			</div>

				<?php echo ShowPagination("my_workers",$results_per_page,"myworkers.php?","WHERE employer_id='$userid'"); ?>

    <?php }else{ ?>
			<div class="alert alert-info text-center">You do not have favorite workers yet.</div>
    <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>