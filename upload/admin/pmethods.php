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


	$cc = 0;


	$title = "Payment Methods";
	require_once ("inc/header.inc.php");

?>

	<div id="addnew"><a class="addnew" href="pmethod_add.php">Add Payment Method</a></div>

	<h2><i class="fa fa-money" style="color: #5cb85c"></i> Payment Methods</h2>

	<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
	<div class="alert alert-success">
		<?php
			switch ($_GET['msg'])
			{
				case "added":	echo "Payment method was successfully added"; break;
				case "updated": echo "Payment method has been successfully edited"; break;
				case "deleted": echo "Payment method has been successfully deleted"; break;
			}
		?>
	</div>
	<?php } ?>

	<div style="float: left; width: 50%">
	<h1 align="center"><img src="images/icons/deposit.png" align="absmiddle" /> Deposit Methods</h1>

	<?php 

		$query2 = "SELECT * FROM jobfia_pmethods WHERE pmethod_type='deposit' ORDER BY status";
		$result2 = smart_mysql_query($query2);
		$total2 = mysqli_num_rows($result2);

		$dactive_total = mysqli_num_rows(smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_type='deposit' AND status='active'"));

	?>
		<?php if ($dactive_total == 0) { ?>
			<div class="alert alert-danger">Please setup at least one deposit method!</div>
		<?php } ?>

        <?php if ($total2 > 0) { ?>

			<table align="center" width="90%" style="border-bottom: 1px solid #F7F7F7;" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="15%">&nbsp;</th>
				<th width="40%">Payment Method</th>
				<th width="20%">Status</th>
				<th width="25%">Actions</th>
			</tr>
             <?php while ($row2 = mysqli_fetch_array($result2)) { $cc++; ?>		  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle">	
						<?php
							 if ($row2['pmethod_title'] != "")
							 {
								$p_logo = PUBLIC_HTML_PATH."/images/pmethods/".strtolower($row2['pmethod_title']).".png"; 
								echo (file_exists($p_logo)) ? "<img src='/images/pmethods/".strtolower($row2['pmethod_title']).".png'>" : "<i class='fa fa-money fa-2x' style='color: #7ad108'></i>"; 
							 }
							 else
							 {
								echo "---"; 
							 }					
						?>
					</td>
					<td align="left" valign="middle" style="padding-left: 15px">
						<a href="pmethod_edit.php?id=<?php echo $row2['pmethod_id']; ?>"><?php echo $row2['pmethod_title']; ?></a>
					</td>
					<td align="center" valign="middle">
						<?php if ($row2['status'] == "inactive") echo "<span class='label label-default'>".$row2['status']."</span>"; else echo "<span class='label label-success'>".$row2['status']."</span>"; ?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="pmethod_edit.php?id=<?php echo $row2['pmethod_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
					<div class="alert alert-info">There are no deposit methods at this time.</div>
          <?php } ?>
	</div>

	<div style="float: right; width: 50%;">
	<h1 align="center"><img src="images/icons/withdrawal.png" align="absmiddle" /> Withdrawal Methods</h1>

	<?php 

		$query = "SELECT * FROM jobfia_pmethods WHERE pmethod_type='withdraw' ORDER BY status";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		$wactive_total = mysqli_num_rows(smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_type='withdraw' AND status='active'"));

	?>
		<?php if ($wactive_total == 0) { ?>
			<div class="alert alert-danger">Please setup at least one withdrawal method!</div>
		<?php } ?>

        <?php if ($total > 0) { ?>

			<table align="center" width="90%" style="border-bottom: 1px solid #F7F7F7;" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="15%">&nbsp;</th>
				<th width="40%">Payment Method</th>
				<th width="20%">Status</th>
				<th width="25%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>		  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle">
						<?php
							 if ($row['pmethod_title'] != "")
							 {
								$p_logo = PUBLIC_HTML_PATH."/images/pmethods/".strtolower($row['pmethod_title']).".png"; 
								echo (file_exists($p_logo)) ? "<img src='/images/pmethods/".strtolower($row['pmethod_title']).".png'>" : "<i class='fa fa-money fa-2x' style='color: #7ad108'></i>"; 
							 }
							 else
							 {
								echo "---"; 
							 }					
						?>						
					</td>
					<td align="left" valign="middle" style="padding-left: 15px">
						<a href="pmethod_edit.php?id=<?php echo $row['pmethod_id']; ?>"><?php echo $row['pmethod_title']; ?></a>
					</td>
					<td align="center" valign="middle">
						<?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="pmethod_edit.php?id=<?php echo $row['pmethod_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are You sure you really want to delete this payment method?') )location.href='pmethod_delete.php?id=<?php echo $row['pmethod_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
					<div class="alert alert-info">There are no withdrawal methods at this time.</div>
          <?php } ?>
	</div>
	<div style="clear: both"></div>


<?php require_once ("inc/footer.inc.php"); ?>