<?php
/*******************************************************************\
 * Jobfia v3.0
 * http://www.Jobfia.com
 *
 * Copyright (c) 2012-2015 Jobfia Software. All rights reserved.
  * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");

	// for test: www.sandbox.paypal.com
	// for live: www.paypal.com

	// PayPal settings
	$paypal_email	= PAYPAL_ACCOUNT;
	$payment_id		= (int)getPostParameter('custom');
	$return_url		= SITE_URL.'payment_success.php';
	$cancel_url		= SITE_URL.'payment_cancelled.php';
	$notify_url		= SITE_URL.'paypal_ipn.php';	
	$amount			= (float)getPostParameter('amount');
	$item_name		= "Deposit: ".DisplayMoney($amount);

	###  Paypal request ###
	###########################################################################################
	if (isset($_POST['action']) && $_POST['action'] == "deposit" && !isset($_POST["txn_id"]) && !isset($_POST["txn_type"]))
	{
		// Firstly Append paypal account to querystring
		$querystring .= "?business=".urlencode($paypal_email)."&";	
	
		// Append amount & currency to quersytring so it cannot be edited in html
	
		//The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
		$querystring .= "item_name=".urlencode($item_name)."&";
		//$querystring .= "amount=".urlencode($amount)."&";
		$querystring .= "currency_code=".urlencode(SITE_CURRENCY_CODE)."&";
	
		//loop for posted values and append to querystring
		foreach($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$querystring .= "$key=$value&";
		}
	
		// Append paypal return addresses
		$querystring .= "return=".urlencode(stripslashes($return_url))."&";
		$querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
		$querystring .= "notify_url=".urlencode($notify_url);
	
		// Append querystring with custom field
		$querystring .= "&custom=".$payment_id;
	
		// Redirect to paypal IPN
		header('Location: https://www.paypal.com/cgi-bin/webscr'.$querystring);
		exit();
	}


	###  Paypal response  ###
	###########################################################################################
	if (isset($_POST["txn_id"]) && isset($_POST["txn_type"]))
	{
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value); // IPN fix
			$req .= "&$key=$value";
		}
	
		// assign posted variables to local variables
		$data['txn_id']				= $_POST['txn_id'];
		$data['business']			= mysqli_real_escape_string($conn, getPostParameter('business'));
		$data['item_name']			= $_POST['item_name'];
		$data['item_number'] 		= $_POST['item_number'];
		$data['payment_amount'] 	= (float)getPostParameter('mc_gross');
		$data['payment_currency']	= mysqli_real_escape_string($conn, getPostParameter('mc_currency'));
		$data['payment_status'] 	= mysqli_real_escape_string($conn, getPostParameter('payment_status'));
		$data['receiver_email'] 	= mysqli_real_escape_string($conn, getPostParameter('receiver_email'));
		$data['payer_email'] 		= mysqli_real_escape_string($conn, getPostParameter('payer_email'));
		$data['custom'] 			= (int)getPostParameter('custom');
		
		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);	
	
		if (!$fp)
		{
			// HTTP ERROR
			header ("Location: payment_cancelled.php");
			exit();
		}
		else
		{
			fputs ($fp, $header . $req);

			while (!feof($fp))
			{
				$res = fgets ($fp, 1024);

				if (strcmp($res, "VERIFIED") == 0)
				{
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment

					$payment_id			= mysqli_real_escape_string($conn, $data['custom']);
					$payment_amount		= mysqli_real_escape_string($conn, $data['payment_amount']);
					$payment_currency	= mysqli_real_escape_string($conn, $data['payment_currency']); 

					$check_result = smart_mysql_query("SELECT * FROM jobfia_transactions WHERE transaction_id='$payment_id' AND amount='$payment_amount' AND currency='$payment_currency' AND status='pending' LIMIT 1");
							
					// PAYMENT VALIDATED & VERIFIED!
					if (mysqli_num_rows($check_result) != 0 && $data['receiver_email'] == PAYPAL_ACCOUNT)
					{
						$payment_row = mysqli_fetch_array($check_result);

						// Confirm transaction
						$payment_query = "UPDATE jobfia_transactions SET status='paid' WHERE transaction_id='$payment_id' AND status='pending' LIMIT 1";

						// Update member's balance
						smart_mysql_query("UPDATE jobfia_users SET balance=balance+".$payment_row['amount']." WHERE user_id='".(int)$payment_row['user_id']."' LIMIT 1");
						
						// Payment has been made & member's balance successfully updated
						if (smart_mysql_query($payment_query))
						{
							header ("Location: payment_success.php");
							exit();
						}
						else
						{
							// Error inserting into DB
							//@mail(SITE_MAIL, "PAYPAL - Error inserting into Database", "Error inserting into DB<br />data = <pre>".print_r($_POST, true)."</pre>");
						}
					}
					else
					{
						header ("Location: payment_cancelled.php");
						exit();
					}
				}
				elseif (strcmp ($res, "INVALID") == 0)
				{
					header ("Location: payment_cancelled.php");
					exit();
				}
			}
			fclose ($fp);
		}
	}

?>