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

	$amount = DisplayMoney(MIN_DEPOSIT, $hide_currency = 1);

	if (isset($_GET['amount']) && is_numeric($_GET['amount']))
	{
		$damount = getGetParameter('amount');
		if ($damount > $amount) $amount = $damount;
	}

	if (isset($_POST['amount']) && is_numeric($_POST['amount'])) $amount = mysqli_real_escape_string($conn, getPostParameter('amount'));


	if (isset($_POST['payment_method']) && is_numeric($_POST['payment_method']))
	{
		$payment_method_id = (int)getPostParameter('payment_method');
		$payment_method_result = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_id='$payment_method_id' AND pmethod_type='deposit' AND status='active' LIMIT 1");
		if (mysqli_num_rows($payment_method_result) > 0)
		{
			$payment_method_row = mysqli_fetch_array($payment_method_result);
			$deposit_fee = $payment_method_row['fee'];
			$deposit_min_amount = $payment_method_row['min_amount'];
			$payment_details = str_replace("{USER_ID}", $userid, $payment_method_row['pmethod_details']);
		}
	}

	if (isset($_POST['deposit']) && $_POST['deposit'] != "")
	{
		unset($errs);
		$errs = array();

		$amount					= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$payment_method			= mysqli_real_escape_string($conn, getPostParameter('payment_method'));

		if (!(is_numeric($amount) && $amount > 0))
		{
			$errs[] = "Please enter correct amount";
			$amount = DisplayMoney(MIN_DEPOSIT, $hide_currency = 1);
		}
		elseif (!($payment_method))
		{
			$errs[] = "Please select payment method";
		}
		else
		{
			if ($amount < MIN_DEPOSIT)
			{
				$errs[] = "The minimum amount for deposit: ".DisplayMoney(MIN_DEPOSIT);
			}

			$payment_method_result = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_id='$payment_method' AND pmethod_type='deposit' AND status='active' LIMIT 1");
			if (mysqli_num_rows($payment_method_result) == 0)
			{
				$errs[] = "Please select payment method";
			}
			else
			{
				$payment_method_row = mysqli_fetch_array($payment_method_result);
				$payment_method_name = mysqli_real_escape_string($conn, $payment_method_row['pmethod_title']);
			}
		}

		if (count($errs) == 0)
		{
			$payment_amount = $amount;
			
			$payment_method_row = mysqli_fetch_array($payment_method_result);
			$deposit_fee = $payment_method_row['fee'];

			// add deposit fee
			if ($deposit_fee != "" && is_numeric($deposit_fee) && $deposit_fee > 0)
			{
				$transaction_fee = CalculatePercentage($amount, $deposit_fee);
				$payment_amount += $transaction_fee;
			}

			$payment_description = "Deposit: ".DisplayMoney($amount);
			
			$reference_id = GenerateReferenceID();
			$result = smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$userid', payment_type='deposit', payment_method='$payment_method_name', currency='".mysqli_real_escape_string($conn, SITE_CURRENCY_CODE)."', transaction_fee='$transaction_fee', amount='$amount', status='pending', created=NOW()");
			$payment_id = mysqli_insert_id($conn);

			// send notification
			if (NEW_DEPOSIT_ALERT == 1)
			{
				$subject = "New deposit";
				$message = "New deposit submitted";

				SendEmail(SITE_MAIL, $subject, $message, $noreply_mail = 1);
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br>";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Deposit Funds";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
	
	<?php /*<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div> */ ?>

	<div class="container">
	<div class="row">        
        <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">

	<h1 class="text-center"><i class="fa fa-upload" style="color: #01a650"></i> Deposit Funds</h1>

	<?php if (!$payment_id) { ?>
		<p class="text-center">Here you can select the amount you wish to deposit into your account.</p>
	<?php } ?>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="success_msg">
				<?php

					switch ($_GET['msg'])
					{
						case "success": echo "Funds added successfully to your account."; break;
						case "canceled": echo "Your payment was canceled."; break;
					}

				?>
			</div>
		<?php } ?>

	<?php 

		if ($payment_id)
		{			
			if ($payment_method == "1") //PayPal
			{
	?>
      <form action="paypal_ipn.php" name="deposit_form" id="deposit_form" method="post">
		<input type="hidden" name="rm" value="2" />
		<input type="hidden" name="cmd" value="_xclick" />
		<input type="hidden" name="business" value="<?php echo PAYPAL_ACCOUNT; ?>" />
		<input type="hidden" name="item_name" value="<?php echo $payment_description; ?>" />
		<input type="hidden" name="currency_code" value="<?php echo SITE_CURRENCY_CODE; ?>" />
		<input type="hidden" name="custom" value="<?php echo $payment_id; ?>" />
		<input type="hidden" name="amount" value="<?php echo DisplayMoney($payment_amount, $hide_currency = 1); ?>" />
		<input type="hidden" name="no_shipping" value="1" />
		<input type="hidden" name="no_note" value="1" />
		<input type="hidden" name="tax" value="0" />
		<input type="hidden" name="action" value="deposit" /><br />
		<p align="center"><img src="<?php echo SITE_URL; ?>images/loading.gif" /></p>
		<p align="center"><span style="font-family: Arial, Times New Roman, Georgia, serif; font-size:22px; line-height:10px; letter-spacing:-1px; color:#444;">Please wait, you are being redirected ...</span></p>
		<center>
			If your browser does not redirect, please click this button: 
			<input type="submit" class="btn btn-info" name="submit_payment" value="Continue &#155;" />
		</center>
      </form>

		<script language="JavaScript" type="text/javascript">
			setTimeout("document.deposit_form.submit()",150);
		</script>

	<?php
			}
			elseif ($payment_method == "2") //Payza
			{
	?>
      <form action="https://secure.payza.com/checkout" name="deposit_form" id="deposit_form" method="post">
		<input type="hidden" name="ap_purchasetype" value="item" />
		<input type="hidden" name="ap_merchant" value="<?php echo ALERTPAY_ACCOUNT; ?>" />
		<input type="hidden" name="ap_itemname" value="<?php echo $payment_description; ?>" />
		<input type="hidden" name="ap_currency" value="<?php echo SITE_CURRENCY_CODE; ?>" />
		<input type="hidden" name="ap_returnurl" value="<?php echo SITE_URL; ?>payment_success.php" />
		<input type="hidden" name="ap_cancelurl" value="<?php echo SITE_URL; ?>payment_cancelled.php" />
		<input type="hidden" name="ap_quantity" value="1" />
		<input type="hidden" name="ap_description" value="<?php echo $payment_description; ?>" />
		<input type="hidden" name="ap_amount" value="<?php echo DisplayMoney($payment_amount, $hide_currency = 1); ?>" />
		<input type="hidden" name="apc_1" value="<?php echo $payment_id; ?>" /><br />
		<p align="center"><img src="<?php echo SITE_URL; ?>images/loading.gif" /></p>
		<p align="center"><span style="font-family: Arial, times, Times New Roman, times-roman, georgia, serif; font-size:22px; line-height:10px; letter-spacing:-1px; color:#444;">Please wait, you are being redirected ...</span></p>
		<center>
			If your browser does not redirect, please click this button: 
			<input type="submit" class="btn btn-info" name="submit_payment" value="Continue &#155;" />
		</center>
      </form>

		<script language="JavaScript" type="text/javascript">
			setTimeout("document.deposit_form.submit()",150);
		</script>

	<?php
			}
			elseif ($payment_method == "3") //Skrill
			{
	?>
      <form action="https://www.moneybookers.com/app/payment.pl" name="deposit_form" id="deposit_form" method="post">
		<input type="hidden" name="pay_to_email" value="<?php echo SKRILL_ACCOUNT; ?>" />
		<input type="hidden" name="status_url" value="<?php echo SITE_URL; ?>scrill_ipn.php" /> 
		<input type="hidden" name="language" value="EN" />
		<input type="hidden" name="amount" value="<?php echo DisplayMoney($payment_amount, $hide_currency = 1); ?>" />
		<input type="hidden" name="currency" value="<?php echo SITE_CURRENCY_CODE; ?>" />
		<input type="hidden" name="detail1_description" value="<?php echo $payment_description; ?>" />
		<input type="hidden" name="detail1_text" value="<?php echo $payment_description; ?>" />
		<p align="center"><img src="<?php echo SITE_URL; ?>images/loading.gif" /></p>
		<p align="center"><span style="font-family: times, Times New Roman, times-roman, georgia, serif; font-size:22px; line-height:10px; letter-spacing:-1px; color:#444;">Please wait, you are being redirected ...</span></p>
		<center>
			If your browser does not redirect, please click this button: 
			<input type="submit" class="btn btn-default" name="submit_payment" value="Continue" />
		</center>
      </form>

		<script language="JavaScript" type="text/javascript">
			setTimeout("document.deposit_form.submit()",150);
		</script>

	<?php
			}
			elseif ($payment_method == "4") //Authorize.net
			{
			}
			else
			{
				echo "Thank you for payment. We will check it soon and update your account balance.";
			}
	?>

	<?php
	
		}else{ 

	?>

		<div style="background: #fcfcfc; border: 1px solid #F5F5F5; border-radius: 10px; padding: 10px;">
		<?php if (isset($allerrors)) { ?><div class="alert alert-danger"><?php echo $allerrors; ?></div><?php } ?>
		<form action="" method="post" name="wform">
		<div class="row form-group">
			<label class="col-sm-4 label-title">Payment Method:</label>
			<div class="col-sm-8">
				<select name="payment_method" onchange="this.form.submit();" class="form-control">
					<option value="">-- Select payment method --</option>
					<?php
						$sql_pmethods = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_type='deposit' AND status='active' ORDER BY pmethod_title");
						if (mysqli_num_rows($sql_pmethods) > 0)
						{
							while ($row_pmethods = mysqli_fetch_array($sql_pmethods))
							{
								if ($payment_method == $row_pmethods['pmethod_id'] || $_POST['payment_method'] == $row_pmethods['pmethod_id']) $selected = " selected=\"selected\""; else $selected = "";
								echo "<option value=\"".$row_pmethods['pmethod_id']."\"".$selected.">".$row_pmethods['pmethod_title']."</option>";
							}
						}
					?>
				</select>
			</div>
		</div>
		<?php if (@$deposit_fee > 0) { ?>
		<div class="row form-group">
			<label class="col-sm-4 label-title">Fee:</label>
			<div class="col-sm-8"><?php echo $deposit_fee; ?>%</div>
		</div>
		<?php } ?>
		<div class="row form-group">
			<label class="col-sm-4 label-title">Amount:</label>
			<div class="col-sm-8">
				<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
						<input type="number" name="amount" value="<?php echo @$amount; ?>" size="7" min="0" step="1" data-number-to-fixed="2" data-number-stepfactor="100" class="form-control currency" id="c2" />
		    	</div>
				<?php if (@$deposit_min_amount > 0) { ?>Min amount: <b><?php echo $deposit_min_amount; ?></b><?php } ?>
			</div>
		</div>
		<?php if (@$payment_details != "") { ?>
		<div class="row form-group">
			<label class="col-sm-4 label-title">Instructions:</label>
			<div class="col-sm-8"><?php echo $payment_details; ?></div>
		</div>
		<div class="row form-group">
			<label class="col-sm-4 label-title"></label>
			<div class="col-sm-8">
				<input type="hidden" name="action" value="deposit" />
				<input type="submit" class="btn btn-success" name="deposit" id="deposit" value="I sent a payment" />
			</div>
		</div>
		<?php }else{ ?>
		<div class="row form-group">
			<label class="col-sm-4 label-title"></label>
			<div class="col-sm-8">
			<?php if (PAYPAL_ACCOUNT != "") { ?><img src="<?php echo SITE_URL; ?>images/paypal_icon.png" border="0" /><br/><?php } ?>
			<?php if (PAYZA_ACCOUNT != "" && PAYZA_SECURITY_CODE != "") { ?><img src="<?php echo SITE_URL; ?>images/payza_icon.png" border="0" /><?php } ?>
			<?php if (SKRILL_ACCOUNT != "" && SKRILL_SECURITY_CODE != "") { ?><img src="<?php echo SITE_URL; ?>images/skrill_icon.png" border="0" /><?php } ?>
			<?php if (AUTHORIZE_LOGIN != "" && AUTHORIZE_TRANSACTION_KEY != "") { ?><img src="<?php echo SITE_URL; ?>images/authorizenet_icon.png" border="0" /><?php } ?>			
				<br>
				<input type="hidden" name="action" value="deposit" />
				<input type="submit" class="btn btn-success btn-lg" name="deposit" id="deposit" value="Deposit Funds" />
				<a class="btn btn-default btn-lg" href="#" onclick="history.go(-1);return false;">Cancel</a>
			</div>
		</div>
		<?php } ?>
		</form>
		</div>

	<?php } ?>
	
        </div>
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>