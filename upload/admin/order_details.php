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


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$order_id	= (int)$_REQUEST['id'];
		$offer_id	= (int)$_REQUEST['offer_id'];
		$pn			= (int)$_REQUEST['pn'];

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS order_date FROM jobfia_orders WHERE offer_id='$offer_id' AND order_id='$order_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	if (isset($_POST['action']) && $_POST['action'] == "modify")
	{
		unset($errs);
		$errs = array();

		$order_id		= (int)getPostParameter('id');;
		$comment		= mysqli_real_escape_string($conn, nl2br(getPostParameter('comment')));
		$status			= mysqli_real_escape_string($conn, getPostParameter('mark_as'));

		if (isset($comment) && $comment != "")
		{		
			smart_mysql_query("UPDATE jobfia_orders SET comment='$comment' WHERE order_id='$order_id' LIMIT 1");
		}

		if (count($errs) == 0)
		{
			switch($status)
			{
				//case "confirm": ConfirmTask($job_task_id); break;
				//case "decline": DeclineTask($job_task_id); break;
				//case "incomplete": NotCompleteTask($job_task_id); break;
			}

			header("Location: offer_orders.php?msg=updated&id=".$offer_id);
			exit();
		}
		else
		{
			foreach ($errs as $errorname)
			{
				$errormsg .= "&#155; ".$errorname."<br/>\n";
			}
		}
	}

	$title = "Order Details";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

			<h2><i class="fa fa-shopping-cart"></i> Order Details #<?php echo $row['reference_id']; ?></h2>
			<h3>I will <a href="offer_details.php?id=<?php echo $row['offer_id']; ?>"><?php echo $row["offer_title"]; ?></a></h3>

			<?php if (isset($errormsg)) { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

			<table width="100%" cellpadding="3" cellspacing="2" border="0" align="center">
			<tr>
				<td height="80" width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo DisplayMoney($row['amount']); ?></span> <i class="fa fa-money fa-lg"></i> amount</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo GetUsername($row['user_id'], $username=1); ?></span> <i class="fa fa-user-circle-o fa-lg"></i> customer</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num"><?php echo $row['order_date']; ?></span> <i class="fa fa-clock-o fa-lg"></i> date/time</td>
				<td width="20%" bgcolor="#F7F7F7" align="center"><span class="num">
				<?php
					switch ($row['status'])
					{
						case "notcomplete": echo "<span class='label label-default'>".$row['status']."</span>"; break;
						case "pending": echo "<span class='label label-warning'>in process</span>"; break;							
						case "declined": echo "<span class='label label-danger'>".$row['status']."</span>"; break;
						case "confirmed": echo "<span class='label label-success'>".$row['status']."</span>"; break;
					}
				?>				
				</span> status
				</td>
			</tr>
			</table>

			<form action="" method="post">
			<table width="100%" style="background: #fcfcfc" align="center" border="0" cellspacing="0" cellpadding="4">
			<?php if ($row['comment'] != "") { ?>
			<tr>
				<td colspan="2" align="left" valign="top">
					<br>
					<h3>Comment from employer:</h3>
					<textarea name="comment" cols="55" rows="5" class="form-control" /><?php echo strip_tags($row['comment']); ?></textarea>
				</td>
			</tr>
			<?php } ?>
			<?php if ($row['status'] != "confirmed") { ?>
			<tr>
				<td width="100" align="left">Mark as:</td>
				<td align="left">
						<select name="mark_as">
							<option value="">--------------</option>
							<option value="confirm" style="background:#82ED09;color:#FFF;">Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">Not Complete</option>
							<option value="decline" style="background:#FC2020;color:#FFF;">Not Satisfied</option>
						</select>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" align="left">
					<br>
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="id" value="<?php echo $row['order_id']; ?>" />
					<input type="hidden" name="offer_id" value="<?php echo $row['offer_id']; ?>" />
					<input type="hidden" name="action" value="modify" />
					<?php if ($row['status'] != "confirmed") { ?>
						<input type="submit" class="btn btn-success" value="Update Order" />
						<input type="button" class="btn btn-default" name="goback" value="Cancel" onclick="history.go(-1);return false;" />
					<?php }else{ ?>
						<input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;" />
					<?php } ?>
				</td>
			</tr>
           </table>
		   </form>

	  <?php }else{ ?>
	  	<h2><i class="fa fa-check-circle-o"></i> Order Details</h2>
			<div class="alert alert-info text-center">Sorry, order not found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>