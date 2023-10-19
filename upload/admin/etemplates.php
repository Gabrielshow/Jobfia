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

	$query = "SELECT * FROM jobfia_email_templates ORDER BY template_id ASC";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Email Templates";
	require_once ("inc/header.inc.php");

?>

		<h2><i class="fa fa-at"></i> Email Templates</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:45%;" class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "added":	echo "Email template was successfully added!"; break;
						case "updated": echo "Email template has been successfully edited!"; break;
						case "deleted": echo "Email template has been successfully deleted!"; break;
					}

				?>
			</div>
			<?php } ?>


			<table align="center" style="border-bottom: 1px solid #F7F7F7;" width="45%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th class="noborder" width="10%">&nbsp;</th>
				<th width="65%">Template Name</th>
				<th width="20%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center"><i class="fa fa-at fa-2x" style="color: #5ac0de"></i></td>
					<td align="left" valign="middle" ><a href="etemplate_details.php?id=<?php echo $row['template_id']; ?>"><?php echo $row['email_title']; ?></a></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="etemplate_details.php?id=<?php echo $row['template_id']; ?>" title="View"><i class="fa fa-search fa-lg" style="color: #555"></i></a>
						<a href="etemplate_edit.php?id=<?php echo $row['template_id']; ?>" title="Edit"><i class="fa fa-edit fa-lg" style="color: #555"></i></a>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
					<div class="alert alert-info">Sorry, no email template found.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>