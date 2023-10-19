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

	$amount = DisplayMoney(MIN_PAYOUT, $hide_currency = 1);

	if (isset($_POST['amount']) && is_numeric($_POST['amount'])) $amount = mysqli_real_escape_string($conn, getPostParameter('amount'));

	if (isset($_POST['payment_method']) && is_numeric($_POST['payment_method']))
	{
		$payment_method_id = (int)getPostParameter('payment_method');
		$payment_method_result = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_id='$payment_method_id' AND pmethod_type='withdraw' AND status='active' LIMIT 1");
		if (mysqli_num_rows($payment_method_result) > 0)
		{
			$payment_method_row = mysqli_fetch_array($payment_method_result);
			$payment_method_name = mysqli_real_escape_string($conn, $payment_method_row['pmethod_title']);
			$withdraw_fee = $payment_method_row['fee'];
			$withdraw_details = $payment_method_row['pmethod_details'];
		}
	}

	if (isset($_POST['withdraw']) && $_POST['withdraw'] != "")
	{
		unset($errs);
		$errs = array();

		$amount					= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$payment_method			= (int)getPostParameter('payment_method');
		$payment_details		= mysqli_real_escape_string($conn, nl2br(getPostParameter('payment_details')));
		$current_balance		= GetUserBalance($userid, 1);

		if (!(is_numeric($amount) && $amount > 0))
		{
			$errs[] = "Please enter correct amount";
			$amount = "";
		}
		elseif (!(isset($payment_method) && $payment_method != 0))
		{
			$errs[] = "Please select payment method";
		}
		elseif (!(isset($payment_details) && $payment_details != ""))
		{
			$errs[] = "Please enter payment details";
		}
		else
		{
			if ($amount > $current_balance)
			{
				$errs[] = "Sorry, you do not have enough money";
			}
			else
			{
				if ($amount < MIN_PAYOUT)
				{
					$errs[] = "The minimum amount:  ".DisplayMoney(MIN_PAYOUT);
				}

				if (!MoneyEnough($userid))
				{
					$errs[] = "You do not have enough money";
				}
			}
		}

		if (count($errs) == 0)
		{
			$reference_id = GenerateReferenceID();

			// add withdraw fee
			if ($withdraw_fee != "" && is_numeric($withdraw_fee) && $withdraw_fee > 0)
			{
				$transaction_fee = CalculatePercentage($amount, $withdraw_fee);
			}

			smart_mysql_query($rp_query = "INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$userid', payment_type='withdraw', payment_method='$payment_method_name', payment_details='$payment_details', transaction_fee='$transaction_fee', amount='$amount', status='request', created=NOW()");
		
			// send notification
			if (NEW_WITHDRAW_ALERT == 1)
			{
				$subject = "New withdraw request";
				$message = "New withdraw request";

				SendEmail(SITE_MAIL, $subject, $message, $noreply_mail = 1);
			}

			header("Location: withdraw.php?msg=sent");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br>";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Withdraw Funds";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
		<?php /*<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>*/ ?>

		<?php if (!(isset($_GET['msg']) && $_GET['msg'] == "sent")) { ?>
		<div class="abalance pull-right text-center">
			Available Balance<br/>
			<img src="<?php echo SITE_URL; ?>images/icon_money.png">
			<h2 class="text-center" style="color: #01a650"><span><?php echo GetUserBalance($userid); ?></span></h2>
        </div>
        <?php } ?>
  
	<div class="container">
	<div class="row">        
        <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
        
		<h1 class="text-center"><i class="fa fa-download" style="color: #35b2d6"></i> Withdraw Funds</h1>

		<?php if (isset($_GET['msg']) && $_GET['msg'] == "sent") { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle-o fa-lg"></i> Thanks for requesting to withdraw money, your request will be processed soon.</div>
		<?php }else{ ?>

		<?php if (GetUserBalance($userid, $hide_currency_sign = 1) < MIN_PAYOUT) { ?>
		<div class="alert alert-info text-center">
			Sorry, you don't have enough funds to request payout yet.<br/>
			You can request payout only if your current account balance more than <b><?php echo DisplayMoney(MIN_PAYOUT); ?></b>.
		</div>
		<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-chevron-left"></i> Go Back</a></p>
		<?php }else{ ?>

			<div style="background: #fcfcfc; border: 1px solid #F5F5F5; border-radius: 10px; padding: 10px;">
			<?php if (isset($allerrors)) { ?><div class="alert alert-danger"><?php echo $allerrors; ?></div><?php } ?>
			<form action="" method="post" name="wform">
			<?php if (@$withdraw_fee > 0) { ?>
			<div class="row form-group">
				<label class="col-sm-3 label-title">Fee:</label>
				<div class="col-sm-9"><?php echo $withdraw_fee; ?>%</div>
			</div>
			<?php } ?>
			<div class="row form-group">
				<label class="col-sm-3 label-title">Amount:</label>
				<div class="col-sm-9">
					<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>					
						<input type="text" class="form-control" name="amount" value="<?php echo @$amount; ?>" size="7" />
					</div>
				</div>
			</div>
			<div class="row form-group">
				<label class="col-sm-3 label-title">Method:</label>
				<div class="col-sm-9">
					<select class="form-control" name="payment_method" onchange="this.form.submit();">
						<option value="">-- Select payment method --</option>
						<?php

							$sql_pmethods = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_type='withdraw' AND status='active' ORDER BY pmethod_title");
							
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
			<?php if (@$withdraw_details != "") { ?>
			<div class="row form-group">
				<label class="col-sm-3 label-title">&nbsp;</label>
				<div class="col-sm-9">
					<div class="alert alert-info"><?php echo $withdraw_details; ?><br><center><i class="fa fa-arrow-circle-down fa-2x" style="color: #1c90c6"></i></center></div>	
					<textarea name="payment_details" cols="45" rows="6" class="form-control"><?php echo getPostParameter('payment_details'); ?></textarea>
				</div>
			</div>
			<?php } ?>
			<div class="row form-group">
				<label class="col-sm-3 label-title">&nbsp;</label>
				<div class="col-sm-9">
					<input type="hidden" name="action" value="withdraw" />
					<input type="submit" class="btn btn-success btn-lg" name="withdraw" id="withdraw" value="Withdraw" />
					<a class="btn btn-default btn-lg" href="#" onclick="history.go(-1);return false;">Cancel</a>
				</div>
			</div>
			</form>
			</div>
			
		<?php } ?>

	<?php } ?>
	
        </div>
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>