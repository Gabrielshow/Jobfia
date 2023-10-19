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

			header("Location: user_payments.php?id=".$_GET['id']."&msg=deleted");
			exit();
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid = (int)$_GET['id'];

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "reference": $rrorder = "reference_id"; break;
					case "ptype": $rrorder = "payment_type"; break;
					case "pmethod": $rrorder = "payment_method"; break;
					case "amount": $rrorder = "amount"; break;
					case "status": $rrorder = "status"; break;
					case "ids": $rrorder = "transaction_id"; break;
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
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS date_created FROM jobfia_transactions WHERE user_id='$uid' ORDER BY created DESC";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_transactions WHERE user_id='$uid' ORDER BY created DESC";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);
	}

	$title = "Payment History";
	require_once ("inc/header.inc.php");

?>

	<h2><i class="fa fa-money" style="color: #5cb85c"></i> <?php echo GetUsername($uid); ?> Payment History</h2>

	<?php if ($total > 0) { ?>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
				switch ($_GET['msg'])
				{
					case "processed": echo "Payment has been successfully processed"; break;
					case "updated": echo "Payment has been successfully updated"; break;
					case "deleted": echo "Payment has been successfully deleted"; break;
				}
			?>
		</div>
		<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td nowrap="nowrap" width="45%" valign="middle" align="left">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="reference" <?php if ($_GET['column'] == "reference") echo "selected"; ?>>Reference ID</option>
			<option value="ptype" <?php if ($_GET['column'] == "ptype") echo "selected"; ?>>Payment Type</option>
			<option value="pmethod" <?php if ($_GET['column'] == "pmethod") echo "selected"; ?>>Payment Method</option>
			<option value="amount" <?php if ($_GET['column'] == "amount") echo "selected"; ?>>Amount</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" onChange="document.form1.submit()">
			<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
			 <input type="hidden" name="id" value="<?php echo $uid; ?>" />
			</td>
			<td nowrap="nowrap" width="30%" valign="middle" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
                <th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
                <th width="15%">Date</th>
				<th width="15%">Reference ID</th>
                <th width="15%">Payment Type</th>
				<th width="15%">Method</th>
				<th width="12%">Amount</th>
				<th width="9%">Fee</th>
				<th width="13%">Status</th>
				<th width="8%">Actions</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr style="height:25px;" bgcolor="<?php if (($cc%2) == 0) echo "#F7F7F7"; else echo "#FFFFFF"; ?>">
                  <td align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['transaction_id']; ?>]" id="id_arr[<?php echo $row['transaction_id']; ?>]" value="<?php echo $row['transaction_id']; ?>" /></td>
                  <td valign="middle" align="center"><?php echo $row['date_created']; ?></td>
                  <td valign="middle" align="center"><a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
                  <td valign="middle" align="left" style="padding-left: 10px">
					<?php echo GetPaymentName($row['payment_type']); ?>
					<?php if ($row['payment_type'] == "send_money") echo "<br/><span style='color:#B5B5B5;'>To: ".GetUsername($row['recipient_id'])."</span>"; ?>
				  </td>
				  <td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px">
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
				  <td valign="middle" align="left" style="padding-left: 10px"><?php echo DisplayMoney($row['amount']); ?></td>
				  <td align="center" valign="middle"><?php echo ($row['transaction_fee'] != "0.0000") ? DisplayMoney($row['transaction_fee']) : "---"; ?></td>
                  <td valign="middle" align="left">
					<?php
							switch ($row['status'])
							{
								case "paid": echo "<span class='label label-success'>paid</span>"; break;
								case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
								case "pending": echo "<span class='label label-warning'>pending</span>"; break;
								case "declined": echo "<span class='label label-danger'>declined</span>"; break;
								case "failed": echo "<span class='label label-danger'>failed</span>"; break;
								case "request": echo "<span class='label label-warning'>waiting for approval</span>"; break;
								default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
							}

							if ($row['reason'] != "") echo "<span class=\"jobfia_tooltip\" title=\"".$row['reason']."\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
							if ($row['payment_type'] == "friend_bonus" && $row['status'] == "pending") echo "<span class=\"jobfia_tooltip\" title=\"will be confirmed when user's account balance reach ".DisplayMoney(REFER_FRIEND_BONUS_AFTER)."\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
					?>
				  </td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="payment_edit.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
					</td>
                </tr>
				<?php } ?>
			  <tr>
				<td colspan="9" align="left" style="border-top: 1px solid #F7F7F7" >
					<input type="hidden" name="action" value="delete" />
					<input type="hidden" name="id" value="<?php echo $uid; ?>" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
			  </tr>
           </table>
		   </form>

		   <?php echo ShowPagination("transactions",$results_per_page,"user_payments.php?id=$uid&column=$rrorder&order=$rorder&&show=$results_per_page&","WHERE user_id='$uid'"); ?>
	  
	  <?php }else{ ?>
				<div class="alert alert-info text-center">There are no payments at this time.</div>
      <?php } ?>

			<?php if (@$uid) { ?>
				<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='user_details.php?id=<?php echo $uid; ?>'" /></p>
			<?php }else{ ?>
				<p align="center"><input class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" type="button"></p>
			<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>