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


	if (isset($_POST['add']) && $_POST['add'] != "")
	{
		unset($errs);
		$errs = array();

		$pmethod_type	 = mysqli_real_escape_string($conn, getPostParameter('pmethod_type'));
		$pmethod_title	 = mysqli_real_escape_string($conn, getPostParameter('pmethod_title'));
		$fee			= mysqli_real_escape_string($conn, getPostParameter('fee'));
		$pmethod_details = mysqli_real_escape_string($conn, nl2br(getPostParameter('pmethod_details')));

		if(!($pmethod_title && $pmethod_type && $pmethod_details))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			$check_query = smart_mysql_query("SELECT * FROM jobfia_pmethods WHERE pmethod_title='$pmethod_title' LIMIT 1");
			if (mysqli_num_rows($check_query) != 0)
			{
				$errs[] = "Sorry, payment method exists";
			}
		}

		if (count($errs) == 0)
		{
			$sql = "INSERT INTO jobfia_pmethods SET pmethod_type='$pmethod_type', pmethod_title='$pmethod_title', fee='$fee', pmethod_details='$pmethod_details', status='active'";

			if (smart_mysql_query($sql))
			{
				header("Location: pmethods.php?msg=added");
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

	$title = "Add Payment Method";
	require_once ("inc/header.inc.php");

?>
 
        <h2><i class="fa fa-money"></i> Add Payment Method</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<div style="background: #F9F9F9; padding: 10px 0;">
        <form action="" method="post">
          <table align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="right" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="pmethod_title" id="pmethod_title" value="<?php echo @$_POST['pmethod_title']; ?>" size="35" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1"><span class="req">* </span>Type:</td>
            <td valign="top">
				<select name="pmethod_type" id="pmethod_type" class="form-control" onchange="this.form.submit()">
					<option value="">--- select type ---</option>
					<option value="deposit" <?php if (@$_POST['pmethod_type'] == "deposit") echo "selected"; ?>>deposit</option>
					<option value="withdraw" <?php if (@$_POST['pmethod_type'] == "withdraw") echo "selected"; ?>>withdraw</option>
				</select>			
			</td>
          </tr>
		  <?php if (@$_POST['pmethod_type'] == "deposit") { ?>
          <tr>
            <td valign="middle" align="right" class="tb1">
				<span class="req">* </span>Payment Details:
				<br/><span class="help">your account ID <br/>and payment instructions for user</span>
			</td>
            <td valign="top"><textarea name="pmethod_details" cols="50" rows="7" class="form-control"><?php echo getPostParameter('pmethod_details'); ?></textarea>
			<br/><span class="note">Optional tag: {USER_ID} will be replaced with user ID</span></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Deposit Fee:</td>
            <td valign="top"><input type="text" name="fee" placeholder="0" id="fee" value="" size="3" class="form-control" /> %</td>
          </tr>
		  <?php }else if (@$_POST['pmethod_type'] == "withdraw") { ?>
          <tr>
            <td valign="middle" align="right" class="tb1">
				<span class="req">* </span>Payment Details:
				<br/><span class="help">user will need to provide<br/> this information</span>
			</td>
            <td valign="top"><textarea name="pmethod_details" cols="50" rows="7" class="form-control"><?php echo getPostParameter('pmethod_details'); ?></textarea></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Withdraw Fee:</td>
            <td valign="top"><input type="text" name="fee" placeholder="0" id="fee" value="<?php echo getPostParameter('fee'); ?>" size="3" class="form-control" /> %</td>
          </tr>
		  <?php } ?>
          <tr>
            <td align="center" valign="top">&nbsp;</td>
	        <td align="left" valign="top">
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Payment Method" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='pmethods.php'" />
            </td>
          </tr>
        </table>
      </form>
	  </div>


<?php require_once ("inc/footer.inc.php"); ?>