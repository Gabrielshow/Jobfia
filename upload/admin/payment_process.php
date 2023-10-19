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


	if (isset($_POST["action"]) && $_POST["action"] == "process_payment")
	{
		unset($errors);
		$errors = array();

		$transaction_id	= (int)getPostParameter('tid');
		$user_id		= (int)getPostParameter('uid');
		$amount			= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));
		$reason			= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));
		$notification	= (int)getPostParameter('notification'); //dev

		if (!($status))
		{
			$errors[] = "Please select payment status";
		}
		else
		{
			if (!(is_numeric($amount) && $amount > 0))
			{
				$errors[] = "Wrong transaction amount";
			}
		}

		if (count($errors) == 0)
		{
			$transaction_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_transactions WHERE transaction_id='$transaction_id' LIMIT 1"));
			
			// update transaction
			smart_mysql_query("UPDATE jobfia_transactions SET amount='$amount', status='$status', reason='$reason', process_date=NOW() WHERE transaction_id='$transaction_id' LIMIT 1");

			if ($status == "paid" && ($transaction_row['payment_type'] == "withdraw" || $transaction_row['payment_type'] == "deposit"))
			{
				if ($transaction_row['payment_type'] == "withdraw" && $transaction_row['status'] == "request")
					smart_mysql_query("UPDATE jobfia_users SET balance=balance-'".$amount."' WHERE user_id='$user_id' LIMIT 1");
				//send email to user

				if ($transaction_row['payment_type'] == "deposit" && $transaction_row['status'] == "pending")
					smart_mysql_query("UPDATE jobfia_users SET balance=balance+'".$amount."' WHERE user_id='$user_id' LIMIT 1");
				
				//send email//dev
				if ($notification == 1)
				{
				}
			}

			header("Location: payments.php?msg=processed");
			exit();
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "&#155; ".$errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT t.*, DATE_FORMAT(t.created, '%d %b %Y %h:%i %p') AS payment_date, u.username, u.fname, u.lname FROM jobfia_transactions t, jobfia_users u WHERE t.user_id=u.user_id AND t.transaction_id='$id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Process Payment";
	require_once ("inc/header.inc.php");

?>
    
    
     <h2><i class="fa fa-hand-o-right"></i> Proceed Payment</h2>

		<?php if ($total > 0) { 

				$row = mysqli_fetch_array($result);

		 ?>

		<script>
		<!--
			function hiddenDiv(id,showid){
				if(document.getElementById(id).value == "declined"){
					document.getElementById(showid).style.display = ""
				}else{
					document.getElementById(showid).style.display = "none"
				}
			}
		-->
		</script>

		<?php if (isset($errormsg)) { ?>
			<div class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>

			<form action="" method="post" name="form1">
            <table style="background: #FCFCFC;padding: 20px;" width="100%" cellpadding="3" cellspacing="4" border="0" align="center">
			  <tr>
                <td width="15%" valign="middle" align="left" class="tb1">Payment ID:</td>
                <td valign="top"><?php echo $row['transaction_id']; ?></td>
              </tr>
			  <tr>
                <td nowrap="nowrap" valign="middle" align="left" class="tb1">Reference ID:</td>
                <td valign="top"><?php echo $row['reference_id']; ?></td>
              </tr>
              <tr>
                <td nowrap="nowrap" valign="middle" align="left" class="tb1">Member:</td>
                <td valign="top"><i class="fa fa-user"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['username']; ?></a> ( <?php echo $row['fname']." ".$row['lname']; ?> )</td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Type:</td>
                <td valign="top"><b><?php echo GetPaymentName($row['payment_type']); ?></b></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Method:</td>
                <td valign="top">
					<?php if ($row['payment_method'] == "paypal") { ?><img src="images/icons/paypal.png" align="absmiddle" />&nbsp;<?php } ?>
					<?php if ($row['payment_method'] == "payza") { ?><img src="images/icon/payza.png" align="absmiddle" />&nbsp;<?php } ?>
					<?php echo ($row['payment_method'] != "") ? $row['payment_method'] : "---"; ?>
				</td>
              </tr>
			  <?php if ($row['payment_details'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Details:</td>
                <td valign="top"><?php echo $row['payment_details']; ?></td>
              </tr>
			  <?php } ?>
			  <?php if ($row['transaction_fee'] != "0.0000") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Fee:</td>
                <td valign="top"><?php echo DisplayMoney($row['transaction_fee']); ?></td>
              </tr>
			  <?php } ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Amount:</td>
                <td valign="top"><?php echo DisplayMoney($row['amount']); ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Status:</td>
                <td valign="top">
					<?php
						switch ($row['status'])
						{
							case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning'>pending</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "failed": echo "<span class='label label-danger'>failed</span>"; break;
							case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> waiting for approval</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}
					?>
				</td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="top"><?php echo $row['payment_date']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Change status:</td>
                <td valign="top">
					<select name="status" id="status" class="form-control" onchange="javascript:hiddenDiv('status','reason')">
						<!--<option value="confirmed" style="background:#72CE44;color:#FFF;">Confirmed</option>-->
						<option value="paid" style="background:#82ED09;color:#FFF;">Paid</option>
						<option value="declined" style="background:#FC2020;color:#FFF;">Declined</option>
					</select>
				</td>
              </tr>
            <tr id="reason" style="display: none;">
                <td valign="middle" align="left" class="tb1">Reason:<br/><small>(optional)</small></td>
                <td valign="top"><textarea cols="40" rows="4" name="reason" class="form-control"><?php echo getPostParameter('reason'); ?></textarea></td>
            </tr>
			<!--
              <tr>
                <td valign="middle" align="left" class="tb1">&nbsp;</td>
                <td valign="top"><input type="checkbox" class="checkbox" name="notification" value="1" <?php if (getPostParameter('notification') == 1 || !$_POST['action']) echo "checked=\"checked\""; ?> /> send email to user about payment status</td>
              </tr>
			-->
            <tr>
              <td align="left" valign="bottom">&nbsp;</td>
	          <td align="left" valign="bottom">
				<input type="hidden" name="tid" id="tid" value="<?php echo (int)$row['transaction_id']; ?>" />
				<input type="hidden" name="uid" id="uid" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="amount" id="amount" value="<?php echo $row['amount']; ?>" />
				<input type="hidden" name="action" id="action" value="process_payment">
				<input type="submit" class="btn btn-success" name="process" value="Proceed Payment" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
			  </td>
            </tr>
          </table>
		  </form>
      
	  <?php }else{ ?>
			<div class="alert alert-info">Sorry, no payment found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>