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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_categories WHERE category_id='$id'";
		$rs = smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}


	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
		unset($errors);
		$errors = array();
 
		$catid					= (int)getPostParameter('catid');
		$parent_category		= (int)getPostParameter('parent_id');
		$category_name			= mysqli_real_escape_string($conn, getPostParameter('catname'));
		$min_price				= mysqli_real_escape_string($conn, getPostParameter('min_price'));
		$category_description	= mysqli_real_escape_string($conn, nl2br(getPostParameter('description')));		

		if (!($category_name && $catid))
		{
			$errors[] = "Please ensure that all fields marked with an asterisk are complete";
		}
		else
		{
			if (isset($min_price) && $min_price != "" && !(is_numeric($min_price) && $min_price > 0))
			{
				$errors[] = "Please enter correct category min price";
				$min_price = "0.00";
			}
		}

		if (count($errors) == 0)
		{
			smart_mysql_query("UPDATE jobfia_categories SET parent_id='$parent_category', name='$category_name', min_price='$min_price', description='$category_description' WHERE category_id='$catid'");

			header("Location: categories.php?msg=updated");
			exit();
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "&#155; ".$errorname."<br/>";
		}
	}


	$title = "Edit Category";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-list-alt"></i> Edit Category</h2>

	<?php if ($total > 0) {
	
		$row = mysqli_fetch_array($rs);

	?>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div style="width:97%;" class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } ?>

      <form action="" method="post">
        <table width="100%"	style="background: #fcfcfc" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="15%" valign="middle" align="left" class="tb1"><span class="req">* </span>Name:</td>
            <td valign="top"><input type="text" name="catname" id="catname" value="<?php echo $row['name']; ?>" size="35" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Parent Category:</td>
			<td align="left">
				<select name="parent_id" class="form-control" style="width: 200px;">
					<option value=""> ---------- None ---------- </option>
					<?php CategoriesDropDown (0,"",$row['category_id'],$row['parent_id']); ?>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Description:</td>
			<td align="left" valign="top"><textarea name="description" cols="50" rows="5" class="form-control"><?php echo strip_tags($row['description']); ?></textarea></select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Min Price: <span class="jobfia_tooltip" title="min job's reward for category"><i class="fa fa-info-circle"></i></span></td>
			<td align="left">
				<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
						<input type="text" name="min_price" value="<?php echo DisplayMoney($row['min_price'], $hide_currency_sign = 1); ?>" size="7"  style="width: 150px" class="form-control" />
		    	</div>								
			</td>
          </tr>
			<td>&nbsp;</td>
			<td valign="middle" align="left">
			  <input type="hidden" name="catid" id="catid" value="<?php echo (int)$row['category_id']; ?>" />
			  <input type="hidden" name="action" id="action" value="edit">
			  <input type="submit" class="btn btn-success" name="update" id="update" value="Update" />&nbsp;
			  <input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='categories.php'" />
			</td>
            </tr>
          </table>
      </form>
      
	  <?php }else{ ?>
				<div class="alert alert-info">Sorry, no category found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>