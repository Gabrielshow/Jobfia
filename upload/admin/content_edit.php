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


	if (isset($_POST['action']) && $_POST['action'] == "editcontent")
	{
		$content_id			= (int)getPostParameter('cid');
		$page_title			= mysqli_real_escape_string($conn, $_POST['page_title']);
		$description		= mysqli_real_escape_string($conn, $_POST['description']);
		$meta_description	= mysqli_real_escape_string($conn, getPostParameter('meta_description'));
		$meta_keywords		= mysqli_real_escape_string($conn, getPostParameter('meta_keywords'));

		unset($errs);
		$errs = array();

		if (!($page_title))
		{
			$errs[] = "Please fill in all required fields";
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE jobfia_content SET title='$page_title', description='$description', meta_description='$meta_description', meta_keywords='$meta_keywords', meta_description='$meta_description', meta_keywords='$meta_keywords', modified=NOW() WHERE content_id='$content_id' LIMIT 1";
			if (smart_mysql_query($sql))
			{
				header("Location: content.php?msg=updated");
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

	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $cid = (int)$_GET['id']; } else { $cid = (int)$_POST['cid']; }
	$query = "SELECT * FROM jobfia_content WHERE content_id='$cid' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	$title = "Edit Content";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><i class="fa fa-file-o"></i> Edit Content</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background: #fcfcfc" width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="100" valign="middle" align="right" class="tb1"><span class="req">* </span>Page Title:</td>
            <td valign="top"><input type="text" name="page_title" id="page_title" value="<?php echo $row['title']; ?>" size="80" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['description']); ?></textarea></td>
          </tr>
			<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
			<script>
				CKEDITOR.replace( 'editor1' );
			</script>
          <tr>
			<td valign="middle" align="right" class="tb1">Meta Description:</td>
			<td valign="top"><textarea name="meta_description" cols="112" rows="2" style="width:98%" class="form-control"><?php echo strip_tags($row['meta_description']); ?></textarea></td>
          </tr>
          <tr>
			<td valign="middle" align="right" class="tb1">Meta Keywords:</td>
			<td valign="top"><input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo $row['meta_keywords']; ?>" size="115" style="width:98%" class="form-control" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="cid" id="cid" value="<?php echo (int)$row['content_id']; ?>" />
				<input type="hidden" name="action" id="action" value="editcontent" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='content.php'" />
            </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no page found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>