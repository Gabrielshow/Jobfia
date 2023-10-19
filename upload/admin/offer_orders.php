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


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 20;


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pn			= (int)$_GET['pn'];
		$job_id		= (int)$_GET['id'];

		$jquery = "SELECT *, DATE_FORMAT(added, '%d %b %Y <sup>%h:%i %p</sup>') AS date_added FROM jobfia_jobs WHERE job_type='offer' AND job_id='$job_id' LIMIT 1";
		$jresult = smart_mysql_query($jquery);
		$jtotal = mysqli_num_rows($jresult);
		$jrow = mysqli_fetch_array($jresult);
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
					//case "confirm":		ConfirmTask($task_id); break;
					//case "decline":		DeclineTask($task_id); break;
					//case "incomplete":	NotCompleteTask($task_id); break;
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
				case "worker_id": $rrorder = "worker_id"; break;
				case "proof": $rrorder = "job_proof"; break;
				case "created": $rrorder = "created"; break;
				case "status": $rrorder = "status"; break;
				default: $rrorder = "created"; break;
			}
		}
		else
		{
			$rrorder = "created";
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

	$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_orders WHERE offer_id='$job_id' $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
	
	$total_result = smart_mysql_query("SELECT * FROM jobfia_orders WHERE offer_id='$job_id' $filter_by ORDER BY created DESC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);

	$title = "Offer Orders";
	require_once ("inc/header.inc.php");

?>
    
     <h2><i class="fa fa-shopping-cart"></i> Offer Orders <?php if ($total > 0) { ?> - <span style="color: #aaa">I will</span> <a href="offer_details.php?id=<?php echo $jrow['job_id']; ?>"><?php echo $jrow['title']; ?></a><?php } ?> <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?> <?php if (@$filter) { ?> / Search results<?php } ?></h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
				switch ($_GET['msg'])
				{
					case "updated": echo "Order have been successfully updated"; break;
					case "deleted": echo "Order have been successfully deleted"; break;
				}
			?>
		</div>
		<?php } ?>

		<form action="" id="form1" name="form1" method="get">
		<table style="background: #fcfcfc" width="100%" border="0" cellpadding="3" cellspacing="0" align="center">
		<tr>
			<td height="30" nowrap="nowrap" valign="middle" align="left" width="45%">
				Sort by: 
				<select name="column" id="column" onChange="document.form1.submit()">
					<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Date</option>
					<option value="worker_id" <?php if ($_GET['column'] == "worker_id") echo "selected"; ?>>Worker Name</option>
					<option value="proof" <?php if ($_GET['column'] == "proof") echo "selected"; ?>>Proof</option>
					<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
				</select>
				<select name="order" id="order" onChange="document.form1.submit()">
					<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
					<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
				</select>
				<input type="hidden" name="id" value="<?php echo $job_id; ?>" />
				<input type="hidden" name="page" value="<?php echo $page; ?>" />
				</select>
			  &nbsp;&nbsp;View: 
			  <select name="show" id="show" onChange="document.form1.submit()">
				<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
				<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
				<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
				<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
			  </select>
			</td>
			<td nowrap="nowrap" width="30%" valign="middle" align="left">
				<div class="admin_filter">
					<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="40" /> <input type="submit" class="btn btn-success" value="Search" />
					<?php if (isset($filter) && $filter != "") { ?><a title="Cancel Search" href="offer_orders.php?id=<?php echo $job_id; ?>"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?> 
				</div>
			</td>
			<td nowrap="nowrap" valign="middle" width="20%" align="right">
				<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</td>
		</tr>
		</table>
		</form>

            <form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="25%">Customer</th>
				<th width="25%">Instructions</th>
				<th width="10%">Amount</th>
				<th width="15%">Date/Time</th>
                <th width="10%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
                  <td align="center" valign="middle">
					<input type="checkbox" class="checkbox" <?php if ($row['status'] != "pending") echo "disabled=\"disabled\""; ?> name="id_arr[<?php echo $row['order_id']; ?>]" id="id_arr[<?php echo $row['order_id']; ?>]" value="<?php echo $row['order_id']; ?>" />
                  </td>
                  <td valign="middle" align="left"><?php echo GetUsername($row['user_id'], 1); ?></td>
                  <td valign="middle" align="left">
					<?php
							if (strlen($row['job_proof']) > 75)
							{
								$job_proof = substr($row["job_proof"], 0, 70);
								$job_proof .= "... <a href='task_details.php?job_id=".$row['job_id']."&id=".$row['job_task_id']."><img src='images/icon_more.png' align='absmiddle' /></a>";
							}
							else
							{
								$job_proof = $row["job_proof"];
							}

							if (@$filter) echo highlight_word($job_proof, $filter, "#EF9013"); else echo $job_proof;
					?>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 10px">
					<?php echo DisplayMoney($row['amount']); ?>	                  
                  </td>
				  <td nowrap="nowrap" valign="middle" align="center"><?php echo $row['date_complete']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 10px;">
					<?php
							switch ($row['status'])
							{
								case "notcomplete": echo "<span class='label label-default'>".$row['status']."</span>"; break;
								case "pending": echo "<span class='label label-warning'>in process</span>"; break;							
								case "declined": echo "<span class='label label-danger'>".$row['status']."</span>"; break;
								case "confirmed": echo "<span class='label label-success'>".$row['status']."</span>"; break;
								default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
							}

							if ($row['comment'] != "")
							{
								echo "<span class=\"jobfia_tooltip\" title=\"".$row['comment']."\"> <i class='fa fa-info-circle'></i>";
							}
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="center">
					<a href="order_details.php?job_id=<?php echo $row['job_id']; ?>&id=<?php echo $row['order_id']; ?>"><img src="images/view.png" alt="Details" title="Details" /></a>
					<a href="#" onclick="if (confirm('Are you sure you really want to delete this order?') )location.href='order_details.php?job_id=<?php echo $row['offer_id']; ?>&id=<?php echo $row['order_id']; ?>&act=delete';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
				  </td>
                </tr>
			<?php } ?>
				<tr>
					<td style="border-top: solid 1px #FFF;" bgcolor="#F9F9F9" colspan="7" align="left">
						<b>Mark selected as:</b> 
						<select name="mark_as">
							<option value="">--------------</option>
							<option value="confirm" style="background:#92D811;color:#FFF;">Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">Not Complete</option>
							<option value="decline" style="background:#F9AA98;color:#FFF;">Not Satisfied</option>
						</select>
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="modify" />
						<input type="submit" class="btn btn-success" name="MassModify" id="MassModify" value="Go" />
					</td>
				</tr>
				<tr>
					<td colspan="7" align="center">
						 <?php echo ShowPagination("orders",$results_per_page,"offer_orders.php?id=$job_id&column=$rrorder&order=$rorder&show=$results_per_page&","WHERE offer_id='$job_id'".$filter_by); ?>
					</td>
				</tr>

	<?php }else{ ?>
			<?php if (isset($filter)) { ?>
				<tr><td colspan="7"><div class="alert alert-info text-center">No orders found. <a class="btn btn-success" href="offer_orders.php?id=<?php echo $job_id; ?>">Search again &#155;</a></div></td></tr>
			<?php }else{ ?>
				<tr><td colspan="7"><div class="alert alert-info text-center">This offer has no orders at this time.</div></td></tr>
			<?php } ?>
	<?php } ?>

           </table>
		   </form>

	<?php if (isset($job_id)) { ?>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;" /></p>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>