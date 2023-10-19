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

	$cc = 0;


	if (isset($_POST['action']) && $_POST['action'] == "reply")
	{
		unset($errs);
		$errs = array();

		$message_id		= mysqli_real_escape_string($conn, getPostParameter('mid'));
		$answer			= mysqli_real_escape_string($conn, nl2br(getPostParameter('answer')));

		if(!($message_id && $answer))
		{
			$errs[] = "Please enter your message";
		}

		if (count($errs) == 0)
		{
			smart_mysql_query("INSERT INTO jobfia_messages_answers SET message_id='$message_id', user_id='$userid', answer='$answer', answer_date=NOW()");

			header("Location: mymessage.php?mid=$message_id&msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>\n";
		}
	}

	if (isset($_REQUEST['mid']) && is_numeric($_REQUEST['mid']))
	{
		$message_id = (int)$_REQUEST['mid'];
	}
	else
	{
		header ("Location: mysupport.php");
		exit();
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Message Details";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
	
	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<div class="pull-right">
		<!--<a class="btn btn-info" href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-envelope"></i> My Messages</a> &nbsp;&nbsp;-->
		<a class="btn btn-info" href="javascript:void(0);" onclick="$('#reply_box').toggle('fast');$('#replies_box').toggle('fast');"><i class="fa fa-reply"></i> Reply</a>
	</div>

	<h1 class="brd"><i class="fa fa-envelope-open-o" style="color: #01a650"></i> Message Details</h1>

		<?php if (isset($_GET['msg']) and $_GET['msg'] == 1) { ?>
				<div class="alert alert-success alert-dismissible fade in">
				 	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				 	<i class="fa fa-check-circle-o fa-lg"></i> Your message has been sent!
				 </div>
		<?php } ?>

		<div id="reply_box" style="width: 100%; padding: 10px; background: #F9F9F9; <?php if (!(isset($_POST['action']) && $_POST['action'] == "reply")){ ?>display: none;<?php } ?>">
		<?php if (isset($allerrors) and $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
 		<form action="" method="post">
	 		<div class="row">
				<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
				<a name="reply"></a>
				<h3><img src="<?php echo SITE_URL; ?>images/icon_reply.png" align="absmiddle" /> Reply</h3>
				<div class="form-group row">
					<textarea rows="7" cols="60" class="form-control" name="answer" required="required"><?php echo getPostParameter('answer'); ?></textarea>
          		</div>
		  		<div class="form-group row">
					<input type="hidden" name="mid" id="mid" value="<?php echo $message_id; ?>" />
					<input type="hidden" name="action" id="action" value="reply" />
					<input type="submit" class="btn btn-success btn-lg" name="Send" id="Sent" value="Reply" />
					<a class="btn btn-default btn-lg" href="javascript:void(0);" onclick="$('#reply_box').hide('fast');$('#replies_box').toggle('fast');">Cancel</a>
		  		</div>
		</div>
		</div>
		</form>
		</div>


		<div id="replies_box">
		<?php

			$ms_query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS sent_date FROM jobfia_messages WHERE user_id='$userid' AND message_id='$message_id' LIMIT 1";
			$ms_result = smart_mysql_query($ms_query);
			if (mysqli_num_rows($ms_result) > 0)
			{
				$ms_row = mysqli_fetch_array($ms_result);
				echo "<div class='support_message'>";
				echo "<div class='message_date'><i class='fa fa-clock-o'></i> ".$ms_row['sent_date']."</div>";
				echo "<div class='message_subject'>".$ms_row['subject']."</div>";
				echo "<div class='message_text'>".$ms_row['message']."</div>";
				echo "</div>";
			}

			$aquery = "SELECT *, DATE_FORMAT(answer_date, '%d %b %Y %h:%i %p') AS a_date FROM jobfia_messages_answers WHERE user_id='$userid' AND message_id='$message_id' ORDER BY answer_date ASC";
			$aresult = smart_mysql_query($aquery);
			if (mysqli_num_rows($aresult) > 0)
			{
				smart_mysql_query("UPDATE jobfia_messages SET viewed='1' WHERE message_id='$message_id' AND is_admin='1' AND user_id='$userid' AND viewed='0'");
				smart_mysql_query("UPDATE jobfia_messages_answers SET viewed='1' WHERE message_id='$message_id' AND user_id='$userid' AND viewed='0'");
			
				while ($arow = mysqli_fetch_array($aresult))
				{
					if ($arow['is_admin'] == 1) echo "<div class='answer admin_reply'>"; else echo "<div class='answer'>";
					echo "<div class='answer_date'><i class='fa fa-clock-o'></i> ".$arow['a_date']."</div>";
					echo "<div class='answer_sender'>".$sender."</div>";
					if ($arow['is_admin'] == 1) echo "<div class='answer_sender admin'>Support</div>"; else echo "<div class='answer_sender'>".$_SESSION['FirstName']."</div>";
					echo "<div class='answer_text'>".$arow['answer']."</div>";
					echo "</div>";
				}
			}

		?>
		
		<br>
		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-angle-left"></i> Go Back</a></p>
		</div>


<?php require_once ("inc/footer.inc.php"); ?>