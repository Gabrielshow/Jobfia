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


	if (isset($_POST['action']) && $_POST['action'] == "editpmethod")
	{
		unset($errs);
		$errs = array();

		$pmethod_id			= (int)getPostParameter('pmethodid');
		$pmethod_title		= mysqli_real_escape_string($conn, getPostParameter('pmethod_title'));
		$fee				= mysqli_real_escape_string($conn, getPostParameter('fee'));
		$pmethod_details	= mysqli_real_escape_string($conn, nl2br(getPostParameter('pmethod_details')));
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($status))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if ($pmethod_id > 4 && !$pmethod_title)
			{
				$errs[] = "Please enter payment method title";
			}

			if (isset($fee) && $fee!="" && !is_numeric($fee))
				$errs[] = "Please enter correct fee";
		}

		if (count($errs) == 0)
		{
			if ($pmethod_id > 4) $title_query = "pmethod_title='$pmethod_title', "; else $title_query = "";
			if ($pmethod_details != "") $details_query = "pmethod_details='$pmethod_details', "; else $details_query = "";
			$sql = "UPDATE jobfia_pmethods SET ".$title_query." ".$details_query." fee='$fee', status='$status' WHERE pmethod_id='$pmethod_id' LIMIT 1";

			if (smart_mysql_query($sql))
			{
				header("Location: pmethods.php?msg=updated");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pmid = (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_pmethods WHERE pmethod_id='$pmid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Edit Payment Method";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><?php echo $row['pmethod_title']; ?></h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<div style="background: #F9F9F9; padding: 10px 0;">
        <form action="" method="post">
          <table width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
		  <?php if ($row['pmethod_id'] > 4) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="middle"><input type="text" name="pmethod_title" id="pmethod_title" value="<?php echo $row['pmethod_title']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">
				<span class="req">* </span>Payment Details:
				<span class="jobfia_tooltip" title="user will need to provide this information to complete the money transfer"><i class="fa fa-info-circle"></i></span>
			</td>
            <td valign="top"><textarea name="pmethod_details" cols="50" rows="7" class="form-control"><?php echo strip_tags($row['pmethod_details']); ?></textarea></td>
          </tr>
		  <?php }else{ ?>
          <tr>
			<td valign="middle" align="left" class="tb1">Settings:</td>
            <td align="left" valign="middle"><i class="fa fa-eye"></i> <i>settings hidden</i> &nbsp; <a href="settings.php#payment">setup</a></td>
          </tr>
		  <?php } ?>
          <tr>
            <td width="13%" valign="middle" align="left" class="tb1"><?php echo ($row['pmethod_type'] == "deposit") ? "Deposit" : "Withdrawal"; ?> Fee:</td>
            <td valign="middle"><input type="text" name="fee" id="fee" value="<?php echo $row['fee']; ?>" size="3" class="form-control" /> %</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="middle">
				<select name="status" class="form-control">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
            <td align="center" valign="top">&nbsp;</td>
	        <td align="left" valign="top">
				<input type="hidden" name="pmethodid" id="pmethodid" value="<?php echo (int)$row['pmethod_id']; ?>" />
				<input type="hidden" name="action" id="action" value="editpmethod" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='pmethods.php'" />
            </td>
          </tr>
        </table>
      </form>
	  </div>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no payment method found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>