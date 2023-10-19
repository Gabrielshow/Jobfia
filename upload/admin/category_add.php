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
		$category_name			= mysqli_real_escape_string($conn, getPostParameter('catname'));
		$parent_category		= (int)getPostParameter('parent_id');
		$min_price				= mysqli_real_escape_string($conn, getPostParameter('min_price'));
		$category_description	= mysqli_real_escape_string($conn, nl2br(getPostParameter('description')));

		if (!($category_name))
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
			$check_query = smart_mysql_query("SELECT * FROM jobfia_categories WHERE parent_id='$parent_category' AND name='$category_name'");
			
			if (mysqli_num_rows($check_query) == 0)
			{
				$sql = "INSERT INTO jobfia_categories SET parent_id='$parent_category', name='$category_name', min_price='$min_price', description='$category_description'";

				if (smart_mysql_query($sql))
				{
					header("Location: categories.php?msg=added");
					exit();
				}
			}
			else
			{
				header("Location: categories.php?msg=exists");
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

	$title = "Add Category";
	require_once ("inc/header.inc.php");

?>

		  <h2><i class="fa fa-list-alt"></i> Add Category</h2>

			<?php if (isset($errormsg) && $errormsg != "") { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>

		  <form action="" method="post">
		  <table width="100%" style="background: #fcfcfc" align="center" border="0" cellpadding="3" cellspacing="0">
          <tr>
            <td width="15%" valign="middle" align="left" class="tb1"><span class="req">* </span>Name:</td>
			<td align="left">
				<input type="text" name="catname" id="catname" value="<?php echo getPostParameter('catname'); ?>" size="35" class="form-control" />
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Parent Category:</td>
			<td align="left">
				<select name="parent_id" class="form-control" style="width: 200px;">
					<option value=""> ---------- None ---------- </option>
					<?php CategoriesDropDown (0); ?>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Description:</td>
			<td align="left" valign="top"><textarea name="description" cols="50" rows="5" class="form-control"><?php echo getPostParameter('description'); ?></textarea></select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Min Price: <span class="jobfia_tooltip" title="min job's reward for category"><i class="fa fa-info-circle"></i></span></td>
			<td align="left">
				<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
						<input type="text" name="min_price" value="<?php echo getPostParameter('min_price'); ?>" size="7" style="width: 150px" class="form-control" />
		    	</div>					
			</td>
          </tr>
          <tr>
			<td>&nbsp;</td>
			<td valign="middle" align="left">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Category" />&nbsp;
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='categories.php'" />
		    </td>
          </tr>
		  </table>
		  </form>


<?php require_once ("inc/footer.inc.php"); ?>