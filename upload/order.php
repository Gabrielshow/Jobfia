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


	if (isset($_POST['action']) && $_POST['action'] == "checkout")
	{
		unset($errs);
		$errs = array();

		$job_id			= (int)getPostParameter('job_id');
		$instructions	= mysqli_real_escape_string($conn, nl2br(getPostParameter('instructions')));
		//$worker_ip	= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!(isset($instructions) && $instructions != ""))
		{
			$errs[] = "Please enter required instructions";
		}
		else
		{
			$check_job = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE $where job_type='offer' AND job_id='$job_id' AND status='active' LIMIT 1");
			if (mysqli_num_rows($check_job) == 0)
			{
				//job not found
				header ("Location: index.php");
				exit();
			}
			else
			{
				$job_row = mysqli_fetch_array($check_job);
			}

			/*
				if (!isAvaliableJob($job_id))
			{
				$errs[] = "Sorry, no available items for this offer at this time.";
			}*/

			if (isCompleteJob($job_id, $userid) && $job_row['few_times'] != 1)
			{
				$errs[] = "You can purchase this offer once. <a href='".SITE_URL."offers.php'>Find other offers</a>";
			}

			if (isOwner($job_id, $userid))
			{
				$errs[] = "Sorry, you can not order your own offer";
			}

			/*if ($job_row['is_proof_file'] == 1 && !$_FILES['proof_file']['tmp_name'])
			{
				$errs[] = "Please upload proof file";
			}*/
		}


		if (count($errs) == 0)
		{
			$job_row		= mysqli_fetch_array(smart_mysql_query("SELECT * FROM jobfia_jobs WHERE job_id='$job_id' AND status='active' LIMIT 1"));
			$job_title		= mysqli_real_escape_string($conn, $job_row['title']);
			$job_price		= mysqli_real_escape_string($conn, $job_row['price']);
			$reference_id 	= GenerateReferenceID();

			smart_mysql_query("INSERT INTO jobfia_orders SET reference_id='$reference_id', user_id='$userid', offer_id='$job_id', offer_title='$job_title', amount='$job_price', comment='$instructions', status='pending', created=NOW()");

			/*
				// send new order notification to user
				SendOrderNotification($job_id);
			*/
			
			smart_mysql_query("UPDATE jobfia_users SET balance=balance-".($job_price+2)." WHERE user_id='$userid' LIMIT 1"); //dev //fee

			header("Location: payment_success.php?offer=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>\n";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$job_id = (int)$_GET['id'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	$query = "SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE $where job_id='$job_id' AND job_type='offer' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Order an Offer";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<div class="job-postdetails2">
				<div class="row">	
					<div class="col-md-8">
						<div class="section2">


			<h1 style="border-bottom: 2px solid #f5f5f5"><i class=" fa fa-shopping-cart" style="color: #01a650"></i>  Order an Offer</h1>

			<?php if (isset($allerrors)) { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>

			<div class="row">
				<div class="col-md-3">
					<img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="" style="border-radius: 5px">
				</div>
				<div class="col-md-7">
					<h4><?php echo $row['title']; ?></h4>
					<i class="fa fa-star fa-lg" style="color: #ffbf00"></i><i class="fa fa-star fa-lg" style="color: #ffbf00"></i><i class="fa fa-star fa-lg" style="color: #ffbf00"></i><i class="fa fa-star fa-lg" style="color: #ffbf00"></i><i class="fa fa-star fa-lg" style="color: #ffbf00"></i>
				</div>
			</div>
			<hr>


			<h3>About This Offer</h3>
			
			<p class="description">
				<?php echo stripslashes($row['description']); ?>				
			</p>
			
			<?php if (GetUserBalance($userid, $hide_currency_sign = 1) >= ($row['price'] + 2)) { ?>
			<div class="row">
			<form action="" method="post">
			 <div class="col-md-12">
				 <h3>Requirements</h3>
				 <textarea name="instructions" rows="8" class="form-control" placeholder="<?php echo stripslashes($row['instructions']); ?>" required><?php echo getPostParameter('instructions'); ?></textarea>
			<p>
								<!--<br><button type="submit" class="btn btn-success btn-lg"><i class="fa fa-shopping-cart"></i> Checkout</button> &nbsp; <a class="btn btn-default btn-lg" href="<?php echo SITE_URL; ?>offers.php"><i class="fa fa-times"></i> Cancel</a>-->
			</p>							 
			</div>

			
			</div>
			<?php } ?>
	
	</div>
	</div>
		<div class="col-md-4">
			<div class="section" style="border: 1px solid #eee; background: #fcfcfc; border-radius: 8px">
				<h2 class="brd"><i class="fa fa-list" style="color: #01a650"></i> Order Details</h2>
				
<table width="100%" cellpadding="3" cellspacing="2"><tr><td width="65%"><span>Subtotal</span></td><td><span class="price"><?php echo DisplayMoney($row['price']); ?></span></td></tr>
<tr><td><div class="payment-summary-item padding"><div class="processing-fee"><div><span>Service Fee <span class="jobfia_tooltip" title="This helps us operate our platform and offer 24/7 customer support for your orders"><i class="fa fa-question-circle"></i></span></span></td><td></div><span class="price">$2</span></div></div></td></tr><tr><td><hr><h4>Total</h4></td><td><hr><span class="price" style="color: #01a650"><h4><?php echo DisplayMoney($row['price'] + 2); ?></h4></span></div></td></tr><tr><td><div class="payment-summary-item padding"><span><i class="fa fa-clock-o"></i> Delivery Time</span></td><td> <span><!-- react-text: 96 --><!-- /react-text --><span><?php echo ConvertDuration($row['duration']); ?></span></span></div></td></tr></table>


		<?php if (GetUserBalance($userid, $hide_currency_sign = 1) >= ($row['price'] + 2)) { ?>

				<div class="payment-summary-item padding"><br><div class="text-center"><button class="btn btn-success btn-lg btn-block" type="submit"><i class="fa fa-shopping-cart"></i> Pay Now</button><center><br>You won't be charged yet</center><br></div></div>

				
				<input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>" />
				<input type="hidden" name="action" value="checkout" />


			</form>

			
			<p class="text-center">
				<img src="<?php echo SITE_URL; ?>images/pay-logos.png"><br><br>
				<b><span style="color: #01b22e"><i class="fa fa-lock"></i> SSL</span> SECURED PAYMENT</b><br>
				Your information is protected by 256-bit SSL encryption
				
			</p>

		<?php }else{ ?>
				<br>
				<div class="alert alert-warning text-center">
					<h4>You don't have enough money!</h4>
					Your Account Balance: <b><?php echo GetUserBalance($userid); ?></b><br><br>
					<a class="btn btn-info" href="<?php echo SITE_URL; ?>deposit.php"><i class="fa fa-upload"></i> Deposit Funds</a> 
				</div>
		<?php } ?>
		
		

</div>			
				
			</div>

		</div>
	</div>
	</div>
	

	<?php }else{ ?>
		<h1>Offer not found</h1>
		<div class="alert alert-info text-center"><p>Sorry, no offer found. <a class="btn btn-info" href="<?php echo SITE_URL; ?>offers.php">All offers</a></p></div>
	<?php } ?>	
	



<?php require_once ("inc/footer.inc.php"); ?>