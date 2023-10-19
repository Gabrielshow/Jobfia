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


	if (isset($_POST["action"]) && $_POST["action"] == "proceed")
	{
		unset($errors);
		$errors = array();

		$username		= mysqli_real_escape_string($conn, getPostParameter('username'));
		$amount			= mysqli_real_escape_string($conn, getPostParameter('amount'));
		$payment_type	= mysqli_real_escape_string($conn, getPostParameter('payment_type'));
		$comment		= mysqli_real_escape_string($conn, getPostParameter('comment'));

		if (!($username && $amount && $payment_type))
		{
			$errors[] = "Please fill in all fields";
		}
		else
		{
			if (!is_numeric($amount))
			{
				$errors[] = "Please enter correct amount";
				$amount = "";
			}
		}

		if (count($errors) == 0)
		{
			if (is_numeric($username) && $username > 0)
				$ures = smart_mysql_query("SELECT user_id FROM jobfia_users WHERE user_id='$username' LIMIT 1");
			else
				$ures = smart_mysql_query("SELECT user_id FROM jobfia_users WHERE username='$username' OR email='$username' LIMIT 1");

			if (mysqli_num_rows($ures) != 0)
			{
				$urow = mysqli_fetch_array($ures);

				$userid			= (int)$urow['user_id'];
				$reference_id	= GenerateReferenceID();

				switch ($payment_type)
				{
					case "deposit":		$balance_sql = "UPDATE jobfia_users SET balance=balance+'".$amount."' WHERE user_id='$userid' LIMIT 1"; break;
					case "withdraw":	$balance_sql = "UPDATE jobfia_users SET balance=balance-'".$amount."' WHERE user_id='$userid' LIMIT 1"; break;
				}

				if ($balance_sql != "") smart_mysql_query($balance_sql);

				smart_mysql_query("INSERT INTO jobfia_transactions SET reference_id='$reference_id', user_id='$userid', payment_type='$payment_type', amount='$amount', reason='$comment', status='confirmed', created=NOW(), process_date=NOW()");

				header("Location: user_money.php?msg=added");
				exit();
			}
			else
			{
				header("Location: user_money.php?msg=notfound");
				exit();
			}
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "<i class='fa fa-times'></i> ".$errorname."<br/>";
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$squery = "SELECT * FROM jobfia_users WHERE user_id='$id' LIMIT 1";
		$sresult = smart_mysql_query($squery); 

		if (mysqli_num_rows($sresult) != 0)
		{
			$srow = mysqli_fetch_array($sresult);
			$username = $srow['username'];
		}
	}

	$title = "Add/Deduct Money";
	require_once ("inc/header.inc.php");

?>

		<h2><img src="images/icons/transfer.png" align="absmiddle" /> Add/Deduct Money</h2>

		<p align="center">From here you can add/deduct amount from users account.</p>

		<?php if (isset($_GET['msg']) && ($_GET['msg']) == "added") { ?>
			<div style="width:78%; margin: 0 auto;" class="alert alert-success"><i class="fa fa-check-circle-o fa-lg"></i> Transaction has been successfully completed</div>
		<?php }elseif(isset($_GET['msg']) && ($_GET['msg']) == "notfound") { ?>
			<div style="width:78%; margin: 0 auto;" class="alert alert-danger">Sorry, member not found</div>
		<?php } ?>

		<?php if (isset($errormsg)) { ?>
			<div style="width:78%; margin: 0 auto;" class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>

			<form action="user_money.php" id="form2" name="form2" method="post">
			<table style="background: #FCFCFC;border-bottom: 1px solid #F7F7F7" align="center" width="70%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="15%">User ID/Username</th>
				<th width="17%">Payment Type</th>
				<th width="17%">Amount</th>
				<th width="20%">Comment (optional)</th>
				<th width="12%"></th>
			</tr>			  
			<tr>
				<td nowrap="nowrap" align="center" valign="middle">
					<input type="text" name="username" id="username" value="<?php echo @$username; ?>" size="29" class="form-control" />
				</td>
				<td nowrap="nowrap" align="center" valign="middle">
					<select name="payment_type" class="form-control">
						<option value="deposit" <?php if (@$payment_type == "deposit") echo "selected='selected'"?>>Deposit</option>
						<option value="withdraw" <?php if (@$payment_type == "withdraw") echo "selected='selected'"?>>Withdraw</option>
					</select>
				</td>
				<td nowrap="nowrap" align="center" valign="middle">
					<div class="input-group"> 
							<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
							<input type="number" name="amount" value="<?php echo @$amount; ?>" size="25" min="0" step="1" data-number-to-fixed="2" data-number-stepfactor="100" class="form-control currency" id="c2" style="width: 100px" />
			    	</div>					
				</td>
				<td nowrap="nowrap" align="center" valign="middle">
					<input type="text" name="comment" id="comment" value="<?php echo GetPostParameter('comment'); ?>" size="29" class="form-control" />
				</td>
				<td nowrap="nowrap" align="center" valign="middle">
					<input type="hidden" name="action" id="action" value="proceed">
					<input type="submit" class="btn btn-success" name="add" id="add" value="Submit" />
				</td>
			 </tr>
            </table>
			</form>


<?php require_once ("inc/footer.inc.php"); ?>