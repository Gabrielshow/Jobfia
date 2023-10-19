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


	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		$currency_name	= mysqli_real_escape_string($conn, getPostParameter('currency_name'));
		$currency		= mysqli_real_escape_string($conn, trim($_POST['currency']));
		$currency_code	= mysqli_real_escape_string($conn, getPostParameter('currency_code'));

		if ($currency_name && $currency && $currency_code)
		{
			$check_query = smart_mysql_query("SELECT * FROM jobfia_currencies WHERE currency_code='$currency_code'");
			if (mysqli_num_rows($check_query) == 0)
			{
				$sql = "INSERT INTO jobfia_currencies SET currency_name='$currency_name', currency='$currency', currency_code='$currency_code', status='active'";
				if (smart_mysql_query($sql))
				{
					header("Location: currencies.php?msg=added");
					exit();
				}
			}
			else
			{
				header("Location: currencies.php?msg=exists");
				exit();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == "delete")
	{
		$id = (int)$_GET['id'];

		if ($id > 36)
		{
			smart_mysql_query("DELETE FROM jobfia_currencies WHERE currency_id='$id'");
			header("Location: currencies.php?msg=deleted");
			exit();
		}
	}


	$query = "SELECT * FROM jobfia_currencies ORDER BY currency_id";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Currencies";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a href="javascript:void(0);" class="search" onclick="$('#add_currency').toggle('slow');$('#list_box').toggle('fast');">Add Currency</a></div>

		<h2><i class="fa fa-money" style="color: #5cb85c"></i> Currencies</h2>

		<div id="add_currency" style="width: 450px; background: #F7F7F7; padding: 10px; margin: 0 auto; border-radius: 5px; <?php if (!(isset($_POST['action']) && $_POST['action'] == "add")){ ?>display: none;<?php } ?>">
		  <form action="" method="post">
		  <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
          <tr>
            <td width="80" valign="middle" align="right" class="tb1">Currency Name:</td>
			<td align="left"><input type="text" name="currency_name" id="currency_name" value="<?php echo getPostParameter('currency_name'); ?>" size="17" class="form-control" required="required" /><span class="note">e.g. US Dollar</span></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Sign:</td>
			<td align="left"><input type="text" name="currency" id="currency" value="<?php echo getPostParameter('currency'); ?>" size="5" class="form-control" required="required" /><span class="note">e.g. $</span></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Currency Code:</td>
			<td align="left"><input type="text" name="currency_code" id="currency_code" value="<?php echo getPostParameter('currency_code'); ?>" size="5" class="form-control" required="required" /><span class="note">USD</span> &nbsp; <a href="http://currencysystem.com/codes/" target="_blank">get codes</a></td>
          </tr>
		  <tr>
			<td align="left">&nbsp;</td>
			<td align="left">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Currency" />
				<a href="javascript:void(0);" class="btn btn-default" onclick="$('#add_currency').hide('fast');$('#list_box').toggle('fast');">Cancel</a>
		    </td>
          </tr>
		  </table>
		  </form>
		</div>

        <?php if ($total > 0) { ?>

		<div id="list_box">
			<table style="border-bottom: 1px solid #F7F7F7;" align="center" width="380" border="0" cellpadding="3" cellspacing="0">
			<tr align="center">
				<td height="15" colspan="6" valign="middle">
					<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
					<div style="width:400px;" class="alert alert-success">
						<?php
							switch ($_GET['msg'])
							{
								case "added":	echo "Currency was successfully added"; break;
								case "exists":	echo "Sorry, currency exists"; break;
								case "updated": echo "Currency has been successfully edited"; break;
								case "deleted": echo "Currency has been successfully deleted"; break;
							}
						?>
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr bgcolor="#F7F7F7" align="center">
				<th width="3%">&nbsp;</th>
				<th width="45%">Currency</th>
				<th width="18%">Sign</th>
				<th width="18%">Code</th>
				<th width="3%">&nbsp;</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle">&nbsp;</td>
					<td height="20" nowrap="nowrap" align="left" valign="middle"><?php echo $row['currency_name']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['currency']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['currency_code']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
					<?php if ($row['currency_id'] > 36) { ?>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this currency?') )location.href='currencies.php?id=<?php echo $row['currency_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					<?php } ?>
					</td>
				</tr>
			<?php } ?>
            </table>
		</div>
          
		  <?php }else{ ?>
					<div class="alert alert-info">There are no currencies at this time.</div>
          <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>