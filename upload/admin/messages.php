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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/adm_functions.inc.php");


	$results_per_page = 10;


		// Delete messages //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$mid = (int)$v;
					DeleteMessage($mid);
				}

				header("Location: messages.php?msg=deleted");
				exit();
			}
		}


		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "subject": $rrorder = "subject"; break;
					case "sender": $rrorder = "u.lname"; break;
					case "ids": $rrorder = "message_id"; break;
					default: $rrorder = "message_id"; break;
				}
			}
			else
			{
				$rrorder = "message_id";
			}

			if (isset($_GET['order']) && $_GET['order'] != "")
			{
				switch ($_GET['order'])
				{
					case "asc": $rorder = "asc"; break;
					case "desc": $rorder = "desc"; break;
					default: $rorder = "desc"; break;
				}
			}
			else
			{
				$rorder = "desc";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT m.*, DATE_FORMAT(m.created, '%d %b %Y <sup>%h:%i %p</sup>') AS message_date, u.fname, u.lname FROM jobfia_messages m LEFT JOIN jobfia_users u ON m.user_id=u.user_id WHERE m.is_admin='0' ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_messages WHERE is_admin='0'";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


	$title = "Messages from members";
	require_once ("inc/header.inc.php");

?>

       <h2><i class="fa fa-support"></i> Messages from members <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?></h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<i class="fa fa-check-circle-o fa-lg"></i> 
				<?php
					switch ($_GET['msg'])
					{
						case "sent": echo "Message has been successfully sent!"; break;
						case "deleted": echo "Message has been successfully deleted!"; break;
					}
				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td valign="middle" align="left" width="50%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="sender" <?php if ($_GET['column'] == "sender") echo "selected"; ?>>Sender</option>
			<option value="subject" <?php if ($_GET['column'] == "subject") echo "selected"; ?>>Subject</option>
          </select>
          <select name="order" id="order" onChange="document.form1.submit()">
			<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
			</td>
			<td nowrap="nowrap" valign="middle" width="45%" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
		</tr>
		</table>
		</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="5%">&nbsp;</th>
				<th width="15%">Date</th>
				<th width="40%">Subject</th>
				<th width="17%">From</th>
				<th width="7%">Replies</th>
				<th width="17%">Last reply from</th>
				<th width="10%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['message_id']; ?>]" id="id_arr[<?php echo $row['message_id']; ?>]" value="<?php echo $row['message_id']; ?>" /></td>
					<td align="center" valign="middle"><i class="fa fa-envelope fa-lg" style="color: #5cb85c"></i></td>
					<td align="center" valign="middle"><?php echo $row['message_date']; ?></td>
					<td align="left" valign="middle" style="padding-left: 7px">
						<a href="message_details.php?id=<?php echo $row['message_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View">
						<?php
							if (strlen($row["subject"]) > 100) $msubject = substr($row["subject"], 0, 100)."..."; else $msubject = $row["subject"];
							if ($row['viewed'] == 0) echo "<b>".$msubject."</b>"; else echo $msubject;
						?>
						</a>
					</td>
					<td align="left" valign="middle"><?php echo Getusername($row['user_id'], $username = 1); ?></td>
					<td align="center" valign="middle"><a href="message_details.php?id=<?php echo $row['message_id']; ?>"><span class="badge" style="background: #cae7ce"><?php echo GetRepliesNum($row['message_id']); ?> <i class="fa fa-comments-o" style="color: #5cb85c"></i></span> </a></td>
					<td align="left" valign="middle" style="padding-left: 7px"><?php echo GetMessageLastReaply($row['message_id'], $username = 1); ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="message_details.php?id=<?php echo $row['message_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="message_reply.php?id=<?php echo $row['message_id']; ?>" title="Reply"><img src="images/reply.png" border="0" alt="Reply" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this message?') )location.href='message_delete.php?id=<?php echo $row['message_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td colspan="7" align="left" style="border-top: 1px solid #F7F7F7">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="action" value="delete" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
				</tr>
            </table>
			</form>
		</table>

			<?php echo ShowPagination("messages",$results_per_page,"messages.php?column=$rrorder&order=$rorder&","WHERE is_admin='0'"); ?>

        <?php }else{ ?>
				<div class="alert alert-info text-center">There are no messages at this time.</div
        <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>