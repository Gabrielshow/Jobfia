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


	if (isset($_POST['action']) && $_POST['action'] == "editetemplate")
	{
		$etemplate_id	= (int)getPostParameter('eid');
		$email_subject	= mysqli_real_escape_string($conn, $_POST['esubject']);
		$email_message	= mysqli_real_escape_string($conn, $_POST['emessage']);

		unset($errs);
		$errs = array();

		if (!($email_subject && $email_message))
		{
			$errs[] = "Please fill in all required fields";
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE jobfia_email_templates SET email_subject='$email_subject', email_message='$email_message', modified=NOW() WHERE template_id='$etemplate_id' LIMIT 1";

			if (smart_mysql_query($sql))
			{
				header("Location: etemplates.php?msg=updated");
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

	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $eid = (int)$_GET['id']; } else { $eid = (int)$_POST['eid']; }
	$query = "SELECT * FROM jobfia_email_templates WHERE template_id='$eid' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	$title = "Edit Email Template";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><i class="fa fa-at"></i> Edit Email Template</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background: #fcfcfc" width="100%" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="80" valign="middle" align="right" class="tb1">Subject:</td>
            <td valign="top"><input type="text" name="esubject" id="esubject" value="<?php echo $row['email_subject']; ?>" size="50" class="form-control" /></td>
          </tr>
           <tr>
            <td>&nbsp;</td>
            <td height="50" style="border: 1px solid #D7D7D7;" bgcolor="#F7F7F7" align="center" valign="middle">
				<p>Please use following variables for your email template:</p>
				<table width="95%" align="center" cellpadding="2" cellspacing="2" border="0">
					<?php if ($row['email_name'] == "signup") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Member First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{username}</b></td><td nowrap="nowrap" align="left"> - Member Username</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{password}</b></td><td nowrap="nowrap" align="left"> - Member Password</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{login_url}</b></td><td nowrap="nowrap" align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "activate") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Member First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{username}</b></td><td nowrap="nowrap" align="left"> - Member Username</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{password}</b></td><td nowrap="nowrap" align="left"> - Member Password</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{activate_link}</b></td><td nowrap="nowrap" align="left"> - Activation Link</td></tr>
					<?php }elseif($row['email_name'] == "forgot_password") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Member First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{username}</b></td><td nowrap="nowrap" align="left"> - Member Username</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{password}</b></td><td nowrap="nowrap" align="left"> - Member Password</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{login_url}</b></td><td nowrap="nowrap" align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "invite_friend") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{friend_name}</b></td><td nowrap="nowrap" align="left"> - Friend First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Member First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{referral_link}</b></td><td nowrap="nowrap" align="left"> - Referral Link</td></tr>
					<?php }elseif($row['email_name'] == "job_complete_alert") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Employer First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_name}</b></td><td nowrap="nowrap" align="left"> - Job Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{login_url}</b></td><td nowrap="nowrap" align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "job_notcomplete_alert") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Worker First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_name}</b></td><td nowrap="nowrap" align="left"> - Job Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{login_url}</b></td><td nowrap="nowrap" align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "job_invitation") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Worker First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_name}</b></td><td nowrap="nowrap" align="left"> - Job Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_price}</b></td><td nowrap="nowrap" align="left"> - Job Price</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{login_url}</b></td><td nowrap="nowrap" align="left"> - Login Link</td></tr>
					<?php }elseif($row['email_name'] == "new_job_alert") { ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Worker First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_name}</b></td><td nowrap="nowrap" align="left"> - Job Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_price}</b></td><td nowrap="nowrap" align="left"> - Job Price</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_url}</b></td><td nowrap="nowrap" align="left"> - Job Link</td></tr>
					<?php }else{ ?>
						<tr><td nowrap="nowrap" align="right"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Worker First Name</td></tr>
						<tr><td nowrap="nowrap" align="right"><b>{job_name}</b></td><td nowrap="nowrap" align="left"> - Job Name</td></tr>					
					<?php } ?>
				</table>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top"><textarea name="emessage" id="editor1" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['email_message']); ?></textarea></td>
          </tr>
			<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
			<script>
				CKEDITOR.replace( 'editor1' );
			</script>
          <tr>
            <td colspan="2" align="center" valign="bottom">
				<input type="hidden" name="eid" id="eid" value="<?php echo (int)$row['template_id']; ?>" />
				<input type="hidden" name="action" id="action" value="editetemplate" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='etemplates.php'" />
		  </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no email template found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>