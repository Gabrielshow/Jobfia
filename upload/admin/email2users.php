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


	$query = "SELECT * FROM jobfia_users WHERE email != '' AND newsletter='1' AND status='active'";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$query2 = "SELECT * FROM jobfia_users WHERE email != ''";
	$result2 = smart_mysql_query($query2);
	$total2 = mysqli_num_rows($result2);


	if (isset($_POST['action']) && $_POST['action'] == "email2users")
	{
		$msubject	= trim($_POST['msubject']);
		$allmessage = $_POST['allmessage'];
		$recipients = $_POST['recipients'];

		$country		= array();
		$country		= $_POST['country_id'];

		$where = "";
		if (count($country) > 0)
		{
			$where .= "AND country_id IN (".implode(",", $country).")";
		}

		unset($errs);
		$errs = array();

		if (!($msubject && $allmessage))
		{
			$errs[] = "Please enter subject and message";
		}

		switch ($recipients)
		{
			case "all":			$query = "SELECT * FROM jobfia_users WHERE email != '' $where"; break;
			case "subscribed":	$query = "SELECT * FROM jobfia_users WHERE email != '' $where AND newsletter='1' AND status='active'"; break;;
		}

		if (count($errs) == 0)
		{
			$result = smart_mysql_query($query);

			while ($row = mysqli_fetch_array($result))
			{
				$allmessage = str_replace("{first_name}", $row['fname'], $allmessage);
				$allmessage = str_replace("{unsubscribe_link}", SITE_URL."unsubscribe.php?key=".$row['unsubscribe_key'], $allmessage);
				$message = "<html>
							<head>
								<title>".$subject."</title>
							</head>
							<body>".$allmessage."</body>
							</html>";
				$to_email = $row['fname'].' '.$row['lname'].' <'.$row['email'].'>';
				
				SendEmail($to_email, $msubject, $message, $noreply_mail = 1);
			}

			header ("Location: email2users.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>";
		}
	}
	else
	{
		$allmessage = "
			<p style='font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size:13px;'>
			<br><br><br><br>
			<div style='font-family:tahoma,arial,sans-serif;padding-top:12px;clear:both;font-size:13px;color:#5B5B5B;text-align:left;'>	
			----------------<br/>
			You are receiving this email as you have directly signed up to ".SITE_TITLE.".<br>If you do not wish to receive these messages in the future, please <a href='{unsubscribe_link}' target='_blank'>unsubscribe</a>.</div></p>";
	}

	$title = "Send Email to All Members";
	require_once ("inc/header.inc.php");

?>

      <?php if ($total2 > 0) { ?>

        <h2><i class="fa fa-envelope-o"></i> Send Email to Members</h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
			<div class="alert alert-success">Your message has been successfully sent!</div>
		<?php }else{ ?>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<div class="subscribers">
			<span style="font-size:15px; color:#FFF; background:#777; padding:11px 18px; border-radius: 8px;"><?php echo $total2; ?></span>&nbsp; <?php echo ($total2 == 1) ? "member" : "members"; ?><br/><br/>
			<span style="font-size:15px; color:#FFF; background: #5cb85c; padding:11px 18px; border-radius: 8px;"><?php echo $total; ?></span>&nbsp; subscribed <?php echo ($total == 1) ? "member" : "members"; ?>
		</div>

        <form action="email2users.php" method="post">
          <table width="100%" style="background: #fcfcfc" align="center" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="100" valign="middle" align="left" class="tb1">Send To:</td>
            <td valign="top">
				<select name="recipients" class="form-control">
					<option value="all" <?php echo ($recipients == 'all') ? "selected='selected'" : ""; ?>>All Members (<?php echo $total2; ?>)</option>
					<?php if ($total > 0) { ?>
						<option value="subscribed" <?php echo ($recipients == 'subscribed') ? "selected='selected'" : ""; ?>>Subscribed Members (<?php echo $total; ?>)</option>
					<?php } ?>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top">
				<i class="fa fa-users fa-lg"></i> Members from:<br/>
				<div class="scrollbox">
				<div class="odd"><input type="checkbox" name="allcountries" value="" <?php if (count($country) == 0) echo 'checked="checked"'; ?>>All Countries</div>
				<?php

					$sql_country = "SELECT COUNT(*) AS country_users, users.country_id, countries.name FROM jobfia_users users, jobfia_countries countries WHERE users.country_id=countries.country_id AND users.newsletter = '1' AND users.status='active' GROUP BY users.country_id ORDER BY country_users DESC";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							$cc++;
							if (is_array($country) && in_array($row_country['country_id'], $country)) $checked = 'checked="checked"'; else $checked = '';

							if (($cc%2) == 0)
								echo "<div class=\"even\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked.">".$row_country['name']." (".$row_country['country_users'].")</div>";
							else
								echo "<div class=\"odd\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked.">".$row_country['name']." (".$row_country['country_users'].")</div>";
						}
					}

				?>
				</div>
			</td>
          </tr>
          <tr>
            <td nowrap="nowrap" width="35" valign="middle" align="left" class="tb1">From:</td>
            <td valign="middle"><?php echo EMAIL_FROM_NAME; ?> &lt;<?php echo NOREPLY_MAIL; ?>&gt; &nbsp;<a href="settings.php#mail"><img src="images/icon_edit.png" align="absmiddle">edit</a></td>
          </tr>
          <tr>
            <td nowrap="nowrap" width="35" valign="middle" align="left" class="tb1">Subject:</td>
            <td valign="top"><input type="text" name="msubject" id="msubject" value="<?php echo $msubject; ?>" size="70" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td height="30" bgcolor="#F7F7F7" align="center" valign="middle">
				<p>The following variables can be used in email message:</p>
				<table width="95%" align="center" cellpadding="2" cellspacing="2" border="0">
					<tr><td nowrap="nowrap" align="left"><b>{first_name}</b></td><td nowrap="nowrap" align="left"> - Member First Name</td></tr>
					<tr><td nowrap="nowrap" align="left"><b>{unsubscribe_link}</b></td><td nowrap="nowrap" align="left"> - Newsletter Unsubscribe Link</td></tr>
				</table>
			</td>
          </tr>
          <tr>
            <td nowrap="nowrap" valign="middle" align="left" class="tb1">Message:</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="allmessage" rows="10"><?php echo stripslashes($allmessage); ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>		
			</td>
          </tr>
          <tr>
			<td>&nbsp;</td>
            <td align="left" valign="top">
				<input type="hidden" name="action" id="action" value="email2users" />
				<input type="submit" name="Send" id="Send" class="btn btn-success" value="Send Message" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='index.php'" />
            </td>
          </tr>
        </table>
      </form>

		<?php } ?>

      <?php }else{ ?>
				<div class="alert alert-info">There are no members at this time.</div>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>