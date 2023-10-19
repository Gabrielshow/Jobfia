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
	$SecurityCode					= $_POST['ap_securitycode'];
	$MerchantEmailAddress			= $_POST['ap_merchant'];	
	$transactionStatus				= $_POST['ap_status'];
	$testModeStatus					= $_POST['ap_test'];	 
	$purchaseType					= $_POST['ap_purchasetype'];
	$currency						= mysqli_real_escape_string($conn, getPostParameter('ap_currency')); 	
	$amount							= (float)getPostParameter('ap_totalamount');	
	$feeAmount						= $_POST['ap_feeamount'];
	$netAmount						= $_POST['ap_netamount'];
	$transactionReferenceNumber		= $_POST['ap_referencenumber'];
	$transactionDate				= $_POST['ap_transactiondate'];
	$transactionType				= $_POST['ap_transactiontype'];
	$payment_id						= (int)getPostParameter('apc_1');

	if ($MerchantEmailAddress != PAYZA_ACCOUNT)
	{
		// The data was not meant for the business profile under this email address.
		header ("Location: index.php");
		exit();
	}
	else
	{	
		// Check if the security code matches
		if ($SecurityCode != PAYZA_SECURITY_CODE)
		{
			// The data is NOT sent by Payza
			header ("Location: index.php");
			exit();
		}
		else
		{
			if ($transactionStatus == "Success")
			{
				if ($testModeStatus == "1")
				{
					// TEST MODE (no transaction reference number will be returned)
					//@mail(SITE_MAIL, "Payza Test Mode", "Response<br />data = <pre>".print_r($_POST, true)."</pre>");
				}
				else
				{
					// This REAL transaction is complete and the amount was paid successfully
					$check_result = smart_mysql_query("SELECT * FROM jobfia_transactions WHERE transaction_id='$payment_id' AND amount='$amount' AND currency='$currency' AND status='pending' LIMIT 1");
					
					// PAYMENT VALIDATED & VERIFIED!
					if (mysqli_num_rows($check_result) != 0)
					{
						$payment_row = mysqli_fetch_array($check_result);

						// Confirm transaction
						$payment_query = "UPDATE jobfia_transactions SET status='paid' WHERE transaction_id='$payment_id' AND status='pending' LIMIT 1";

						// Update member's balance
						smart_mysql_query("UPDATE jobfia_users SET balance=balance+".$payment_row['amount']." WHERE user_id='".(int)$payment_row['user_id']."' LIMIT 1");
								
						if (smart_mysql_query($payment_query))
						{
							header ("Location: payment_success.php");
							exit();
						}
						else
						{
							// Error inserting into DB
							//@mail(SITE_MAIL, "Payza - Error inserting into Database", "Error inserting into DB<br />data = <pre>".print_r($_POST, true)."</pre>");
						}
					}
					else
					{
						// Fake Payment
						header ("Location: payment_cancelled.php");
						exit();
					}
				}			
			}
			else
			{
					// Transaction was cancelled or an incorrect status was returned
					header ("Location: payment_cancelled.php");
					exit();
			}
		}
	}

?>