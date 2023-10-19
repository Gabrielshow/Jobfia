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
	require_once("./inc/adm_functions.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid	= (int)$_GET['id'];
		$pn		= (int)$_GET['pn'];

		if (isset($_GET['action']) && $_GET['action'] == "block") BlockUnblockUser($uid);
		if (isset($_GET['action']) && $_GET['action'] == "unblock") BlockUnblockUser($uid,1);

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS created, DATE_FORMAT(last_login, '%d %b %Y %h:%i %p') AS last_login_date, DATE_FORMAT(last_activity, '%d %b %Y %h:%i %p') AS last_activity_date FROM jobfia_users WHERE user_id='$uid' LIMIT 1";
		$result = smart_mysql_query($query);
		$row = mysqli_fetch_array($result);
		$total = mysqli_num_rows($result);
	}

	$title = "User Details";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { ?>

		<h2><?php if ($row['avatar'] != "") { ?><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="50" height="50" class="imgs" style="border-radius: 20%"><?php }else{ ?><i class="fa fa-user-circle-o"></i><?php } ?> User Details</h2>
		<?php if ($row['top_seller'] == 1) { ?><span style="color: #F7883D;"><i class="fa fa-star"></i> TOP SELLER</span><?php } ?>

		<div class="profile_stats" style="background: #F3FCEA; border: 1px solid #E9F9DB; color: #5B5B5A; text-align: center; position: absolute; right: 270px; top: 85px;">
			<h3 class="text-center">Account Balance</h3>
			<p><span style="font-size: 20px; color: #61ab2c;"><?php echo DisplayMoney($row['balance']); ?></span></p>
			<a class="label label-success" style="color: #fff" href="user_money.php?id=<?php echo $row['user_id']; ?>">credit user</a> &nbsp;
			<a class="label label-info" style="color: #fff" href="user_payments.php?id=<?php echo $row['user_id']; ?>">view payments</a>
		</div>

		<div class="profile_stats" style="position: absolute; right: 10px; top: 375px;">
			<h3 class="text-center"><i class="fa fa-user-circle-o"></i> Employer Stats</h3>
			<label>Rating:</label> <?php echo GetEmployerRating($row['user_id']); ?><br/>
			<label>Jobs Posted:</label> <?php echo GetEmployerJobsTotal($row['user_id']); ?> &nbsp;<a href="user_jobs.php?id=<?php echo $row['user_id']; ?>">view &#155;</a><br/>
			<label>Average Job Price:</label> <?php echo GetEmployerJobPrice($row['user_id']); ?><br/>
			<label>Total Spend:</label> <?php echo GetEmployerSpendTotal($row['user_id']); ?><br/>
		</div>

		<div class="profile_stats" style="position: absolute; right: 10px; top: 85px;">
			<h3 class="text-center"><i class="fa fa-user"></i> Worker Stats</h3>
			<?php if (GetWorkerJobsTotal($row['user_id']) > 0) { ?>
				<center><?php echo GetWorkerRating($row['user_id'], $show_stars = 1); ?></center><br/>
				<label>Rating:</label> <b><?php echo GetWorkerRating($row['user_id']); ?></b><br/>
			<?php } ?>
			<label>Jobs Complete:</label> <?php echo GetWorkerJobsTotal($row['user_id']); ?> &nbsp;<a href="applicants.php?worker_id=<?php echo $row['user_id']; ?>">view &#155;</a><br/>
			<label>Satisfied:</label> <?php echo GetWorkerPaidJobs($row['user_id']); ?><br/>
			<label>Not Satisfied:</label> <?php echo GetWorkerDeclinedJobs($row['user_id']); ?><br/>
			<label>Average Job Price:</label> <?php echo GetWorkerJobPrice($row['user_id']); ?><br/>
			<label>Total Earned:</label> <?php echo GetWorkerEarnTotal($row['user_id']); ?><br/>
			<?php if ($row['rate'] != "0.0000") { ?><label>Min Job Price:</label> <?php echo DisplayMoney($row['rate']); ?><br/><?php } ?>
		</div>


          <table width="100%" style="background: #fcfcfc" cellpadding="3" cellspacing="5" border="0">
            <tr>
              <td width="150" valign="middle" align="left" class="tb1">User ID:</td>
              <td align="left" valign="middle"><?php echo $row['user_id']; ?></td>
            </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Username:</td>
            <td align="left" valign="middle"><b><?php echo $row['username']; ?></b> <?php if (ifUserOnline($row['user_id'])) { ?><sup class="online"><img src="images/icon_online.png" align="absmiddle" /> online</sup><?php } ?>	</td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Usertype:</td>
            <td align="left" valign="middle">
				<?php
					switch ($row['usertype'])
					{
						case "worker":		echo "worker"; break;
						case "employer":	echo "employer"; break;
						case "both":		echo "both"; break;
						case "":			echo "both"; break;
						default:			echo $row['usertype']; break;
					}
				?>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Account Type:</td>
            <td align="left" valign="middle"><?php if ($row['premium'] == 1) { ?><span style="color: #F7883D;"><i class="fa fa-user-circle"></i> premium</span><?php }else{ ?><i class="fa fa-user-circle"> basic<?php } ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">First Name:</td>
            <td align="left" valign="middle"><?php echo $row['fname']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Name:</td>
            <td align="left" valign="middle"><?php echo $row['lname']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Email Address:</td>
            <td align="left" valign="middle"><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
          </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Address Line 1:</td>
				<td align="left" valign="top"><?php echo ($row['address'] != "") ? $row['address'] : "---"; ?></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Address Line 2:</td>
				<td align="left" valign="top"><?php echo ($row['address2'] != "") ? $row['address2'] : "---"; ?></td>
			</tr>
          <tr>
            <td valign="middle" align="left" class="tb1">City:</td>
            <td align="left" valign="middle"><?php echo ($row['city'] != "") ? $row['city'] : "---"; ?></td>
          </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">State:</td>
				<td align="left" valign="top"><?php echo ($row['state'] != "") ? $row['state'] : "---"; ?></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Zip Code:</td>
				<td align="left" valign="top"><?php echo ($row['zip'] != "") ? $row['zip'] : "---"; ?></td>
			</tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Country:</td>
            <td align="left" valign="middle"><?php echo GetCountry($row['country_id']); ?></td>
          </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Phone:</td>
				<td align="left" valign="top"><?php echo ($row['phone'] != "") ? $row['phone'] : "---"; ?></td>
			</tr>
			<tr>
				<td width="30" valign="middle" align="left" class="tb1">Address Verified:</td>
				<td align="left" valign="top"><?php echo ($row['pin_verified'] == 1) ? "<img src='./images/icons/yes.png'>" : "<img src='./images/icons/no.png'>"; ?></td>
			</tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">Company:</td>
            <td align="left" valign="middle"><?php echo ($row['company'] != "") ? $row['company'] : "---"; ?></td>
          </tr>
		  <?php if ($row['ref_id'] > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Referred By:</td>
            <td align="left" valign="middle"><?php echo GetUsername($row['ref_id'], 1); ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Referrals:<?php if (GetReferralsTotal($row['user_id']) > 0) { ?><br><a class="badge" href="user_referrals.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-users"></i> view referrals</a><?php } ?></td>
            <td align="left" valign="middle">
				<table width="60%" style="background: #F6F6F6;" border="0" cellspacing="0" cellpadding="10">
				<tr>
					<td width="25%" align="center" valign="top"><br/><span class="count" style="background:#939393;"><?php echo GetRefClicksTotal($row['user_id']); ?></span><br/><br/> <i class="fa fa-hand-pointer-o"></i> referral link clicks</td>
					<td width="25%" align="center" valign="top"><br/><span class="count" style="background:#6EB8C9;"><a href="user_referrals.php?id=<?php echo $row['user_id']; ?>"><?php echo GetReferralsTotal($row['user_id']); ?></a></span><br/><br/> <i class="fa fa-users"></i> referrals</td>
					<td width="25%" align="center" valign="top"><br/><span class="count" style="background:#FFAD16;"><?php echo GetReferralsPendingBonuses($row['user_id']); ?></span><br/><br/> <i class="fa fa-money" style="color: #ffad16"></i> pending earnings</td>
					<td nowrap width="25%" align="center" valign="top"><br/><span class="count" style="background:#7AD108;"><?php echo GetReferralsPaidBonuses($row['user_id']); ?></span><br/><br/> <i class="fa fa-money" style="color: #7ad108"></i> paid earnings</td>
				</tr>
				</table>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Referral Link:</td>
            <td align="left" valign="middle"><span style="color: #777"><?php $ReferralLink = SITE_URL."?ref=".$row['user_id']; echo $ReferralLink; ?></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Newsletter:</td>
            <td align="left" valign="middle"><?php echo ($row['newsletter'] == 1) ? "<img src='./images/icons/yes.png'>" : "<img src='./images/icons/no.png'>"; ?></td>
          </tr>
		  <?php if ($row['about'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">About Me:</td>
            <td align="left" valign="middle"><div style="width: 58%; text-align: left; background: #FFF; border-radius: 5px; padding: 7px;"><?php echo $row['about']; ?></div></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Signup Date:</td>
            <td align="left" valign="middle"><?php echo $row['created']; ?></td>
          </tr>
			<?php if ($row['auth_provider'] != "") { ?>
            <tr>
              <td valign="middle" align="left" class="tb1">Registered with:</td>
              <td align="left" valign="middle"><?php echo $row['auth_provider']; ?></td>
            </tr>
			<?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">IP Address:</td>
            <td align="left" valign="middle"><?php echo $row['ip']; ?></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Login Count:</td>
            <td align="left" valign="middle"><?php echo $row['login_count']; ?></td>
          </tr>
		  <?php if ($row['login_count'] > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Login:</td>
            <td align="left" valign="middle"><?php echo $row['last_login_date']; ?> &nbsp; <sup><?php echo relative_date(strtotime($row['last_login'])); ?></sup></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Activity:</td>
            <td align="left" valign="middle"><?php echo $row['last_activity_date']; ?> &nbsp; <sup><?php echo relative_date(strtotime($row['last_activity'])); ?></sup></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Last Login IP:</td>
            <td align="left" valign="middle"><?php echo $row['last_ip']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td align="left" valign="middle">
				<?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?>
				<?php if ($row['login_count'] == 0 && $row['activation_key'] != "") { ?> &nbsp; <img src="images/wait.gif" align="absmiddle" /> <span style="color: #999">awaiting email confirmation</span><?php } ?>
			</td>
          </tr>
		  <?php if ($row['status'] == "inactive" && $row['block_reason'] != "") { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Block Reason:</td>
            <td style="color: #EB0000; background: #FFEBEB; border-left: 2px #FF0000 solid" align="left" valign="top"><?php echo $row['block_reason']; ?></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['status'] == "active") { ?>
          <tr>
            <td colspan="2" bgcolor="#F5F5F5" height="50" style="padding-left: 125px; border-top: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee;" align="left" valign="middle">
				<p><a class="blockit" href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $pn; ?>&action=block">Block User</a></p>
			</td>
          </tr>
		  <?php }else{ ?>
          <tr>
            <td colspan="2" bgcolor="#F5F5F5" height="50" style="padding-left: 125px; border-top: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee;" align="left" valign="middle">
				<p><a class="unblockit" href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $pn; ?>&action=unblock">UnBlock User</a></p>
			</td>
          </tr>
		  <?php } ?>
          <tr>
			<td colspan="2" align="center" valign="top">
				<input type="button" class="btn btn-success" name="edit" value="Edit User" onClick="javascript:document.location.href='user_edit.php?id=<?php echo $row['user_id']; ?>&page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='users.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
				<input type="button" class="btn btn-danger" style="position: absolute; right: 10px;" name="delete" value="Delete User" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='user_delete.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
			</td>
          </tr>
          </table>

	  <?php }else{ ?>
				<div class="alert alert-info">Sorry, no user found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>