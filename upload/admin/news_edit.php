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


	if (isset($_POST['action']) && $_POST['action'] == "edit_news")
	{
		unset($errs);
		$errs = array();

		$news_id			= (int)getPostParameter('news_id');
		$news_title			= mysqli_real_escape_string($conn, getPostParameter('news_title'));
		$news_description	= mysqli_real_escape_string($conn, $_POST['news_description']);
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($news_title && $news_description && $status))
		{
			$errs[] = "Please fill in all fields";
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE jobfia_news SET news_title='$news_title', news_description='$news_description', status='$status' WHERE news_id='$news_id' LIMIT 1";

			if (smart_mysql_query($sql))
			{
				header("Location: news.php?msg=updated");
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
		$news_id = (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_news WHERE news_id='$news_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Edit News";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><i class="fa fa-newspaper-o"></i> Edit News</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div style="width:60%;" class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background: #fcfcfc" width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td valign="middle" align="right" class="tb1">Title:</td>
            <td valign="top"><input type="text" name="news_title" id="news_title" value="<?php echo $row['news_title']; ?>" size="78" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top"><textarea name="news_description" cols="80" rows="12" id="editor" class="form-control"><?php echo stripslashes($row['news_description']); ?></textarea></td>
          </tr>
		  <script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
		  <script>
				CKEDITOR.replace( 'editor' );
		  </script>
          <tr>
            <td valign="middle" align="right" class="tb1">Status:</td>
            <td valign="top">
				<select name="status">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
	          <td align="left" valign="middle">&nbsp;</td>
            <td align="left" valign="bottom">
				<input type="hidden" name="news_id" id="news_id" value="<?php echo (int)$row['news_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit_news" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='news.php'" />
		  </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no news found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>