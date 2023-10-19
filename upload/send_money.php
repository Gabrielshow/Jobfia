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

	if (SEND_BONUS != 1)
	{
		header("Location: index.php");
		exit();
	}


	$amount = DisplayMoney(MIN_BONUS_PAYMENT, $hide_currency = 1);
	

	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && $_REQUEST['id'] != $userid)
	{
		$recipient_id = (int)$_REQUEST['id'];
	}


	if (isset($_POST['amount']) && is_numeric($_POST['amount'])) $amount = mysqli_real_escape_string($conn, getPostParameter('amount'));

	if (isset($_POST['send']) && $_POST['send'] != "")
	{
		unset($errs);
		$errs = array();
	
		if (isset($_POST['recipient']) && is_numeric($_POST['recipient']))
			$recipient = (int)getPostParameter('recipient');
		else
			$recipient = (int)$recipient_id;

		$amount				= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$payment_note		= mysqli_real_escape_string($conn, nl2br(substr(getPostParameter('payment_note'), 0, 300)));
		$current_balance	= GetUserBalance($userid, 1);

		if (!($amount && $recipient))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if (!(is_numeric($amount) && $amount > 0))
			{
				$errs[] = "Please enter correct amount";
				$amount = "";
			}
			else
			{
				if ($amount > $current_balance)
				{
					$errs[] = "Sorry, there is not enough money in your account";
				}
				else
				{
					if ($amount < MIN_BONUS_PAYMENT)
					{
						$errs[] = "The minimum amount for request payout is ".DisplayMoney(MIN_BONUS_PAYMENT);
					}

					if ($recipient == $userid)
					{
						$errs[] = "You can not send money to yourself";
					}

					if (SIGNUP_BONUS > 0 && isSignUpBonus($userid))
					{
						$errs[] = "You can not send your Sign Up bonus money";
					}

					if (!MoneyEnough($userid))
					{
						$errs[] = "There is not enough money in your account";
					}

					// check if user exists //
					$check_query = "SELECT username FROM jobfia_users WHERE user_id='$recipient' AND status='active' LIMIT 1";
					$check_result = smart_mysql_query($check_query);

					if (mysqli_num_rows($check_result) == 0)
					{
						$errs[] = "Sorry, user does not exist";
					}
					/////////////////////////
				}
			}
		}

		if (count($errs) == 0)
		{
			$reference_id = GenerateReferenceID();

			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".$amount." WHERE user_id='$userid' LIMIT 1");
			if (is_numeric(BONUS_FEE) && BONUS_FEE > 0) $amount -= CalculatePercentage($amount, BONUS_FEE);
			smart_mysql_query("UPDATE jobfia_users SET balance=balance+".$amount." WHERE user_id='$recipient' LIMIT 1");

			smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$userid', recipient_id = '$recipient', payment_type='send_money', payment_details='$payment_note', amount='$amount', status='paid', created=NOW()");

			header("Location: send_money.php?msg=sent");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>\n";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Send a bonus payment";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<?php if (!(isset($_GET['msg']) && $_GET['msg'] == "sent")) { ?>
		<div class="abalance pull-right text-center">
			My Wallet<br/>
			<img src="<?php echo SITE_URL; ?>images/icon_money.png">
			<h2 class="text-center" style="color: #01a650"><span><?php echo GetUserBalance($userid); ?></span></h2>
			<a class="btn btn-info" href="<?php echo SITE_URL; ?>deposit.php">+ Deposit Money</a>
        </div>
    <?php } ?>


	<div class="container">
	<div class="row">        
        <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
        
		<h1><i class="fa fa-dollar" style="color: #01a650"></i> Send a bonus payment</h1>

	<?php if (isset($_GET['msg']) && $_GET['msg'] == "sent") { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle-o fa-lg"></i> Thank you for sending money. Your payment has been sent!</div>
	<?php }else{ ?>

		<div class="well">
			You can send a bonus payment to anybody using your <?php echo SITE_TITLE; ?> account.<br/>
			Enter the amount you want to send and the User ID with which the person you want to send the
			payment to is register on <?php echo SITE_TITLE; ?>, and then click 'Send Money'.
		</div>

		<a name="sendbox"></a>
		<div style="background: #fcfcfc; border-radius: 10px; padding: 10px;">
		
		<?php if (isset($allerrors)) { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
		
		<form action="#sendbox" method="post">
		<?php if (BONUS_FEE > 0) { ?>
		<div class="row form-group">
			<label class="col-sm-3 label-title">Fee:</label>
			<div class="col-sm-9"><b><?php echo BONUS_FEE; ?>%</b></div>
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
		<?php if (isset($recipient_id)) { ?>
		<div class="row form-group">
			<label class="col-sm-3 label-title">Recipient:</label>
			<div class="col-sm-9"><i class="fa fa-user-circle fa-lg"></i> <?php echo GetWorkerInfo($recipient_id, $username = 2); ?> ( <?php echo GetWorkerInfo($recipient_id, 1); ?> )</div>
		</div>
		<?php }else{ ?>
		<div class="row form-group">
			<label class="col-sm-3 label-title"><i class="fa fa-user-circle fa-lg"></i> User ID:</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" name="recipient" value="<?php echo getPostParameter('recipient'); ?>" size="8" />&nbsp; <span class="note jobfia_tooltip" title="you can find User's ID on his profile page"></span>
			</div>
		</div>
		<?php } ?>
		<div class="row form-group">
			<label class="col-sm-3 label-title">Note:<br/><small>(optional)</small></label>
			<div class="col-sm-9">
				<textarea name="payment_note" cols="50" rows="5" class="form-control"><?php echo getPostParameter('payment_note'); ?></textarea>
			</div>
		</div>
		<div class="row form-group">
			<label class="col-sm-3 label-title">&nbsp;</label>
			<div class="col-sm-9">
				<?php if (isset($recipient_id)) { ?><input type="hidden" name="id" value="<?php echo $recipient_id; ?>" /><?php } ?>
				<input type="submit" class="btn btn-success btn-lg" name="send" id="send" value="Send Money" />
				<a class="btn btn-default btn-lg" href="#" onclick="history.go(-1);return false;">Cancel</a>
			</div>
		</div>
		</form>
		</div>

	<?php } ?>
	
        </div>
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>