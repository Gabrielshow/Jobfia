<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");

	//Setting information about the transaction
	$MerchantEmailAddress	= $_POST['merchant_id'];	
	$payment_id				= mysqli_real_escape_string($conn, getPostParameter('transaction_id'));
	$purchaseType			= $_POST['ap_purchasetype'];
	$currency				= mysqli_real_escape_string($conn, getPostParameter('mb_currency')); 	
	$amount					= (float)getPostParameter('mb_amount');

	// Validate the Skrill signature
	$concatFields = $_POST['merchant_id']
		.$_POST['transaction_id']
		.strtoupper(md5(SKRILL_SECURITY_CODE))
		.$_POST['mb_amount']
		.$_POST['mb_currency']
		.$_POST['status'];

	// Ensure the signature is valid, the status code == 2
	if (strtoupper(md5($concatFields)) == $_POST['md5sig'] && $_POST['status'] == 2 && $_POST['pay_to_email'] == SKRILL_ACCOUNT)
	{
		// Valid transaction.
		$check_result = smart_mysql_query("SELECT * FROM jobfia_transactions WHERE transaction_id='$payment_id' AND amount='$amount' AND currency='$currency' AND status='pending' LIMIT 1");
		if (mysqli_num_rows($check_result) != 0)
		{
			$payment_row = mysqli_fetch_array($check_result);

			// Confirm transaction
			smart_mysql_query("UPDATE jobfia_transactions SET status='paid' WHERE transaction_id='$payment_id' AND status='pending' LIMIT 1");

			// Update member's balance
			smart_mysql_query("UPDATE jobfia_users SET balance=balance+".mysqli_real_escape_string($conn, $payment_row['amount'])." WHERE user_id='".(int)$payment_row['user_id']."' LIMIT 1");
			
			header ("Location: payment_success.php");
			exit();
		}
	}
	else
	{
		// Invalid transaction
		header ("Location: payment_cancelled.php");
		exit();
	}
	
?>