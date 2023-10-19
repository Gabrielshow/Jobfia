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

		// Delete users //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$userid = (int)$v;
					DeleteUser($userid);
				}

				header("Location: user_referrals.php?id=".$_GET['id']."&msg=deleted");
				exit();
			}	
		}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y <sup>%h:%i %p</sup>') AS signup_date FROM jobfia_users WHERE ref_id='".(int)$uid."' AND status='active' ORDER BY created DESC";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "User Referrals";
	require_once ("inc/header.inc.php");

?>

	<h2><i class="fa fa-users"></i> <?php echo GetUsername($uid); ?> Referred Users <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?></h2>

	<?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "deleted": echo "Users has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<p class="text-right"><span class="badge"><i class="fa fa-external-link"></i> Referral Link: <span style="color: #fff"><?php $ReferralLink = SITE_URL."?ref=".$uid; echo $ReferralLink; ?></span></span></p>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="6%">User ID</th>
				<th width="6%">&nbsp;</th>
				<th width="27">Full Name / Username</th>
				<th width="22">Email</th>
				<th width="12%">Country</th>
				<th width="12%">Balance</th>
				<th width="12%">Signup Date</th>
				<th width="12%">Status</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr style="height:25px;" bgcolor="<?php if (($cc%2) == 0) echo "#F7F7F7"; else echo "#FFFFFF"; ?>">
                  <td align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
                  <td valign="middle" align="center"><?php echo $row['user_id']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="50" height="50" class="imgs" border="0" /></a>
						<?php if (ifUserOnline($row['user_id'])) { ?><sup><span class="online" title="online"><img src="images/icon_online.png" align="absmiddle" /></span></sup><?php } ?>
					</td>
                  <td valign="middle" align="left"><i class="fa fa-user-circle-o fa-lg" style="color: #bbb"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a><br/><span style="color: #999"><?php echo $row['username']; ?></span></td>
                  <td valign="middle" align="left" style="padding-left: 10px"><?php echo $row['email']; ?></td>
                  <td valign="middle" align="left" style="padding-left: 10px"><?php echo GetCountry($row['country_id']); ?></td>
				  <td valign="middle" align="left" style="padding-left: 10px"><?php echo DisplayMoney($row['balance']); ?></td>
				  <td valign="middle" align="center"><?php echo $row['signup_date']; ?></td>
                  <td valign="middle" align="center">
					<?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?>
				  </td>
                </tr>
				<?php } ?>
			  <tr>
				<td colspan="9" align="left" style="border-top: 1px solid #F7F7F7" >
					<input type="hidden" name="action" value="delete" />
					<input type="hidden" name="id" value="<?php echo $uid; ?>" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
			  </tr>
           </table>
		   </form>
	  
	  <?php }else{ ?>
				<div class="alert alert-info text-center">This user does not receive any referrals at this time.</div>
      <?php } ?>

			<?php if (@$uid) { ?>
				<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='user_details.php?id=<?php echo $uid; ?>'" /></p>
			<?php }else{ ?>
				<p align="center"><input class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" type="button"></p>
			<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>