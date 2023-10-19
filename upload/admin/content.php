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


	$query = "SELECT * FROM jobfia_content ORDER BY content_id ASC";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Content";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a class="addnew" href="content_add.php">Create Page</a></div>

		<h2><i class="fa fa-file-o"></i> Content</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:45%; margin: 0 auto;" class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "added":	echo "Content was successfully added!"; break;
						case "updated": echo "Content has been successfully edited!"; break;
						case "deleted": echo "Content has been successfully deleted!"; break;
					}

				?>
			</div>
			<?php } ?>


			<table align="center" style="border-bottom: 1px solid #F7F7F7;" width="45%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th class="noborder" width="10%">&nbsp;</th>
				<th width="70%">Page Title</th>
				<th width="20%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center"><img src="images/icons/content.png" /></td>
					<td align="left" valign="middle" ><a href="content_details.php?id=<?php echo $row['content_id']; ?>"><?php echo $row['title']; ?></a></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="content_details.php?id=<?php echo $row['content_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="content_edit.php?id=<?php echo $row['content_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<?php if ($row['name'] == "page") { ?>
							<a href="#" onclick="if (confirm('Are You sure you really want to delete this page?') )location.href='content_delete.php?id=<?php echo $row['content_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
						<?php } ?>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
					<div class="alert alert-info">Sorry, no content found.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>