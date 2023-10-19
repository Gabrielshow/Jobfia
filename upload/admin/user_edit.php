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


	$pn = (int)$_GET['pn'];


	if (isset($_POST['action']) && $_POST['action'] == "edituser")
	{
		unset($errs);
		$errs = array();

		$user_id		= (int)getPostParameter('userid');
		$usertype		= getPostParameter('usertype');
		$fname			= mysqli_real_escape_string($conn, getPostParameter('fname'));
		$lname			= mysqli_real_escape_string($conn, getPostParameter('lname'));
		$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$address		= mysqli_real_escape_string($conn, getPostParameter('address'));
		$address2		= mysqli_real_escape_string($conn, getPostParameter('address2'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$state			= mysqli_real_escape_string($conn, getPostParameter('state'));
		$zip			= mysqli_real_escape_string($conn, getPostParameter('zip'));
		$phone			= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$rate			= mysqli_real_escape_string($conn, getPostParameter('rate'));
		$company		= mysqli_real_escape_string($conn, getPostParameter('company'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$country		= (int)getPostParameter('country');
		$pin_verified	= (int)getPostParameter('pin_verified');
		$balance		= mysqli_real_escape_string($conn, getPostParameter('balance'));
		$pwd			= mysqli_real_escape_string($conn, getPostParameter('password'));
		$pwd2			= mysqli_real_escape_string($conn, getPostParameter('password2'));
		$premium		= (int)getPostParameter('premium');
		$newsletter		= (int)getPostParameter('newsletter');
		$about			= mysqli_real_escape_string($conn, nl2br(getPostParameter('about')));
		$block_reason	= mysqli_real_escape_string($conn, nl2br(getPostParameter('block_reason')));
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));

		$flag = 0;

		if (!($fname && $lname && $email && $country && $status))
		{
			$errs[] = "Please fill in all required fields";
		}

		if (isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = "Invalid email address";
		}

		if (isset($balance) && $balance != "" && !is_numeric($balance))
		{
			$errs[] = "Wrong account balance value";
		}

		if (isset($pwd) && $pwd != "" && isset($pwd2) && $pwd2 != "")
		{
			if ($pwd !== $pwd2)
			{
				$errs[] = "Password confirmation is wrong";
			}
			elseif ((strlen($pwd)) < 6 || (strlen($pwd2) < 6) || (strlen($pwd)) > 20 || (strlen($pwd2) > 20))
			{
				$errs[] = "Password must be between 6-20 characters (letters and numbers)";
			}
			elseif (stristr($pwd, ' '))
			{
				$errs[] = "Password must not contain spaces";
			}
			else
			{
				$flag = 1;
			}
		}

		if (count($errs) == 0)
		{
			if ($flag == 1) $psql = "password='".PasswordEncryption($pwd)."',"; else $psql = "";

			$sql = "UPDATE jobfia_users SET usertype='$usertype', ".$psql." email='$email', fname='$fname', lname='$lname', address='$address', address2='$address2', about='$about', company='$company', city='$city', state='$state', zip='$zip', country_id='$country', phone='$phone', pin_verified='$pin_verified', balance='$balance', premium='$premium', newsletter='$newsletter', status='$status', block_reason='$block_reason' WHERE user_id='$user_id' LIMIT 1";

			if (smart_mysql_query($sql))
			{
				header("Location: users.php?msg=updated&page=".$pn."&column=".$_GET['column']."&order=".$_GET['order']);
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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid = (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_users WHERE user_id='$uid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Edit User";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) {

		  $row = mysqli_fetch_array($result);
		  
      ?>

        <h2><i class="fa fa-user-circle-o"></i> Edit User</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

		<div style="background: #F9F9F9; padding: 10px 0;">
		<div style="position: absolute; right: 10px;"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="70" height="70" class="imgs" border="0" style="margin: 5px;" /></div>

		<form action="" method="post">

		<div style="position: absolute; width: 330px; right: 200px; top: 80px; padding: 10px 5px; ">
		<p align="center"><b><i class="fa fa-map-marker"></i> Address</b></p>
		 <table align="center" cellpadding="2" cellspacing="3" border="0">
			<tr>
				<td width="70" valign="middle" align="left" class="tb1">Address Line 1:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="address" id="address" value="<?php echo $row['address']; ?>" size="32" /></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Address Line 2:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="address2" id="address2" value="<?php echo $row['address2']; ?>" size="32" /></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">City:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="city" id="city" value="<?php echo $row['city']; ?>" size="32" /></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">State:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="state" id="state" value="<?php echo $row['state']; ?>" size="32" /></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Zip Code:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="zip" id="zip" value="<?php echo $row['zip']; ?>" size="32" /></td>
			</tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Country:</td>
            <td align="left" valign="top">
				<select name="country" class="form-control" id="country" style="width: 185px">
				<option value="">-- select country --</option>
				<?php

					$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($row['country_id'] == $row_country['country_id'])
								echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
							else
								echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
						}
					}

				?>
				</select>			
			</td>
          </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Phone:</td>
				<td align="left" valign="top"><input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row['phone']; ?>" size="32" /></td>
			</tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td align="left" valign="middle">
	            <div class="checkbox">
	            	<label><input type="checkbox" name="pin_verified" class="checkboxx" value="1" <?php echo (@$row['pin_verified'] == 1) ? "checked" : "" ?>/> Verified Address</label>
	            </div>
	        </td>
          </tr>
		 </table>
		</div>

          <table cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="100" valign="middle" align="left" class="tb1">Username:</td>
            <td valign="top"><b><?php echo $row['username']; ?></b></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Usertype:</td>
            <td valign="top">
				<select name="usertype">
					<option value="worker" <?php if ($row['usertype'] == "worker") echo "selected='selected'"; ?>>worker</option>
					<option value="employer" <?php if ($row['usertype'] == "employer") echo "selected='selected'"; ?>>employer</option>
					<option value="both" <?php if ($row['usertype'] == "both" || $row['usertype'] == "") echo "selected='selected'"; ?>>both</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>First Name:</td>
            <td valign="top"><input type="text" name="fname" id="fname" value="<?php echo $row['fname']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Last Name:</td>
            <td valign="top"><input type="text" name="lname" id="lname" value="<?php echo $row['lname']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Email Address:</td>
            <td valign="top"><input type="text" name="email" id="email" value="<?php echo $row['email']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Company:</td>
            <td valign="top"><input type="text" name="company" id="company" value="<?php echo $row['company']; ?>" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Balance:<br><a class="badge" href="user_money.php?id=<?php echo $row['user_id']; ?>">+ credit user</a></td>
            <td valign="middle">
	            <div class="input-group">
		            <span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
		            <input type="text" name="balance" id="balance" value="<?php echo DisplayMoney($row['balance'], $hide_cyrrency_sign=1); ?>" size="10" class="form-control" />
	            </div>
	            
	        </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-user-circle" style="color: #ffb219"></i> Premium Account</td>
            <td align="left" valign="middle"><input type="checkbox" name="premium" class="checkboxx" value="1" <?php echo (@$row['premium'] == 1) ? "checked" : "" ?>/> Yes</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Newsletter:</td>
            <td align="left" valign="middle"><input type="checkbox" name="newsletter" class="checkboxx" value="1" <?php echo (@$row['newsletter'] == 1) ? "checked" : "" ?>/></td>
          </tr>
          <tr>
            <td width="100" valign="middle" align="left" class="tb1">New Password:</td>
            <td valign="top"><input type="password" name="password" id="password" value="" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Confirm New Password:</td>
            <td valign="top"><input type="password" name="password2" id="password2" value="" size="32" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">About Me:</td>
            <td valign="top"><textarea name="about" cols="32" rows="7" class="form-control" style="width: 325px;"><?php echo strip_tags($row['about']); ?></textarea></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" id="status" onchange="update_status();">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr id="block_reason">
            <td valign="middle" align="left" class="tb1">Block Reason:<br/><small>(optional)</small></td>
            <td valign="top"><textarea name="block_reason" cols="55" rows="2" class="form-control" style="width: 325px;"><?php echo $row['block_reason']; ?></textarea></td>
          </tr>
          <tr>
			<td>&nbsp;</td>
            <td align="left" valign="bottom">
				<input type="hidden" name="userid" id="userid" value="<?php echo (int)$row['user_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edituser" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='users.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
            </td>
          </tr>
        </table>
      </form>
	  </div>

		<script type="text/javascript">
		$(function(){
			update_status();
		});
		function update_status(){
			nstatus = $("#status").val();
			if(nstatus == 'inactive'){
				$("#block_reason").show();
			}else{
				$("#block_reason").hide();
			}
		}
		</script>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no user found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>