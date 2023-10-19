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
		$news_id = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS modify_date FROM jobfia_news WHERE news_id='$news_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$row = mysqli_fetch_array($result);
		$total = mysqli_num_rows($result);
	}

	$title = "News Details";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { ?>

          <h2><i class="fa fa-newspaper-o"></i> News Details</h2>

          <table width="100%" style="background: #fcfcfc" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td align="left" valign="top">
				<b><?php echo stripslashes($row['news_title']); ?></b>
            </td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td valign="top"><?php echo stripslashes($row['news_description']); ?></td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td align="right" valign="top">Last modified: <span class="date"><?php echo $row['modify_date']; ?></span></td>
          </tr>
          <tr>
            <td align="center" valign="bottom">
			<input type="button" class="btn btn-success" name="edit" value="Edit News" onClick="javascript:document.location.href='news_edit.php?id=<?php echo $row['news_id']; ?>&page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
			<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='news.php'" />
		  </td>
          </tr>

          </table>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no news found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>