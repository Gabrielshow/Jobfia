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
		$eid	= (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_email_templates WHERE template_id='$eid'";
		$result = smart_mysql_query($query);
		$row = mysqli_fetch_array($result);
		$total = mysqli_num_rows($result);
	}

	$title = "View Email Template";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { ?>

          <h2><i class="fa fa-at"></i> View Email Template</h2>

          <table width="100%" style="background: #fcfcfc" cellpadding="2" cellspacing="3" border="0">
            <tr>
              <td align="left" valign="top" style="padding: 15px"><b><?php echo stripslashes($row['email_subject']); ?></b></td>
            </tr>
            <tr>
              <td><div class="sline"></div></td>
            </tr>
           <tr>
            <td valign="top" style="padding: 15px"><?php echo stripslashes($row['email_message']); ?></td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
           </tr>
          <tr>
            <td align="center" valign="bottom">
	            <a class="btn btn-success" href="etemplate_edit.php?id=<?php echo $row['template_id']; ?>">Edit Template</a>
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='etemplates.php'" />
            </td>
          </tr>
          </table>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no email template found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>