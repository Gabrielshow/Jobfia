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


	if (PREMIUM_ACCOUNTS != 1)
	{
		header("Location: myaccount.php");
		exit();
	}

	if (isset($_POST['upgrade']) && $_POST['upgrade'] != "")
	{
		unset($errs);
		$errs = array();

		if (isPremium($userid))
		{
			$errs[] = "Your account is premium";
		}

		if (GetUserBalance($userid, $hide_currency_sign = 1) < PREMIUM_FEE)
		{
			$errs[] = "You don't have enough funds to upgrade your account";
		}

		if (count($errs) == 0)
		{
			$reference_id = GenerateReferenceID();
			smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$userid', payment_type='account_upgrade', payment_method='Account Balance', amount='".PREMIUM_FEE."', status='paid', created=NOW()");

			// deduct money from user account
			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".PREMIUM_FEE." WHERE user_id='$userid' LIMIT 1");
			// upgrade account
			smart_mysql_query("UPDATE jobfia_users SET premium='1' WHERE user_id='$userid' LIMIT 1");
		
			header("Location: upgrade_account.php?msg=done");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}

	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Upgrade Account";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	require_once ("inc/usermenu.inc.php");

?>
	<div id="column_center">

		<h1><i class="fa fa-user" style="color: #F49C04"></i> Upgrade Account</h1>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<i class="fa fa-check-circle fa-lg"></i> 
				<?php
					switch ($_GET['msg'])
					{
						case "done": echo "Congratulations! Your account was upgraded to premium!"; break;
					}
				?>
			</div>
		<?php }else{ ?>

		<?php if (isPremium($userid)) { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle fa-lg"></i> Your account is <span style="color: #F49C04"><b>premium</b></span>.</div>
		<?php }else{ ?>

			<?php if (GetUserBalance($userid, $hide_currency_sign = 1) < PREMIUM_FEE) { ?>
			<div class="alert alert-warning">
				<p>Sorry, you don't have enough funds to upgrade your account to <span style="color: #F49C04">premium</span>. Your current balance: <b><?php echo GetUserBalance($userid); ?></b></p>
				<p>You can earn money by complete <a href="<?php echo SITE_URL; ?>jobs.php">jobs</a>. Also you can <a href="<?php echo SITE_URL; ?>deposit.php">deposit money</a> to your account.</p>
			</div>
			<?php } ?>

			<p><i class="fa fa-user" style="color: #F49C04"></i> Premium account fee: <b><?php echo DisplayMoney(PREMIUM_FEE); ?></b></p>
			<p>Premium workers can accept more paying jobs and have a lot of additional benefits. So why not to upgrade your account.</p>
			<?php if (PREMIUM_DAILY_APPLICATIONS > 0) { ?>
				<p>Also with premium account will be able to apply for <b><?php echo PREMIUM_DAILY_APPLICATIONS; ?></b> jobs per day!</p>
			<?php } ?>

			<?php if (isset($allerrors)) { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>

			<?php if (GetUserBalance($userid, $hide_currency_sign = 1) >= PREMIUM_FEE) { ?>
			<form action="" method="post" name="uform">
			<table cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td align="left" valign="top"><input type="submit" class="btn btn-success" name="upgrade" id="upgrade" value="Upgrade account to premium (<?php echo DisplayMoney(PREMIUM_FEE); ?>)" /></td>
			</tr>
			</table>
			</form>
			<?php } ?>
		
		<?php } ?>

	<?php } ?>

	</div>


<?php require_once ("inc/footer.inc.php"); ?>