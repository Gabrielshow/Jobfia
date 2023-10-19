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


	if (isset($_POST["action"]) && $_POST["action"] == "message_reply")
	{
		unset($errors);
		$errors = array();

		$message_id	= (int)getPostParameter('id');
		$user_id	= (int)getPostParameter('uid');
		$answer		= mysqli_real_escape_string($conn, nl2br(getPostParameter('answer')));

		if (!($message_id && $user_id && $answer))
		{
			$errors[] = "Please enter your reply";
		}

		if (count($errors) == 0)
		{
			$ins_query = "INSERT INTO jobfia_messages_answers SET message_id='$message_id', user_id='$user_id', is_admin='1', answer='$answer', answer_date=NOW()";
			if (smart_mysql_query($ins_query))
			{
				smart_mysql_query("UPDATE jobfia_messages SET viewed='1', status='replied' WHERE message_id='$message_id'");
				header("Location: messages.php?msg=sent");
				exit();
			}
		}
		else
		{
			$errormsg = "";
			foreach ($errors as $errorname)
				$errormsg .= "&#155; ".$errorname."<br/>";
		}
	}

	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$mid	= (int)$_REQUEST['id'];
		$pn		= (int)$_GET['pn'];

		$query = "SELECT m.*, DATE_FORMAT(m.created, '%d %b %Y %h:%i %p') AS message_date, u.fname, u.lname FROM jobfia_messages m, jobfia_users u WHERE m.user_id=u.user_id AND m.message_id='$mid'";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Message Reply";
	require_once ("inc/header.inc.php");

?>   
    
	<?php

		if ($total > 0)
			{
				$row = mysqli_fetch_array($result);
	?>

	   <h2>Message Reply</h2>

		<?php if (isset($errormsg)) { ?>
			<div class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>

		<form action="" method="post" name="form1">
          <table width="100%" style="background: #fcfcfc" align="center" cellpadding="3" cellspacing="3" border="0">
            <tr>
              <td nowrap="nowrap" width="100" valign="middle" align="left" class="tb2">To:</td>
              <td valign="top"><i class="fa fa-user-circle-o" style="color: #bbb"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
            </tr>
            <tr>
              <td nowrap="nowrap" valign="middle" align="left" class="tb2">Subject:</td>
              <td nowrap="nowrap" valign="top"><b><?php echo $row['subject']; ?></b></td>
            </tr>
           <tr>
            <td nowrap="nowrap" valign="middle" align="left" class="tb2">&nbsp;</td>
            <td align="left" valign="top"><textarea rows="8" cols="60" class="form-control" name="answer"><?php echo getPostParameter('answer'); ?></textarea></td>
          </tr>
          <tr>
            <td>&nbsp</td>
			<td align="left" valign="top">
				<input type="hidden" name="id" id="id" value="<?php echo (int)$row['message_id']; ?>" />
				<input type="hidden" name="uid" id="uid" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="action" id="action" value="message_reply">
				<input type="submit" name="reply" id="reply" class="btn btn-success" value="Send" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='messages.php'" />
            </td>
          </tr>
          </table>
		</form>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no message found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>