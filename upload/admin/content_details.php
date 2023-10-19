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
		$cid = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(modified, '%d %b %Y %h:%i %p') AS modify_date FROM jobfia_content WHERE content_id='$cid' LIMIT 1";
		$result = smart_mysql_query($query);
		$row = mysqli_fetch_array($result);
		$total = mysqli_num_rows($result);
	}

	$title = "View Content";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { ?>

          <h2><i class="fa fa-file-o"></i> View Page</h2>

          <table width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td bgcolor="#F7F7F7" align="left" valign="top">
				<h1 style="margin:0;padding: 3px;"><?php echo stripslashes($row['title']); ?></h1>
            </td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td valign="top"><?php echo stripslashes($row['description']); ?></td>
          </tr>
			<?php if ($row['meta_description'] != "") { ?>
			<tr>
				<td valign="top" align="right" class="tb1">
					Meta Description:<br/>
					<?php echo $row['meta_description']; ?>
				</td>
			</tr>
			<?php } ?>
			<?php if ($row['meta_keywords'] != "") { ?>
			<tr>
				<td valign="top" align="right" class="tb1">
					Meta Keywords:<br/>
					<?php echo $row['meta_keywords']; ?>
				</td>
			</tr>
			<?php } ?>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td align="right" valign="top">Last modified: <span class="date"><?php echo $row['modify_date']; ?></span></td>
          </tr>
		  <?php if ($row['name'] == "page") { ?>
           <tr>
            <td colspan="2" height="30" bgcolor="#F7F7F7" align="center" valign="middle">Page URL: <a target="_blank" href="<?php echo SITE_URL."content.php?id=".$row['content_id']; ?>"><?php echo SITE_URL."content.php?id=".$row['content_id']; ?></a></td>
          </tr>
		  <?php } ?>
          <tr>
            <td align="center" valign="bottom">
				<input type="button" class="btn btn-success" name="edit" value="Edit Content" onClick="javascript:document.location.href='content_edit.php?id=<?php echo $row['content_id']; ?>&page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='content.php'" />
            </td>
          </tr>
          </table>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no page found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>