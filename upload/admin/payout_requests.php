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
		$results_per_page = 10;


		// Delete payments //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$pid = (int)$v;
					DeletePayment($pid);
				}

				header("Location: payout_requests.php?msg=deleted");
				exit();
			}
		}

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "username": $rrorder = "u.username"; break;
					case "amount": $rrorder = "t.amount"; break;
					case "ids": $rrorder = "t.transaction_id"; break;
					default: $rrorder = "t.transaction_id"; break;
				}
			}
			else
			{
				$rrorder = "t.transaction_id";
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
				$filter_by = " AND reference_id='$filter'";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT t.*, DATE_FORMAT(t.created, '%d %b %Y <sup>%h:%i %p</sup>') AS date_created, u.username, u.fname, u.lname, u.email FROM jobfia_transactions t, jobfia_users u WHERE t.payment_type='withdraw' AND t.status='request' AND t.user_id=u.user_id $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_transactions WHERE payment_type='withdraw' AND status='request' $filter_by";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


	$title = "Payout Requests";
	require_once ("inc/header.inc.php");

?>

       <h2><img src="images/icons/withdrawal.png" align="absmiddle" /> Payout Requests</h2>
		
			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "updated": echo "Payment has been successfully edited"; break;
						case "deleted": echo "Payment has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td nowrap="nowrap" valign="middle" align="left" width="50%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="username" <?php if ($_GET['column'] == "username") echo "selected"; ?>>Username</option>
			<option value="amount" <?php if ($_GET['column'] == "amount") echo "selected"; ?>>Amount</option>
          </select>
          <select name="order" id="order" onChange="document.form1.submit()">
			<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
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
					<?php if (isset($filter) && $filter != "") { ?><a title="Cancel Search" href="payout_requests.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?> 
				</div>
			</td>
			<td nowrap="nowrap" valign="middle" width="45%" align="right">
			   <?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="10%">Request Date</th>
				<th width="10%">Reference ID</th>
				<th width="20%">Member</th>
				<th width="10%">Fee</th>
				<th width="10%">Amount</th>
				<th width="13%">Payment Method</th>
				<th width="13%">Status</th>
				<th width="10%">Action</th>
				<th width="5%"></th>
			</tr>
			<?php if ($total > 0) { ?>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['transaction_id']; ?>]" id="id_arr[<?php echo $row['transaction_id']; ?>]" value="<?php echo $row['transaction_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap><?php echo $row['date_created']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px;"><?php echo $row['reference_id']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle"><i class="fa fa-user-circle-o" style="color: #bbb"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; //$row['username']; ?></a></td>
					<td align="left" valign="middle" style="padding-left: 10px"><?php echo ($row['transaction_fee'] != "0.0000") ? DisplayMoney($row['transaction_fee']) : "---"; ?></td>
					<td align="left" valign="middle" style="padding-left: 10px"><?php echo DisplayMoney($row['amount']); ?></td>
					<td align="left" valign="middle" style="padding-left:10px">
						<?php /*if ($row['payment_method'] == "paypal") { ?><img src="images/icons/paypal.png" align="absmiddle" />&nbsp;<?php }*/ ?>
						<?php /*if ($row['payment_method'] == "payza") { ?><img src="images/icon/payza.png" align="absmiddle" />&nbsp;<?php }*/ ?>
						<?php //echo ($row['payment_method'] != "") ? $row['payment_method'] : "---"; ?>
						<?php
							if ($row['payment_method'] != "")
						 {
							$p_logo = PUBLIC_HTML_PATH."/images/pmethods/".strtolower($row['payment_method']).".png"; 
							echo (file_exists($p_logo)) ? "<img src='/images/pmethods/".strtolower($row['payment_method']).".png'>" : $row['payment_method']; 
						 }
						 else
						 {
							echo "---"; 
						 }
						?>
					</td>
					<td nowrap align="left" valign="middle" style="padding-left: 10px;">
					<?php
						switch ($row['status'])
					  {
							case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning'>pending</span>"; break;
							case "declined": echo "<span class='label label-danger'>rejected</span>"; break;
							case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> waiting for approval</span>"; break;
							case "paid": echo "<span class='label label-success'>payment sent</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}
					?>
					</td>
					<td nowrap align="center" valign="middle">
						<?php if ($row['status'] == "request") { ?>
							<a class="btn btn-info" href="payment_process.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>"><i class="fa fa-check-square-o"></i> Proceed</a>
						<?php } ?>
					</td>
					<td nowrap align="center" valign="middle">
						<a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this payment?') )location.href='payment_delete.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
					<td colspan="10" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="action" value="delete" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
				<tr>
					<td colspan="10" align="center">
						<?php echo ShowPagination("transactions",$results_per_page,"payout_requests.php?column=$rrorder&order=$rorder&show=$results_per_page&","WHERE payment_type='withdraw' AND status='request' $filter_by"); ?>
					</td>
				</tr>

          <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<tr><td colspan="10"><div class="alert alert-info text-center">Sorry, no payments found for your search criteria! <a href="payout_requests.php">Search again &#155;</a></div></td></tr>
				<?php }else{ ?>
					<tr><td colspan="10"><div class="alert alert-info text-center">There are no payout requests at this time.</div></td></tr>
				<?php } ?>
          <?php } ?>

            </table>
			</form>

<?php require_once ("inc/footer.inc.php"); ?>