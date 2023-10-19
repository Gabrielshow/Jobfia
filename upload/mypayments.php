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
	$total_deposit = 0;
	$total_widthrawal = 0;

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y <sup>%h:%i %p</sup>') AS date_created FROM jobfia_transactions WHERE (user_id='$userid' OR recipient_id='$userid') ORDER BY created DESC LIMIT ".$from.",".$results_per_page;
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	
	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Payment History";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<div class="pull-right"><a class="btn btn-success" href="<?php echo SITE_URL; ?>deposit.php"><i class="fa fa-upload"></i> Deposit Funds</a> <a class="btn btn-info" href="<?php echo SITE_URL; ?>withdraw.php"><i class="fa fa-download"></i> Withdraw Funds</a></div>

    <h1 class="brd"><i class="fa fa-money" style="color: #01a650"></i> Payment History</h1>

		<?php if ($total > 0) { ?>

			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td bgcolor="F7F7F7" style="border-top: 1px solid #EEE; padding: 5px;" colspan="7" valign="middle" align="right">
						<table width="270" align="right" border="0" cellspacing="0" cellpadding="3">
							<tr><td width="50%" align="right"><b>Account Balance:</b></td><td align="right"><b style="color: #01a650"><?php echo GetUserBalance($userid); ?></b></td></tr>
							<tr><td align="right">Total Deposit:</td><td align="right"><?php echo DisplayMoney($total_deposit); ?></td></tr>
							<tr><td align="right">Total Withdrawal:</td><td align="right"><?php echo DisplayMoney($total_withdrawal); ?></td></tr>
						</table>
					</td>
				</tr>	            
              <tr>
                <th width="17%">Date</th>
				<th width="15%">Reference ID</th>
                <th width="20%">Payment Type</th>
				<th width="20%">Method</th>
                <th width="15%">Status</th>
				<th width="15%">Fee</th>
				<th width="17%">Amount</th>
              </tr>
              
			<?php 
				while ($row = mysqli_fetch_array($result)) { $cc++;
					if ($row['status'] == "confirmed" || $row['status'] == "paid")
					{
						if ($row['payment_type'] == "deposit")	$total_deposit += $row['amount'];
						if ($row['payment_type'] == "withdraw") $total_withdrawal += $row['amount'];
					}
			?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td nowrap valign="middle" align="left"><?php echo $row['date_created']; ?></td>
                  <td valign="middle" align="left"><?php echo $row['reference_id']; ?></td>
                  <td valign="middle" align="left">
					<?php echo GetPaymentName($row['payment_type']); ?>
					<?php if ($row['payment_type'] == "send_money" && $row['recipient_id'] == $userid) echo "<br/><span style='font-size:12px;color:#B5B5B5;'>From: <i class='fa fa-user'></i> ".GetEmployerInfo($row['user_id'])."</span>"; ?>
					<?php if ($row['payment_type'] == "send_money" && $row['user_id'] == $userid) echo "<br/><span style='font-size:12px;color:#B5B5B5;'>To: <i class='fa fa-user-circle'></i> ".GetWorkerInfo($row['recipient_id'])."</span>"; ?>
					<?php if ($row['payment_details'] != "") echo "<span class=\"jobfia_tooltip\" title=\"".$row['payment_details']."\"><i class='fa fa-info-circle'></i></span>"; ?>
                  </td>
				  <td valign="middle" align="left">
					<?php
						 //echo ($row['payment_method'] != "") ? $row['payment_method'] : "---"; 
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
                  <td nowrap="nowrap" valign="middle" align="left">
					<?php
							switch ($row['status'])
							{
								case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting approval</span>"; break;
								case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting approval</span>"; break;
								case "paid": echo "<span class='label label-success'><i class='fa fa-check'></i> paid</span>"; break;
								case "confirmed": echo "<span class='label label-success'><i class='fa fa-check'></i> paid</span>"; break;
								case "declined": echo "<span class='label label-danger'><i class='fa fa-times'></i> declined</span>"; break;
								case "fraud": echo "<span class='label label-danger'><i class='fa fa-times'></i> fraud</span>"; break;
								case "failed": echo "<span class='label label-danger'><i class='fa fa-times'></i> failed</span>"; break;
								default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
							}

							if ($row['reason'] != "") echo " <span class=\"jobfia_tooltip\" title=\"".$row['reason']."\"><i class='fa fa-info-circle'></i></span>";
							if ($row['payment_type'] == "friend_bonus" && $row['status'] == "pending") echo "<span class=\"jobfia_tooltip\" title=\"will be confirmed when user's account balance reach ".DisplayMoney(REFER_FRIEND_BONUS_AFTER)."\"><i class='fa fa-info-circle'></i></span>";
					?>
				  </td>
				  <td nowrap valign="middle" align="left"><?php echo ($row['transaction_fee'] != "0.0000") ? DisplayMoney($row['transaction_fee']) : "---"; ?></td>
                  <td nowrap valign="middle" align="left"><?php echo DisplayMoney($row['amount']); ?></td>
                </tr>
			<?php } ?>
			</table>
			</div>

				<center><?php echo ShowPagination("transactions",$results_per_page,"mypayments.php?","WHERE (user_id='$userid' OR recipient_id='$userid')"); ?></center>

     <?php }else{ ?>
				<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> There are no payments at this time.</div>
	 <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>