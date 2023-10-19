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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_countries WHERE country_id='$id'";
		$rs = smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}

	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
		unset($errors);
		$errors = array();
 
		$country_id		= (int)getPostParameter('country_id');
		$code			= mysqli_real_escape_string($conn, getPostParameter('code'));
		$country_name	= mysqli_real_escape_string($conn, getPostParameter('country_name'));
		$sort_order		= (int)getPostParameter('sort_order');

		if (!($code && $country_name))
		{
			$errors[] = "Please fill in all required fields";
		}

		if (count($errors) == 0)
		{
			$sql = "UPDATE jobfia_countries SET code='$code', name='$country_name', sort_order='$sort_order' WHERE country_id='$country_id' LIMIT 1";
			if (smart_mysql_query($sql))
			{
				header("Location: countries.php?msg=updated");
				exit();
			}
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "&#155; ".$errorname."<br/>";
		}
	}

	$title = "Edit Country";
	require_once ("inc/header.inc.php");

?>

    <h2>Edit Country</h2>

	<?php if ($total > 0) {
	
		$row = mysqli_fetch_array($rs);

	?>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div style="width:95%;" class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } ?>

      <form action="" method="post">
        <table style="background: #fcfcfc" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="45%" valign="middle" align="right" class="tb1"><span class="req">* </span>Country Code:</td>
            <td valign="top"><input type="text" name="code" id="code" value="<?php echo $row['code']; ?>" size="32" class="form-control" /></td>
			</tr>
          <tr>
            <td valign="middle" align="right" class="tb1"><span class="req">* </span>Name:</td>
            <td valign="top"><input type="text" name="country_name" id="country_name" value="<?php echo $row['name']; ?>" size="32" class="form-control" /></td>
			</tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Sort Order:</td>
            <td valign="top"><input type="text" name="sort_order" id="sort_order" value="<?php echo $row['sort_order']; ?>" size="5" class="form-control" /></td>
			</tr>
            <tr>
              <td align="center" colspan="2" valign="bottom">
				<input type="hidden" name="country_id" id="country_id" value="<?php echo (int)$row['country_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit">
				<input type="submit" class="btn btn-success" name="update" id="update" value="Update" />&nbsp;
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='countries.php'" />
              </td>
            </tr>
          </table>
      </form>
      
	  <?php }else{ ?>
				<div class="alert alert-info">Sorry, no country found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>