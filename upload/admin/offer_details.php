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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/adm_functions.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pn			= (int)$_GET['pn'];
		$job_id		= (int)$_GET['id'];

		if (isset($_GET['action']) && $_GET['action'] == "approve") ApproveJob($job_id);
		if (isset($_GET['action']) && $_GET['action'] == "decline") DeclineJob($job_id);

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_added, DATE_FORMAT(updated, '%d %b %Y %h:%i %p') AS date_updated, DATE_FORMAT(reviewed, '%d %b %Y %h:%i %p') AS date_reviewed FROM jobfia_jobs WHERE job_id='$job_id'";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Offer Details";
	require_once ("inc/header.inc.php");

?>

	 <?php if ($total > 0) {
	
		 $row = mysqli_fetch_array($result);

	 ?>

		<h2><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="Image" width="70" height="50"> Offer Details - <span style="color: #777">I will</span> <?php echo $row['title']; ?></h2>

		<table width="100%" style="border: 1px dotted #F5F5F5;" cellpadding="2" cellspacing="2" border="0" align="center">
		<tr>
			<td height="80" width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num" style="color: #5cb85c"><?php echo DisplayMoney($row['price']); ?></span> <i class="fa fa-money fa-lg"></i> Price</td>
			<?php if ($row['duration'] != "") { ?><td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo ConvertDuration($row['duration']); ?></span> <i class="fa fa-clock-o fa-lg"></i> Delivery Time</td><?php } ?>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo $row['positions']; ?></span> <i class="fa fa-comments-o fa-lg"></i> Feedbacks</td>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><?php echo GetJobAvailablePositions($row['job_id']); ?></span> <i class="fa fa-users fa-lg"></i> Available Items</td>
			<td width="20%" bgcolor="#F7F7F7" align="center" style="border-right: 1px solid #FFF"><span class="num"><a href="offer_orders.php?id=<?php echo $row['job_id']; ?>"><?php echo GetOfferSalesTotal($row['job_id']); ?></a> <sup><?php echo GetOfferSalesTotal($row['job_id'], $today = 1); ?></sup></span> <i class="fa fa-shopping-cart fa-lg"></i> Orders <sup>today</sup>
							
			</td>
		</tr>
		</table>

		<div style="position: absolute; right: 10px; text-align: right; padding: 5px 10px;">
			<?php if ($row['homepage'] == 1) { ?><img src="images/icons/home.png" align="absmiddle" /> <span style="color: #777">homepage offer</span><br/><?php } ?>
			<?php if ($row['featured'] == 1) { ?><img src="images/icons/featured.png" align="absmiddle" /> <span style="color: #ffad16">featured offer</span><br/><?php } ?>
			<?php if ($row['highlighted'] == 1) { ?><span style="color: #71B4DD">highlighted offer</span><br/><?php } ?>
		</div>

		
        <table style="background: #FCFCFC;border-bottom: 2px solid #F7F7F7" width="100%" cellpadding="3" cellspacing="6" border="0" align="center">
			<tr>
				<td width="150" valign="middle" align="left" class="tb1"><br>Title:</td>
				<td align="left" valign="middle"><br><?php echo $row['title']; ?></td>
			</tr>
			<tr>
				<td width="150" valign="middle" align="left" class="tb1">Rating:</td>
				<td align="left" valign="middle">
					<i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star-half" style="color: #eec80c"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)
				</td>
			</tr>			
			<tr>
				<td width="150" valign="middle" align="left" class="tb1">Created by:</td>
				<td align="left" valign="middle"><?php echo GetUsername($row['user_id'], 1); ?></td>
			</tr>
			<?php if (GetJobCategory($row['job_id']) != "---") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Category:</td>
				<td align="left" valign="middle"><?php echo GetJobCategories($row['job_id']); ?></td>
			</tr>
			<?php } ?>
			<!--
			<tr>
				<td valign="middle" align="left" class="tb1">Location:</td>
				<td align="left" valign="middle"><?php if ($row['worldwide'] == 1) echo "<img src='images/icons/worldwide.png' align='absmiddle' /> International (All Workers)"; else echo GetJobCountriesList($row['job_id']); ?></td>
			</tr>
			-->
			<tr>
				<td valign="middle" align="left" class="tb1">Description:</td>
				<td align="left" valign="middle">
					<div style="width: 97%; min-height: 80px; background: #FFFFFF; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px;">
					<?php 
						$desc = stripslashes($row['description']);
						$desc = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '<a href="'.SITE_URL.'out.php?url=$0" target="_blank" class="external" rel="nofollow">$0</a>', $desc);
						echo $desc;
					?>
					</div>
				</td>
			</tr>
			<?php if ($row['tags'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Tags:</td>
				<td align="left" valign="middle"><?php echo $row['tags']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['views'] > 0) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Views:</td>
				<td align="left" valign="middle"><i class="fa fa-eye"></i> <?php echo number_format($row['views']); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Posted:</td>
				<td align="left" valign="middle"><?php echo $row['date_added']; ?></td>
			</tr>
			<?php if ($row['updated'] != "0000-00-00 00:00:00") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Updated:</td>
				<td align="left" valign="middle"><?php echo $row['date_updated']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['reviewed'] != "0000-00-00 00:00:00") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Reviewed:</td>
				<td align="left" valign="middle"><?php echo $row['date_reviewed']; ?></td>
			</tr>
			<?php } ?>
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td align="left" valign="middle">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='label label-success'>active</span>"; break;
							case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
							case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "complete": echo "<span class='label label-success'>complete</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}

						if ($row['status'] == "inactive" && $row['paused'] != "0000-00-00 00:00:00")
						{
							echo "<span class=\"jobfia_tooltip\" title=\"auto paused by daily tasks limit, will run automatically tomorrow\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}
					?>
				</td>
            </tr>
			<?php if ($row['status'] == "declined" && $row['reason'] != "") { ?>
            <tr>
				<td valign="middle" align="left" class="tb1">Decline Reason:</td>
				<td align="left" valign="top"><?php echo $row['reason']; ?></td>
            </tr>
			<?php } ?>
          </table>      
          
          	
          	<?php 
				$oquery = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS order_date FROM jobfia_orders WHERE offer_id='$job_id' ORDER BY created DESC LIMIT 10";
				
				$oresult = smart_mysql_query($oquery);
				$ototal = mysqli_num_rows($oresult);   
	?>      	
          	<br>		
			<h3 class="brd"><i class="fa fa-shopping-cart" style="color: #5cb85c"></i> Recent 10 Orders</h3>
			
			<?php if ($ototal > 0) { ?>
<form id="form2" name="form2" method="post" action="#orders">
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%">Order Date</th>
				<th width="15%">Reference ID</th>
				<th width="20%">User</th>
				<th width="20%">Comment</th>
				<th width="10%">Amount</th>
                <th width="10%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php while ($orow = mysqli_fetch_array($oresult)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $orow['order_date']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $orow['reference_id']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo GetWorkerInfo($orow['user_id'], 2); ?></td>
                  <td valign="middle" align="left"></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php echo DisplayMoney($orow['amount']); ?></td>      
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($orow['status'])
						{
							case "notcomplete": echo "<span class='label label-default'>not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'>in process</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'>paid</span>"; break;
							default: echo "<span class='label label-primary'>".$orow['status']."</span>"; break;
						}
						if ($orow['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$orow['comment']."\"></i>";
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="order_details.php?offer_id=<?php echo $orow['offer_id']; ?>&id=<?php echo $orow['order_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
				</table>
				<hr>
			<?php }else{ ?>
				<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> This offer has no recent orders.</div>
			<?php } ?>

			<p align="center">
			<?php if ($row['status'] == "pending" && $row['reviewed'] == "0000-00-00 00:00:00") { ?> 
				<a class="btn btn-success" style="margin:0; background:#65cf00;" href="offer_details.php?id=<?php echo $job_id ?>&pn=<?php echo $pn; ?>&action=approve">Approve Offer</a>
				<a class="btn btn-danger" style="margin:0; background:#F91F1B;" href="#" onclick="if (confirm('Are you sure you really want to decline this offer?') )location.href='offer_details.php?id=<?php echo $job_id; ?>&pn=<?php echo $pn; ?>&action=decline'">Decline</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
				<input type="button" class="btn btn-success" name="edit" value="Edit Offer" onClick="javascript:document.location.href='offer_edit.php?id=<?php echo $row['job_id']; ?>&page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='offers.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-danger" style="position: absolute; right: 10px;" name="delete" value="Delete Offer" onclick="if (confirm('Are you sure you really want to delete this offer?') )location.href='job_delete.php?id=<?php echo $row['job_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
			</p>


	  <?php }else{ ?>
			<h2>Offer Details</h2>
			<div class="alert alert-info">Sorry, no offer found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>