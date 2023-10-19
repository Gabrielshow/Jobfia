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
		$mid	= (int)$_GET['id'];
		$pn		= (int)$_GET['pn'];

		$query = "SELECT m.*, DATE_FORMAT(m.created, '%d %b %Y %h:%i %p') AS message_date, u.fname, u.lname FROM jobfia_messages m, jobfia_users u WHERE m.user_id=u.user_id AND m.message_id='$mid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			// mark message as viewed //
			smart_mysql_query("UPDATE jobfia_messages SET viewed='1' WHERE message_id='$mid' LIMIT 1");
		}
	}

	$title = "View Message";
	require_once ("inc/header.inc.php");

?>   
    
	<?php

		if ($total > 0)
		{
				$row = mysqli_fetch_array($result);
	?>

	   <h2>View Message</h2>

		<form action="" method="post" name="form1">
          <table width="90%" align="center" cellpadding="3" cellspacing="3" border="0">
            <tr>
              <td valign="top"><i class="fa fa-user-circle-o" style="color: #bbb"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
            </tr>
            <tr>
              <td nowrap="nowrap" valign="top"><b><?php echo $row['subject']; ?></b></td>
            </tr>
           <tr>
            <td valign="top">
				<div style="width: 97%; background: #FFFFFF; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px; min-height: 200px;">
					<div align="right"><span class="date"><?php echo $row['message_date']; ?></span></div><br/>
					<?php echo $row['message']; ?>
				</div>
			</td>
          </tr>

		<?php

			$aquery = "SELECT *, DATE_FORMAT(answer_date, '%d %b %Y %h:%i %p') AS a_date FROM jobfia_messages_answers WHERE user_id='".$row['user_id']."' AND message_id='$mid' ORDER BY answer_date ASC";
			$aresult = smart_mysql_query($aquery);
			$atotal = mysqli_num_rows($aresult);
			
			if ($atotal > 0) { 
				while ($arow = mysqli_fetch_array($aresult)) { if ($arow['is_admin'] == 1) { $sender = "Admin"; $bg = "#F7FBFF"; }else{ $sender = "Member"; $bg = "#F7F7F7"; }
				
		?>				
			<tr>
				<td valign="top">
					<br>
					<?php if ($sender == "Admin") { ?><i class="fa fa-user-circle" style="color: #5ac0de"></i><?php }else{ ?><i class="fa fa-user-o"></i><?php } ?> <?php echo $sender; ?><br>
					<div style="width: 97%; background: <?php echo $bg; ?>; border: 1px solid #F5F5F5; border-radius: 5px; padding: 10px; min-height: 30px;">
						<div align="right"><span class="date"><?php echo $arow['a_date']; ?></span></div><br/>
						<?php echo $arow['answer']; ?>
					</div>
				</td>
			</tr>
		
		<?php } ?>
		
		<?php }	?>

          <tr>
		   <td align="center" valign="top">
			<input type="button" class="btn btn-success" name="reply" value="Reply" onClick="javascript:document.location.href='message_reply.php?id=<?php echo $mid; ?>'" />
			<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='messages.php'" />
			<input type="button" class="btn btn-danger" style="position: absolute; right: 10px;" name="delete" value="Delete Message" onclick="if (confirm('Are you sure you really want to delete this message?') )location.href='message_delete.php?id=<?php echo $mid; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
		  </td>
          </tr>
          </table>
		</form>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no message found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>