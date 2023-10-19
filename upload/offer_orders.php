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


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;

	$cc = 0;


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$job_id = (int)$_REQUEST['id'];
		
		// check if viewing job poster //
		$job_result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_id='$job_id' AND user_id='$userid' LIMIT 1");
		if (mysqli_num_rows($job_result) == 0)
		{
			header ("Location: index.php");
			exit();
		}
		else
		{
			$job_row = mysqli_fetch_array($job_result);
			$job_id = $job_row['job_id'];
		}
		//////////////////////////////////
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}


	if (isset($_POST['action']) && $_POST['action'] == "modify")
	{
		$ids_arr	= array();
		$ids_arr	= $_POST['id_arr'];

		if (count($ids_arr) > 0)
		{
			foreach ($ids_arr as $v)
			{
				$task_id = (int)$v;
				
				switch($_POST['mark_as'])
				{
					case "confirm":		ConfirmTask($task_id); break;
					case "decline":		DeclineTask($task_id); break;
					case "incomplete":	NotCompleteTask($task_id); break;
				}
			}

			header("Location: offer_orders.php?msg=updated&id=".$job_id);
			exit();
		}	
	}

	////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "user_id": $rrorder = "user_id"; break;
				case "added": $rrorder = "added"; break;
				case "status": $rrorder = "status"; break;
				default: $rrorder = "added"; break;
			}
		}
		else
		{
			$rrorder = "added";
		}

		if (isset($_GET['order']) && $_GET['order'] != "")
		{
			switch ($_GET['order'])
			{
				case "asc": $rorder = "asc"; break;
				case "desc": $rorder = "desc"; break;
				default: $rorder = "desc"; break;
			}
		}
		else
		{
			$rorder = "desc";
		}

		if (isset($_GET['filter']) && $_GET['filter'] != "")
		{
			$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
			$filter_by = " AND (job_proof LIKE '%$filter%') ";
		}
	//////////////////////////////////////////////////

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_orders WHERE offer_id='$job_id' $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
	
	$total_result = smart_mysql_query("SELECT * FROM jobfia_orders WHERE offer_id='$job_id' $filter_by ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Offer Orders";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	    <ol class="breadcrumb breadcrumb-arrow">
			<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
			<li><a href="<?php echo SITE_URL; ?>myoffers.php">My Offers</a></li>
			<li class="active"><span>Offer Orders</span></li>
		</ol>
		
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<div style="text-align: right"><a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $job_id; ?>" target="_blank">view offer details <i class="fa fa-external-link"></i></a></div>

		<!--<h1><?php echo $job_row['title']; ?></h1>-->
		
		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success alert-dismissible fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<i class="fa fa-check-circle-o fa-lg"></i> 
			<?php
				switch ($_GET['msg'])
				{
					case "updated": echo "Tasks have been successfully updated"; break;
				}
			?>
		</div>
		<?php } ?>

		<?php if (isset($allerrors)) { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>


		<?php if ($total > 0) { ?>
		<table width="100%" cellpadding="3" cellspacing="4" border="0" align="center">
		<tr>
			<td height="80" width="20%" bgcolor="#F7F7F7" style="border-right: 1px solid #FFF" align="center"><span class="num"><?php echo DisplayMoney($job_row['price']); ?></span> <i class="fa fa-money fa-2x"></i> Price</td>			
			<?php if ($job_row['duration'] != "") { ?><td width="20%" bgcolor="#F7F7F7" style="border-right: 1px solid #FFF" align="center"><span class="num"><?php echo ConvertDuration($job_row['duration']); ?></span> <i class="fa fa-clock-o fa-2x"></i> Delivery Time</td><?php } ?>			
			<td width="20%" bgcolor="#F7F7F7" style="border-right: 1px solid #FFF" align="center"><span class="num"><?php echo $job_row['positions']; ?></span> <i class="fa fa-list fa-2x"></i> Available Items</td>
			<td width="20%" bgcolor="#F7F7F7" style="border-right: 1px solid #FFF" align="center"><span class="num"><?php echo GetOfferSalesTotal($job_row['job_id']); ?> <span style="color: #FFF; background: #F4C869; padding: 5px 11px; border-radius: 5px" class="jobfia_tooltip" title="awaiting review"><?php echo GetOfferSalesTotal($job_row['job_id']); ?></span></span> <i class="fa fa-shopping-cart fa-2x"></i> Orders</td>
			<td width="20%" bgcolor="#F7F7F7" style="border-right: 1px solid #FFF" align="center">
				<span class="num"><?php echo $job_row['positions']; ?></span>
				<i class="fa fa-comments-o fa-2x"></i> Feedbacks
				<br><i class="fa fa-star fa-lg" style="color: #f4c869"></i>  Rating: <?php echo GetJobSuccess($job_row['job_id']); ?><br/><br/>
			</td>
		</tr>
		</table>
		<?php } ?>
		
		<br>
		<a name="orders"></a>
		<h3 class="brd"><i class="fa fa-shopping-cart" style="color: #5cb85c"></i> Offer Orders - <?php echo $job_row['title']; ?></h3>
		<?php if (@$filter) { ?><p><b><i class="fa fa-search"></i> Search results for '<?php echo substr($filter, 0, 70); ?>'</b></p><hr><?php } ?>
		
		<?php if ($total > 0) { ?>
		<form action="" id="form1" name="form1" method="get">
		<div class="row">
			<div class="col-md-3">			
					<!--<span>Sort by:</span>-->
					<div class="col-md-6">
					<select name="column" id="column" onChange="document.form1.submit()" class="form-control input-sm">
						<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Date</option>
						<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
						<option value="user_id" <?php if ($_GET['column'] == "user_id") echo "selected"; ?>>User Name</option>
					</select>
					</div>
					<div class="col-md-6">
					<select name="order" id="order" onChange="document.form1.submit()" class="form-control input-sm">
						<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
					</div>
			</div>
			<div class="col-md-3">
				  <!--&nbsp;&nbsp;<span>Results:</span>-->
				  <select name="show" id="show" onChange="document.form1.submit()" class="form-control input-sm">
					<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
					<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
					<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
					<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
				  </select>
			</div>
			<div class="col-md-4">
					<div class="input-group form-group-sm">
						<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control input-sm" required />
						<span class="input-group-btn">
							<input type="submit" class="btn btn-info" value="Search" />
							<?php if (isset($filter) && $filter != "") { ?><a href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $job_id; ?>"><span class="label label-info"><i class="fa fa-remove"></i> cancel search</span></a><?php } ?>
						</span>
					</div>
					<input type="hidden" name="id" value="<?php echo $job_id; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
			</div>
			<div class="col-md-2 results text-right">
				<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</div>
		</div>
		</form>
		<br>
		<?php } ?>
		
			<form id="form2" name="form2" method="post" action="#orders">
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="15%">Order Date</th>
				<th width="15%">Reference ID</th>
				<th width="20%">User</th>
				<th width="20%">Comment</th>
				<th width="10%">Amount</th>
                <th width="10%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td align="center" valign="middle">
					<input type="checkbox" class="checkbox" <?php if ($row['status'] != "pending") echo "disabled=\"disabled\""; ?> name="id_arr[<?php echo $row['job_task_id']; ?>]" id="id_arr[<?php echo $row['job_task_id']; ?>]" value="<?php echo $row['job_task_id']; ?>" />
                  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row['date_complete']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row['reference_id']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php if ($row['country_code'] != "") { ?><img src="<?php SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /> <?php } ?> <i class="fa fa-user-circle-o"></i> <?php echo GetWorkerInfo($row['worker_id']); ?></td>
                  <td valign="middle" align="left">
					<?php
							if (strlen($row['job_proof']) > 75)
							{
								$job_proof = substr($row["job_proof"], 0, 70);
								$job_proof .= "... <a href='".SITE_URL."order_details.php?offer_id=".$row['offer_id']."&id=".$row['order_id']."><img src='".SITE_URL."images/icon_more.png' align='absmiddle' /></a>";
							}
							else
							{
								$job_proof = $row["job_proof"];
							}

							if (@$filter) echo highlight_word($job_proof, $filter, "#EF9013"); else echo $job_proof;
					?>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left"></td>      
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($row['status'])
						{
							case "notcomplete": echo "<span class='label label-default'>not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'>paid</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}
						if ($row['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row['comment']."\"></i>";
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>order_details.php?offer_id=<?php echo $row['offer_id']; ?>&id=<?php echo $row['order_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
				<tr>
					<td style="background: #f5fdf3; border-top: solid 2px #FFF;" colspan="2" align="left">
						<b>Mark selected as:</b>
					</td>
					<td style="background: #f5fdf3; border-top: solid 2px #FFFF;" align="left"> 
						<select name="mark_as" class="form-control input-sm" required>
							<option value="">--------------</option>
							<option value="confirm" id="background:#82ED09;color:#FFF;">&#10003; Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">- Not Complete</option>
							<option value="decline" style="background:#FC2020;color:#FFF;">&times; Not Satisfied</option>
						</select>
					</td>
					<td style="background: #f5fdf3; border-top: solid 2px #FFF;" colspan="5" align="left">
					
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="modify" />
						<input type="submit" class="btn btn-info" name="MassModify" id="MassModify" value="Go" />
					</td>
				</tr>
		<?php }else{ ?>
				<tr>
				<td colspan="8" align="center">			
					<?php if (@$filter) { ?>
						<p align="center">No orders found. <a class="btn btn-info" href="<?php echo SITE_URL; ?>offer_orders.php?id=<?php echo $job_id; ?>">Search again &#155;</a></p>
					<?php }else{ ?>
						<p align="center">No orders for this offer at this time.</p>
					<?php } ?>
				</td>
				</tr>
		<?php } ?>
           </table>
           </form>
           </div>
           				
				<?php echo ShowPagination("orders",$results_per_page,"offer_orders.php?id=$job_id&column=$rrorder&order=$rorder&show=$results_per_page&","WHERE offer_id='$job_id'".$filter_by); ?>


		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>myoffers.php"><i class="fa fa-angle-left"></i> Go back</a></p>


<?php require_once ("inc/footer.inc.php"); ?>