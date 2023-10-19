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


	$results_per_page = RESULTS_PER_PAGE;
	$cc = 0;

	$job_id	= (int)$_GET['id'];

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;


	if (isset($_GET['act']) && $_GET['act'] != "" && isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_id = (int)$_GET['id'];
		
		$pagem = "updated";

		switch ($_GET['act'])
		{
			case "run":			$act_query = "UPDATE jobfia_jobs SET status='active' WHERE user_id='$userid' AND job_id='$job_id' AND job_type='offer' AND status='inactive'"; break;
			case "pause":		$act_query = "UPDATE jobfia_jobs SET status='inactive' WHERE user_id='$userid' AND job_id='$job_id' AND job_type='offer' AND status='active'"; break;
			case "delete":		$act_query = "DELETE FROM jobfia_jobs WHERE user_id='$userid' AND job_id='$job_id' AND job_type='offer' AND status='pending'"; $pagem="deleted"; break;
			case "save":		if (mysqli_num_rows(smart_mysql_query("SELECT * FROM jobfia_favorites WHERE user_id='$userid' AND favorite_id='$job_id' AND favorite_type='offer'")) == 0) $act_query = "INSERT INTO jobfia_favorites SET user_id='$userid', favorite_id='$job_id', favorite_type='offer', status='active', added=NOW()"; else $act_query = ""; $pagem="saved#saved_list"; break;
		}

		if ($act_query!= "" && smart_mysql_query($act_query))
		{
			header("Location: myoffers.php?msg=".$pagem);
			exit();
		}
	}

	$jobs_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE user_id='$userid' AND job_type='offer' ORDER BY status DESC, added DESC LIMIT $from, $results_per_page";
	$jobs_result = smart_mysql_query($jobs_query);
	$jobs_total = mysqli_num_rows($jobs_result);

	$total_result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_type='offer' AND user_id='$userid'");
	$total = mysqli_num_rows($total_result);

	// saved offers
	$saved_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_favorites WHERE user_id='$userid' AND favorite_type='offer' ORDER BY status DESC, added DESC LIMIT $from, $results_per_page";
	$saved_result = smart_mysql_query($saved_query);
	$saved_total = mysqli_num_rows($saved_result);

	$stotal_result = smart_mysql_query("SELECT * FROM jobfia_favorites WHERE favorite_type='offer' AND user_id='$userid'");
	$stotal = mysqli_num_rows($stotal_result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "My Offers";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>
	
	
<?php 
	
	if (isset($_GET['offer_id']) && isset($_GET['id']))
	{ 
		$order_id		= (int)$_REQUEST['id'];
		$offer_id		= (int)$_REQUEST['offer_id'];
		
		$order_result = smart_mysql_query("SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS order_date FROM jobfia_orders WHERE offer_id='$offer_id' AND order_id='$order_id' AND user_id='$userid' LIMIT 1");
		if (mysqli_num_rows($order_result) == 0)
		{
			header ("Location: myoffers.php");
			exit();
		}
		else
		{
			$order_row = mysqli_fetch_array($order_result);
			
		}
		
?>		
		<h2><i class="fa fa-file-text-o" style="color: #5cb85c"></i> Order Details</h2>
		
		
		<table class="table table-condensed table-striped">
		    <tbody>
		      <tr>
		        <td style="width: 120px">Order ID</td>
		        <td><?php echo $order_row['reference_id']; ?></td>
		      </tr>
		      <tr>
		        <td>Date</td>
		        <td><?php echo $order_row['order_date']; ?></td>
		      </tr>
		      <tr>
		        <td>Offer</td>
		        <td><?php echo $order_row['offer_title']; ?></td>
		      </tr>		      
		      <tr>
		        <td>Amount</td>
		        <td><?php echo DisplayMoney($order_row['amount']); ?></td>
		      </tr>
		      <tr>
		        <td>Status</td>
		        <td>
					<?php
						switch ($order_row['status'])
						{
							case "request": echo "<span class='label label-default'>request</span>"; break;
							case "pending": echo "<span class='label label-warning'>in process</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
							default: echo "<span class='label label-primary'>".$order_row['status']."</span>"; break;
						}
						if ($order_row['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$order_row['comment']."\"></i>";
						if ($order_row['status'] == "pending")  echo "<br><br><a class='btn btn-success' href='#'>got offer!</a>";
					?>			        
		        </td>
		      </tr>		      
		    </tbody>
		  </table>

		  <p align="center"><input class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" type="button"></p>	
		  
<?php }else{ ?>

	<div class="pull-right"><a href="<?php echo SITE_URL; ?>offer_create.php" class="btn btn-info">+ Post an Offer</a></div>

	<h1 class="brd"><i class="fa fa-list" style="color: #01a650"></i> My Offers <?php echo ($jobs_total > 0) ? "<sup class='label label-default'>".$jobs_total."</sup>" : ""; ?></h1>

		  <?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<h4>
					<i class="fa fa-check-circle-o fa-lg"></i> 
					<?php
						switch ($_GET['msg'])
						{
							case "added":	echo "Offer has been submitted"; break;
							case "updated": echo "Offers has been updated successfully"; break;
							case "deleted": echo "Offers has been deleted successfully"; break;
							case "saved": echo "Offer has been saved successfully"; break;
						}
					?>
				</h4>
			</div>
		<?php } ?>
		
		<?php if ($jobs_total > 0) { ?>
			<p>Here you can see your offers and sales stats.</p>
		<?php } ?>

			
			<?php if ($jobs_total > 0) { $cc = 0; ?>

			<!--<h3>All Offers</h3> Expiry Date
			<br>-->

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="8%">&nbsp;</th>
                <th width="35%">Title</th>
				<th width="10%">Price</th>
                <th width="10%">Available</th>
                <th width="10%"><i class="fa fa-eye"></i> Views</th>
                <th width="10%"><i class="fa fa-shopping-cart"></i> Sales</th>
                <th width="10%">Created</th>
				<th width="15%">Status</th>
				<th width="15%">Action</th>
              </tr>
			<?php while ($jobs_row = mysqli_fetch_array($jobs_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($jobs_row['image'] != "") ? $jobs_row['image'] : "no_image.png"; ?>" alt="" class="img-responsive" width="100" height="60" style="border-radius: 20%"></td>
                  <td valign="middle" align="left">
					<?php if ($jobs_row['featured'] == 1) { ?><span class="featured" alt="Featured Job" title="Featured Job"></span><?php } ?>
					<a href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $jobs_row['job_id']; ?>">
						I will <?php if (strlen($jobs_row['title']) > 75) $job_title = substr($jobs_row["title"], 0, 70)."..."; else $job_title = $jobs_row["title"]; echo $job_title; ?>
					</a>
					<?php if ($jobs_row['country'] != "") { ?><br><small><i class="fa fa-map-marker"></i> Location: <?php echo $jobs_row["country"]." ".$jobs_row["city"]; ?></small><?php } //dev ?>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($jobs_row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $jobs_row['positions']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo number_format($jobs_row['views']); ?></td>                  
                  <td nowrap="nowrap" valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $jobs_row['job_id']; ?>"><span class="badge"><?php echo GetOfferSalesTotal($jobs_row['job_id']); //$jobs_row['sales'] ?></span></a>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $jobs_row['date_added']; ?></td>
				  <td nowrap valign="middle" align="left">
					<?php
							switch ($jobs_row['status'])
							{
								case "active": echo "<span class='label label-success'>active</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaitng review</span>"; break;	
								case "inactive": echo "<span class='label label-default'>paused</span>"; break;
								case "declined": echo "<span class='label label-danger'>declined</span>"; break;
								case "complete": echo "<span class='label label-success'>complete</span>"; break;
								default: echo "<span class='label label-primary'>".$jobs_row['status']."</span>"; break;
							}

							if ($jobs_row['status'] == "declined" && $jobs_row['reason'] != "")
							{
								echo " <span class=\"jobfia_tooltip\" title=\"".$jobs_row['reason']."\"><i class='fa fa-info-circle'></i></span>";
							}
					?>
				  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $jobs_row['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					<?php if ($jobs_row['status'] == "active") { ?>
					&nbsp; <a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $jobs_row['job_id']; ?>&act=pause"><i class="fa fa-pause fa-lg jobfia_tooltip" title="Pause" style="color: #777"></i></a>
					<?php } ?>									
					<?php if ($jobs_row['status'] == "inactive") { ?>
						&nbsp; <a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $jobs_row['job_id']; ?>&act=run"><i class="fa fa-play fa-lg jobfia_tooltip" title="Start" style="color: #4bb218"></i></a> &nbsp;
					<?php }elseif ($jobs_row['status'] == "pending") { ?>
						<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $jobs_row['job_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #f03e3f"></i></a>
					<?php } ?>					
				  </td>
                </tr>
				<?php } ?>
				</table>
			</div>

				<?php echo ShowPagination("jobs",$results_per_page,"myoffers.php?","WHERE job_type='offer' AND user_id='$userid'"); ?>

			<?php }else{ ?>
				<div class="alert alert-info text-center">No offers have been posted yet. <a href="<?php echo SITE_URL; ?>offer_create.php">Post an Offer</a>.</div>
			<?php } ?>
			

			<?php
					$query3 = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS order_date FROM jobfia_orders WHERE user_id='$userid' ORDER BY created DESC";
					$result3 = smart_mysql_query($query3);
					$total3 = mysqli_num_rows($result3);			
			?>
			<br>
			<h2><i class="fa fa-shopping-cart" style="color: #5cb85c"></i> Purchased Offers</h2>
			<div style="clear: both"></div>
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class=" fa fa-clock-o"></i> Order Date</th>
				<th width="15%">Reference ID</th>
				<th width="30%">Offer</th>
				<th width="20%">Order Amount</th>
                <th width="15%">Status</th>
				<th width="10%">Actions</th>
              </tr>
              <?php if ($total3 > 0) { ?>
			  <?php while ($row3 = mysqli_fetch_array($result3)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td valign="middle" align="left"><?php echo $row3['order_date']; ?></td>
                  <td valign="middle" align="left"><?php echo $row3['reference_id']?></td>
                  <td valign="middle" align="left"><?php echo $row3['offer_title']; ?></td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($row3['amount']); ?></td>      
				  <td valign="middle" align="left">
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
				  <td valign="middle" align="left">
					  <a href="<?php echo SITE_URL; ?>myoffers.php?offer_id=<?php echo $row3['offer_id']; ?>&id=<?php echo $row3['order_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					  <?php if ($row3['status'] == "pending") { ?><a href="#" class="jobfia_tooltip" title="Got it!"><i class="fa fa-check-circle fa-lg"></i></a><?php } ?>
				  </td>
                </tr>
			<?php } ?>
		<?php }else{ ?>
				<tr>
				<td colspan="6" align="center">
					<p align="center">You have no purchased offers.</p>
				</td>
				</tr>
		<?php } ?>
           </table>
           </div>

			
			<a name="saved_list"></a>
			<?php if ($stotal > 0) { $cc = 0; ?>
			<br>
			<h2 class="brd"><i class="fa fa-bookmark-o" style="color: #01a650"></i> Saved Offers <?php /*echo ($stotal > 0) ? "<sup class='label label-default'>".$stotal."</sup>" : ""; */ ?></h2>
			
			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
	            <th width="8%">&nbsp;</th>
                <th width="35%">Title</th>
				<th width="10%">Price</th>
                <th width="10%">Created</th>
				<th width="15%"></th>
				<th width="15%">Action</th>
              </tr>
			<?php while ($jobs_row = mysqli_fetch_array($jobs_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($jobs_row['image'] != "") ? $jobs_row['image'] : "no_image.png"; ?>" alt="" class="img-responsive" width="100" height="60" style="border-radius: 20%"></td>
                  <td valign="middle" align="left">
					<?php if ($jobs_row['featured'] == 1) { ?><span class="featured" alt="Featured Job" title="Featured Job"></span><?php } ?>
					<a href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $jobs_row['job_id']; ?>">
						I will <?php if (strlen($jobs_row['title']) > 75) $job_title = substr($jobs_row["title"], 0, 70)."..."; else $job_title = $jobs_row["title"]; echo $job_title; ?>
					</a>
					<?php if ($jobs_row['country'] != "") { ?><br><small><i class="fa fa-map-marker"></i> Location: <?php echo $jobs_row["country"]." ".$jobs_row["city"]; ?></small><?php } //dev ?>
				  </td>
                  <td valign="middle" align="left"><?php echo DisplayMoney($jobs_row['price']); ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo $jobs_row['date_added']; ?></td>
				  <td nowrap valign="middle" align="left">
					<?php/*
							switch ($jobs_row['status'])
							{
								case "active": echo "<span class='label label-success'>active</span>"; break;
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> pending</span>"; break;	
								case "inactive": echo "<span class='label label-default'>paused</span>"; break;
								default: echo "<span class='label label-primary'>".$jobs_row['status']."</span>"; break;
							}*/
					?>
				  </td>
				  <td valign="middle" align="left">
					<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $jobs_row['job_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a>
					<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $jobs_row['job_id']; ?>&act=delete"><i class="fa fa-remove fa-lg jobfia_tooltip" title="Delete" style="color: #f03e3f"></i></a>					
				  </td>
                </tr>
				<?php } ?>
				</table>
			</div>

				<?php echo ShowPagination("favorites",$results_per_page,"myoffers.php?","WHERE favorite_type='offer' AND user_id='$userid'"); ?>			
			<?php } ?>

<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>