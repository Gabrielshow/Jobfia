<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");
	require_once("inc/pagination.inc.php");

	$results_per_page = 10;
	$cc = 0;

	function GetRepliesTotal($message_id)
	{
		global $userid;
		$row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(answer_id) as total_replies FROM jobfia_messages_answers WHERE message_id='".(int)$message_id."' AND user_id='".(int)$userid."'"));
		$total_replies = $row['total_replies'];

		if ($total_replies > 0) 
			return "<span class='reply_num'>".$total_replies."</span>";
		else
			return "<span class='no_reply'>".$total_replies."</span>";
	}


	if (isset($_POST['action']) && $_POST['action'] == "mysupport")
	{
		unset($errs);
		$errs = array();

		$subject = mysqli_real_escape_string($conn, getPostParameter('subject'));
		$message = mysqli_real_escape_string($conn, nl2br(getPostParameter('message')));

		if(!($subject && $message))
		{
			$errs[] = "Please fill in all fields";
		}

		if (count($errs) == 0)
		{
			$ins_query = "INSERT INTO jobfia_messages SET user_id='$userid', subject='$subject', message='$message', status='new', created=NOW()";
			if (smart_mysql_query($ins_query))
			{
				// send notification
				if (NEW_TICKET_ALERT == 1)
				{
					$subject = "New ticket submitted";
					$message = "New ticket submitted";

					SendEmail(SITE_MAIL, $subject, $message, $noreply_mail = 1);
				}

				header("Location: mysupport.php?msg=1");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>\n";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Members Support";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<div class="pull-right"><a href="javascript:void(0);" class="btn btn-info" onclick="$('#create_ticket').toggle('fast'); $('#support_msg').toggle('fast');">+ Create Ticket</a></div>

	<h1><i class="fa fa-headphones" style="color: #01a650"></i> Support</h1>
	
	<?php if (isset($_GET['msg']) and $_GET['msg'] == 1) { ?>
			<div class="alert alert-success alert-dismissible fade in">
				 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				 <i class="fa fa-check-circle-o fa-lg"></i> 
				Your message has been sent. We will get back to you as soon as possible.
			</div>
	<?php }else{ ?>

		<div id="create_ticket" style="width: 100%; <?php if (!(isset($_POST['action']) && $_POST['action'] == "mysupport")){ ?>display: none;<?php } ?>">
		
		<p align="center"><img src="<?php echo SITE_URL; ?>images/icon_support.png" border="0" /></p>
		<p align="center">Please fill form below and we'll be happy to help you as soon as possible.</p>
		<?php if (isset($allerrors) and $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
 		
 		<div class="row">
 		<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
 		<form action="" method="post">
          <div class="form-group">
            <label>Subject:</label>
            <input type="text" class="form-control" name="subject" value="<?php echo getPostParameter('subject'); ?>" size="63" required="required" />
          </div>
          <div class="form-group">
            <label>Message:</label>
            <textarea rows="8" cols="60" class="form-control" name="message" required="required"><?php echo getPostParameter('message'); ?></textarea>
          </div>
          <div class="form-group">
				<input type="hidden" name="action" id="action" value="mysupport" />
				<input type="submit" class="btn btn-success btn-lg" name="Send" id="Sent" value="Send Message" />
				<a class="btn btn-default btn-lg" href="javascript:void(0);" onclick="$('#create_ticket').hide('fast');$('#support_msg').toggle('fast');">Cancel</a>
          </div>
		</form>
		</div>		
		</div></div>

	<?php } ?>


	<?php

		$mquery = "SELECT m.*, DATE_FORMAT(m.created, '%d %b %Y %h:%i %p') AS message_date, u.fname, u.lname FROM jobfia_messages m, jobfia_users u WHERE u.user_id='$userid' AND m.is_admin='0' AND m.user_id=u.user_id ORDER BY created DESC";
		$mresult = smart_mysql_query($mquery);
		$mtotal = mysqli_num_rows($mresult);

		if ($mtotal > 0) {
	?>
			<div class="table-responsive" id="support_msg">
			<table align="center" width="100%" class="table table-striped table-hover brd" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="53%">Subject</th>
				<th width="22%"><i class="fa fa-clock-o"></i> Date</th>
				<th width="7%">Replies</th>
				<th width="10%">Actions</th>
			</tr>
			<?php while ($mrow = mysqli_fetch_array($mresult)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				<td align="left" valign="middle">
					<i class="fa fa-envelope fa-lg" style="color: #ccc"></i> &nbsp; <a href="<?php echo SITE_URL; ?>mymessage.php?mid=<?php echo $mrow['message_id']; ?>">
					<?php
						if (strlen($mrow["subject"]) > 100) $msubject = substr($mrow["subject"], 0, 100)."..."; else $msubject = $mrow["subject"];
						if ($mrow['viewed'] == 0) echo "<b>".$msubject."</b>"; else echo $msubject;
					?>
					</a>
				</td>
				<td align="left" valign="middle"><?php echo $mrow["message_date"]; ?></td>
				<td align="left" valign="middle"><a href="<?php echo SITE_URL; ?>mymessage.php?mid=<?php echo $mrow['message_id']; ?>"><span class="badge" style="font-size: 13px"><?php echo GetRepliesTotal($mrow['message_id']); ?></span>  <i class="fa fa-comments-o fa-lg"></i></a></td>
				<td nowrap="nowrap" align="left" valign="middle">
					<a href="<?php echo SITE_URL; ?>mymessage.php?mid=<?php echo $mrow['message_id']; ?>" title="View"><i class="fa fa-search fa-lg" style="color: #777"></i></a>
				</td>
			</tr>
			<?php } ?>
			</table>
			</div>

				<?php echo ShowPagination("messages",$results_per_page,"mysupport.php?","WHERE user_id='$userid' AND is_admin='0'"); ?>			

        <?php }else{ ?>
					<p id="support_msg" class="well text-center">If you have any question, please feel free to create support ticket.</p>
        <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>