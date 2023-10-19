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

	$query = "SELECT * FROM jobfia_categories ORDER BY name";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Categories";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a class="addnew" href="category_add.php">Add Category</a></div>

		<h2><i class="fa fa-list-alt"></i> Categories</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:70%; margin: 0 auto;" class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Category was successfully added"; break;
						case "exists":	echo "Sorry, category with this name is exists"; break;
						case "updated": echo "Category has been successfully edited"; break;
						case "deleted": echo "Category has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<table align="center" style="border-bottom: 1px solid #F7F7F7;" width="70%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th class="noborder" width="5%">&nbsp;</th>
				<th width="50%">Category Name</th>
				<th width="15%">Min Price</th>
				<th width="15%"><i class="fa fa-briefcase"></i> Jobs</th>
				<th width="15%"><i class="fa fa-list"></i> Offers</th>
				<th width="20%">Actions</th>
			</tr>
             <?php $allcategories = array(); $allcategories = CategoriesList(0); foreach ($allcategories as $category_id => $category_name) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center"><img src="images/icons/cat.png" /></td>
					<td align="left" valign="middle"><a href="category_edit.php?id=<?php echo $category_id; ?>"><span style="font-size: 15px;"><?php echo $category_name; ?></span></a></td>
					<td bgcolor="#fcfcfc" style="border-bottom: 1px solid #FFF" nowrap="nowrap" align="center" valign="middle"><?php echo CategoryPrice($category_id); ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo CategoryTotalJobs($category_id, $all = 1); ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo CategoryTotalJobs($category_id, $all = 2); ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="category_edit.php?id=<?php echo $category_id; ?>" title="Edit"><img border="0" alt="Edit" src="images/edit.png" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this category?') )location.href='category_delete.php?id=<?php echo $category_id; ?>'" title="Delete"><img border="0" alt="Delete" src="images/delete.png" /></a>
					</td>
				  </tr>
			<?php } ?>
            </table>
          
		  <?php }else{ ?>
					<div class="alert alert-info">There are no categories at this time.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>