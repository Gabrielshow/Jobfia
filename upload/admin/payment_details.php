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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT t.*, DATE_FORMAT(t.created, '%d %b %Y %h:%i %p') AS payment_date, DATE_FORMAT(t.updated, '%d %b %Y %h:%i %p') AS updated_date, DATE_FORMAT(t.process_date, '%d %b %Y %h:%i %p') AS processed_date, u.username, u.email, u.fname, u.lname FROM jobfia_transactions t, jobfia_users u WHERE t.transaction_id='$id' AND t.user_id=u.user_id";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Payment Details";
	require_once ("inc/header.inc.php");

?>
    
    
     <h2><i class="fa fa-file-text-o"></i> Payment Details</h2>

		<?php if ($total > 0) { 

				$row = mysqli_fetch_array($result);
		 ?>
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
                <td valign="middle" align="left" class="tb1">Member:</td>
                <td valign="top"><i class="fa fa-user-circle-o"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Username:</td>
                <td valign="top"><?php echo $row['username']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Email:</td>
                <td valign="top"><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment type:</td>
                <td valign="top">
					<b><?php echo GetPaymentName($row['payment_type']); ?></b>
					<?php if ($row['payment_type'] == "send_money") echo "<br> <span style='font-size:14px;color:#B5B5B5;'>To: ".GetUsername($row['recipient_id'])."</span>"; ?>
				</td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Payment method:</td>
                <td valign="top">
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
                <td valign="top"><span style="font-weight: bold"><?php echo DisplayMoney($row['amount']); ?></span></td>
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
							case "paid": echo "<span class='label label-success'>paid</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
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
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="top"><?php echo $row['payment_date']; ?></td>
              </tr>
			  <?php if ($row['updated'] != "0000-00-00 00:00:00") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Updated:</td>
                <td valign="top"><?php echo $row['updated_date']; ?></td>
              </tr>
			  <?php } ?>
			  <?php if ($row['payment_type'] == "withdraw" && $row['status'] != "request" && $row['process_date'] != "0000-00-00 00:00:00") { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Process Date:</td>
                <td valign="top"><?php echo $row['processed_date']; ?></td>
              </tr>
			  <?php } ?>
            <tr>
              <td align="left" colspan="2" valign="bottom">
				<?php if ($row['status'] == "pending" || $row['status'] == "request") { ?>
					<input type="button" class="btn btn-success" name="edit" value="Process Payment" onClick="javascript:document.location.href='payment_process.php?id=<?php echo $row['transaction_id']; ?>'" />
				<?php }else{ ?>
					<input type="button" class="btn btn-success" name="edit" value="Edit Payment" onClick="javascript:document.location.href='payment_edit.php?id=<?php echo $row['transaction_id']; ?>'" />
				<?php } ?>
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
				<input type="button" class="btn btn-danger" name="delete" value="Delete Payment" onclick="if (confirm('Are you sure you really want to delete this payment?') )location.href='payment_delete.php?id=<?php echo $row['transaction_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
			  </td>
            </tr>
          </table>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no payment found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>