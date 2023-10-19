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

	$query = "SELECT *, COUNT(*) AS total_referred, DATE_FORMAT(created, '%d %b %Y') AS signup_date, DATE_FORMAT(last_activity, '%d %b %Y') AS last_activity_date FROM jobfia_users WHERE ref_id>0 GROUP BY ref_id ORDER BY total_referred DESC LIMIT 30";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Top 30 Referrals";

	require_once ("inc/header.inc.php");

?>

       <h2><i class="fa fa-trophy" style="color: #5cb85c"></i>  Top 30 Referrals</h2>

            <table style="border-bottom: 1px solid #F7F7F7" align="center" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr bgcolor="#F7F7F7" align="center">
				<th width="9%">&nbsp;</th>
				<th width="25%">Full Name/Username</th>
				<th width="12%"><i class="fa fa-users"></i> Total Referred</th>
				<th width="17%">Email</th>
				<th width="19%">Country</th>
				<th width="10%">Balance</th>
				<th width="10%">Signup Date</th>
				<th width="10%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php if ($total > 0) { ?>
			 <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="55" height="55" class="img-responsive" border="0" style="border-radius: 20%"></a>
						<?php if (ifUserOnline($row['user_id'])) { ?><sup><span class="online" title="online"><img src="images/icon_online.png" align="absmiddle" /></span></sup><?php } ?>
					</td>
					<td nowrap="nowrap" align="left" valign="middle">
						<i class="fa fa-user-circle"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a>
						<br/><span style="color: #999"><?php echo $row['username']; ?></span>
					</td>
					<td nowrap="nowrap" align="center" valign="middle"><span class="badge"><?php echo number_format($row['total_referred']); ?></span></td>
					<td nowrap="nowrap" align="left" valign="middle"><a style="color: #777" href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php echo GetCountry($row['country_id'], $show_name = 1); ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php echo DisplayMoney($row['balance']); ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['signup_date']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="user_money.php?id=<?php echo $row['user_id']; ?>" title="Add/Deduct Money"><img src="images/cash.png" border="0" alt="Add/Deduct Money" /></a>
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="user_edit.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='user_delete.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>

        <?php }else{ ?>
				<tr><td colspan="9"><div class="alert alert-info">There are no users at this time.</div></td></tr>
        <?php } ?>

		</table>

		<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;" /></p>


<?php require_once ("inc/footer.inc.php"); ?>