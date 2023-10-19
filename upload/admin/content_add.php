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


	if (isset($_POST['action']) && $_POST['action'] == "addcontent")
	{
		$page_title			= mysqli_real_escape_string($conn, $_POST['page_title']);
		$page_text			= mysqli_real_escape_string($conn, $_POST['page_text']);
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
			$sql = "INSERT INTO jobfia_content SET name='page', title='$page_title', description='$page_text', meta_description='$meta_description', meta_keywords='$meta_keywords', modified=NOW()";
			if (smart_mysql_query($sql))
			{
				header("Location: content.php?msg=added");
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


	$title = "Create New Page";
	require_once ("inc/header.inc.php");

?>
 
        <h2><i class="fa fa-file-o"></i> Create New Page</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background: #fcfcfc" width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="15%" valign="middle" align="left" class="tb1"><span class="req">* </span>Page Title:</td>
            <td valign="top"><input type="text" name="page_title" id="page_title" value="<?php echo getPostParameter('page_title'); ?>" size="80" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><textarea name="page_text" id="editor1" cols="75" rows="8" class="form-control"><?php echo getPostParameter('page_text'); ?></textarea></td>
          </tr>
			<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
			<script>
				CKEDITOR.replace( 'editor1' );
			</script>
			<tr>
				<td valign="middle" align="left" class="tb1">Meta Description:</td>
				<td valign="top"><textarea name="meta_description" cols="112" rows="2" style="width:98%" class="form-control"><?php echo getPostParameter('meta_description'); ?></textarea></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Meta Keywords:</td>
				<td valign="top"><input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo getPostParameter('meta_keywords'); ?>" size="115" style="width:98%" class="form-control" /></td>
            </tr>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="action" id="action" value="addcontent" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Create Page" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='content.php'" />
			</td>
          </tr>
        </table>
      </form>

<?php require_once ("inc/footer.inc.php"); ?>