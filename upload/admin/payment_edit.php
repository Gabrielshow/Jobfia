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
	require_once("./inc/adm_functions.inc.php");


	if (isset($_POST["action"]) && $_POST["action"] == "edit_payment")
	{
		unset($errors);
		$errors = array();

		$transaction_id	= (int)getPostParameter('tid');
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));
		$reason			= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));

		if (!$status)
		{
			$errors[] = "Please select payment status";
		}
		else
		{
			switch ($status)
			{
				case "paid": $status="paid"; break;
				case "pending": $status="pending"; break;
				case "declined": $status="declined"; break;
				default: $status="unknown"; break;
			}
		}

		if (count($errors) == 0)
		{
			$sql = "UPDATE jobfia_transactions SET status='$status', reason='$reason', updated=NOW() WHERE transaction_id='$transaction_id' AND status<>'request'";
			$result = smart_mysql_query($sql);

			header("Location: payments.php?msg=updated");
			exit();
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "&#155; ".$errorname."<br/>";
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $id = (int)$_GET['id']; } elseif (isset($_POST['tid']) && is_numeric($_POST['tid'])) { $id = (int)$_POST['tid']; }

	if (isset($id) && is_integer($id))
	{
		$query = "SELECT t.*, DATE_FORMAT(t.created, '%d %b %Y %h:%i %p') AS payment_date, u.fname, u.lname FROM jobfia_transactions t, jobfia_users u WHERE t.user_id=u.user_id AND t.transaction_id='$id'";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Edit Payment";
	require_once ("inc/header.inc.php");

?>

     <h2><i class="fa fa-file-text-o"></i> Edit Payment</h2>

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
                <td valign="top"><a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment Type:</td>
                <td valign="top">
					<b><?php echo GetPaymentName($row['payment_type']); ?></b>
					<?php if ($row['payment_type'] == "send_money") echo "<br><span style='font-size:13px;color:#B5B5B5;'>To: ".GetUsername($row['recipient_id'])."</span>"; ?>
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
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="top"><?php echo $row['payment_date']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Status:</td>
                <td valign="top">
					<?php
						switch ($row['status'])
						{
							case "confirmed": echo "<span style='margin:0;' class='confirmed_status'>confirmed</span>"; break;
							case "pending": echo "<span style='margin:0;' class='pending_status'>pending</span>"; break;
							case "declined": echo "<span style='margin:0;' class='declined_status'>declined</span>"; break;
							case "failed": echo "<span style='margin:0;' class='failed_status'>failed</span>"; break;
							case "request": echo "<span style='margin:0;' class='pending_status'>waiting for approval</span>"; break;
							case "paid": echo "<span style='margin:0;' class='paid_status'>request</span>"; break;
							default: echo "<span style='margin:0;' class='payment_status'>".$row['status']."</span>"; break;
						}
					?>
				</td>
              </tr>
			<?php if ($row['reason'] != "") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Comment:</td>
                <td style="color: #000; padidng: 0 10px;" align="left" valign="top"><?php echo $row['reason']; ?></td>
              </tr>
			<?php } ?>
			  <?php if ($row['status'] != "pending" && $row['status'] != "request") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Change Status:</td>
                <td valign="top">
					<select name="status" id="status" class="form-control" onchange="javascript:hiddenDiv('status','reason')">
						<option value="paid">paid</option>
						<option value="pending">pending</option>
						<option value="declined">declined</option>
					</select>
				</td>
              </tr>
			 <?php }else{ ?>
				<?php if ($row['status'] == "pending") { ?>
					<input type="hidden" name="status" value="pending" />
				<?php }elseif ($row['status'] == "request") { ?>
					<input type="hidden" name="status" value="request" />
				<?php } ?>
			 <?php } ?>
            <tr id="reason" style="display: none;">
                <td valign="middle" align="left" class="tb1">Reason:<br/><small>(optional)</small></td>
                <td valign="top"><textarea cols="40" rows="4" name="reason" class="form-control"><?php echo getPostParameter('reason'); ?></textarea></td>
            </tr>
            <tr>
              <td align="left" colspan="2" valign="bottom">
				<br>
				<input type="hidden" name="tid" id="tid" value="<?php echo (int)$row['transaction_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit_payment">
				 <?php if ($row['status'] != "pending" && $row['status'] != "request") { ?>
					<input type="submit" class="btn btn-success" name="process" value="Update" />
				 <?php }else{ ?>
					<input type="button" class="btn btn-success" name="edit" value="Process Payment" onClick="javascript:document.location.href='payment_process.php?id=<?php echo $row['transaction_id']; ?>'" />
				 <?php } ?>
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