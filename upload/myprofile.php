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

	$query	= "SELECT * FROM jobfia_users WHERE user_id='$userid' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total	= mysqli_num_rows($result);

	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
	}
	else
	{
		header ("Location: logout.php");
		exit();
	}

	
	if (isset($_POST['action']) && $_POST['action'] == "editprofile")
	{
		unset($errs);
		$errs = array();

		$fname			= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('fname'))));
		$lname			= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('lname'))));
		$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$address		= mysqli_real_escape_string($conn, getPostParameter('address'));
		$address2		= mysqli_real_escape_string($conn, getPostParameter('address2'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$state			= mysqli_real_escape_string($conn, getPostParameter('state'));
		$zip			= mysqli_real_escape_string($conn, getPostParameter('zip'));
		$phone			= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$rate			= mysqli_real_escape_string($conn, getPostParameter('rate'));
		$company		= mysqli_real_escape_string($conn, getPostParameter('company'));
		$about			= mysqli_real_escape_string($conn, nl2br(getPostParameter('about')));
		$about			= substr($about, 0, 500);
		$newsletter		= (int)getPostParameter('newsletter');
		$notification1	= (int)getPostParameter('notification1');
		$notification2	= (int)getPostParameter('notification2');
		$notification3	= (int)getPostParameter('notification3');
		$notification4	= (int)getPostParameter('notification4');
		$notification5	= (int)getPostParameter('notification5');

		if(!($fname && $lname && $email))
		{
			$errs[] = "Please fill in all fields";
		}

		if(isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = "Please enter a valid email address";
		}

		if (isset($rate) && $rate != "" && !is_numeric($rate))
		{
			$errs[] = "Please enter correct your min job price";
		}

		if (count($errs) == 0)
		{	
			$_SESSION['FirstName'] = $fname;
			
			smart_mysql_query("UPDATE jobfia_users SET email='$email', fname='$fname', lname='$lname', address='$address', address2='$address2', about='$about', company='$company', city='$city', state='$state', zip='$zip', phone='$phone', rate='$rate', newsletter='$newsletter', notification1='$notification1', notification2='$notification2', notification3='$notification3', notification4='$notification4' WHERE user_id='$userid' LIMIT 1");

			header("Location: myprofile.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i>  ".$errorname."<br/>\n";
		}	
	}


	if (isset($_POST['action']) && $_POST['action'] == "changepwd")
	{
		unset($errs);
		$errs = array();

		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$newpwd		= mysqli_real_escape_string($conn, getPostParameter('newpassword'));
		$newpwd2	= mysqli_real_escape_string($conn, getPostParameter('newpassword2'));

		if (!($pwd && $newpwd && $newpwd2))
		{
			$errs[] = "Please fill in all fields";
		}
		else
		{
			if (PasswordEncryption($pwd) !== $row['password'])
			{
				$errs[] = "Your current password is wrong";
			}

			if ($newpwd !== $newpwd2)
			{
				$errs[] = "Password confirmation is wrong";
			}
			elseif ((strlen($newpwd)) < 6 || (strlen($newpwd2) < 6) || (strlen($newpwd)) > 20 || (strlen($newpwd2) > 20))
			{
				$errs[] = "Password must be between 6-20 characters (letters and numbers)";
			}
			elseif (stristr($newpwd, ' '))
			{
				$errs[] = "Password must not contain spaces";
			}
		}

		if (count($errs) == 0)
		{
			$upp_query = "UPDATE jobfia_users SET password='".PasswordEncryption($newpwd)."' WHERE user_id='$userid' LIMIT 1";
		
			if (smart_mysql_query($upp_query))
			{
				header("Location: myprofile.php?msg=2");
				exit();
			}	
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i>  ".$errorname."<br/>\n";
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "change_avatar")
	{
		unset($errs);
		$errs = array();

		$upload_dir	= PUBLIC_HTML_PATH.AVATARS_URL;


		if (!($_FILES['avatar']['tmp_name']))
		{
			$errs[] = "Please select avatar image";
		}
		else
		{
			if (is_uploaded_file($_FILES['avatar']['tmp_name']))
			{
				list($width, $height, $type) = getimagesize($_FILES['avatar']['tmp_name']);

				if (!getimagesize($_FILES['avatar']['tmp_name']))
				{
					$errs[] = "Only image uploads are allowed";
				}
				elseif ($width < AVATAR_WIDTH || $height < AVATAR_HEIGHT)
				{
					$errs[] = "Too low image dimension. Min ".AVATAR_WIDTH."x".AVATAR_HEIGHT." px";
				}
				elseif ($_FILES['avatar']['size'] > 524288)
				{
					$errs[] = "The image file size is too big. It exceeds 500 Kb.";
				}
				elseif (preg_match('/\\.(gif|jpg|png|jpeg)$/i', $_FILES['avatar']['name']) != 1)
				{
					$errs[] = "Please upload a JPEG, PNG, or GIF image";
					unlink($_FILES['avatar']['tmp_name']);
				}
				else
				{
					$img_path			= $upload_dir.$_FILES['avatar']['name'];
					
					$rnd_number			= mt_rand(1,10000).time();
					$new_avatar_name	= "avatar_".$rnd_number.$userid.".jpg";
					$avatar_path		= $upload_dir.$new_avatar_name;

					create_thumb($_FILES['avatar']['tmp_name'],$avatar_path, AVATAR_WIDTH, AVATAR_HEIGHT);
				}
			}
		}

		if (count($errs) == 0)
		{
			// delete previous avatar
			DeleteAvatar($userid);

			$upp_query = "UPDATE jobfia_users SET avatar='$new_avatar_name' WHERE user_id='$userid' LIMIT 1";
		
			if (smart_mysql_query($upp_query))
			{
				header("Location: myprofile.php?msg=1");
				exit();
			}	
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i>  ".$errorname."<br/>\n";
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "worker_settings")
	{
		unset($errs);
		$errs = array();
		
		$skills		= array();
		$skills		= $_POST['skills'];

		if (count($skills) == 0)
		{
			$errs[] = "Please select skills";
		}

		if (count($errs) == 0)
		{
			smart_mysql_query("DELETE FROM jobfia_worker_skills WHERE worker_id='$userid'");

			foreach ($skills as $skill_id)
			{
				if (is_numeric($skill_id) && $skill_id > 0)
				{
					$countries_insert = "INSERT INTO jobfia_worker_skills SET worker_id='$userid', skill_id='".(int)$skill_id."'";
					smart_mysql_query($countries_insert);
				}
			}
		
			header("Location: myprofile.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>";
		}	
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Edit Profile";

	require_once ("inc/header.inc.php");

?>

	<section class="clearfix ad-profile-page">
		<div class="container">
			
			<div class="profile job-profile">
				<div class="<?php /*user-pro-section*/ ?>">
						
						<?php /*<div class="hidden-xs" style="margin-left: 10px"><br><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>*/ ?>
					
						<div class="row">	
						<div class="col-md-8">					
					
					<!-- profile-details -->
					<div class="profile-details section">
												
							<div class="pull-right" style="padding-top: 25px">
								<a class="profile" href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $userid; ?>" target="_blank"><i class="fa fa-id-card"></i> view my profile</a><br>
								<?php if (PIN_VERIFICATION == 1) { ?>
									<a href="<?php echo SITE_URL; ?>pin_verification.php" target="_blank"><i class="fa fa-address-card"></i> address verification</a>
								<?php } ?>
							</div>
							
					<h2 class="brd"><i class="fa fa-edit" style="color: #01a650"></i> Edit Profile</h2>
		
				<?php if (isset($_GET['msg']) && is_numeric($_GET['msg'])) { ?>
					<div class="alert alert-success alert-dismissible">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<i class="fa fa-check-circle-o fa-lg"></i> 
						<?php
							switch ($_GET['msg'])
							{
								case "1": echo "Your profile has been updated successfully"; break;
								case "2": echo "Password has been changed successfully"; break;
							}
						?>
					</div>
				<?php } ?>
		
				<?php if (isset($allerrors)) { ?>
					<div class="alert alert-danger"><?php echo $allerrors; ?></div>
				<?php } ?>
		
				<?php if (PIN_VERIFICATION == 1 && $row['pin_verified'] == 0) { ?>
					<div class="alert alert-info alert-dismissible">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<b><i class="fa fa-map-marker"></i> Address Verification</b><br>
						Your address does not verified. We have sent you the pin code, so you can verify your accout by entering pin code <a href="<?php echo SITE_URL; ?>pin_verification.php">here</a>.
					</div>
				<?php } ?>						
						
						<br>
						<form action="myprofile.php" method="post">
				            <div class="row form-group">
								<label class="col-sm-3 label-title">Username<span class="required">*</span></label>
								<div class="col-sm-9"><div class="form-control" disabled><span class="username"><?php echo $row['username']; ?></span></div></div>
							</div>
							<?php if (PREMIUM_ACCOUNTS == 1) { ?>
				            <div class="row form-group">
								<label class="col-sm-3 col-xs-4 label-title">Account</label>
								<div class="col-sm-9 col-xs-8"><?php if ($row['premium'] == 1) { ?><span class="premium"><i class="fa fa-user-circle-o"></i> Premium Account</span><?php }else{ ?><i class="fa fa-user"></i> Basic Account (<a href="<?php echo SITE_URL; ?>upgrade_account.php">upgrade</a>)<?php } ?></div>
							</div>
							<?php } ?>
							<div class="row form-group">
								<label class="col-sm-3 label-title">First Name<span class="required">*</span></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" name="fname" id="fname" value="<?php echo $row['fname']; ?>">
								</div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Last Name<span class="required">*</span></label>
								<div class="col-sm-9"><input type="text" class="form-control" name="lname" id="lname" value="<?php echo $row['lname']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Email Address<span class="required">*</span></label>
								<div class="col-sm-9"><input type="email" class="form-control" name="email" id="email" value="<?php echo $row['email']; ?>"></div>
							</div>							
							<div class="row form-group">
								<label class="col-sm-3 label-title">Company</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="company" id="company" value="<?php echo $row['company']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Address Line 1</label>
								<div class="col-sm-9">
									<input type="text" class="form-control" name="address" id="address" value="<?php echo $row['address']; ?>">
								</div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Address Line 2</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="address2" id="address2" value="<?php echo $row['address2']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">City</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="city" id="city" value="<?php echo $row['city']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">State</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="state" id="state" value="<?php echo $row['state']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Zip Code</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="zip" id="zip" value="<?php echo $row['zip']; ?>"></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 col-xs-4 label-title"><i class="fa fa-globe"></i> Country</label>
								<div class="col-sm-9 col-xs-8"><div class="form-control" style="padding-top: 12px" disabled><?php echo GetCountry($row['country_id']); ?></div></div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Phone</label>
								<div class="col-sm-9"><input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row['phone']; ?>"></div>
							</div>
							<?php if (PIN_VERIFICATION == 1 && $row['pin_verified'] == 1) { ?>
							<div class="row form-group">
								<label class="col-sm-3 label-title">&nbsp;</label>
								<div class="col-sm-9"><span class="label label-success"><i class="fa fa-check-circle-o fa-lg"></i> Address Verified</span></div>
							</div>
							<?php } ?>
							<div class="row form-group">
								<label class="col-sm-3 label-title">Min Job Price <span class="note jobfia_tooltip" title="your min job reward"><i class="fa fa-info-circle"></i></span></label>
								<div class="col-sm-9">
									<div class="input-group">
										<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
										<input type="text" class="form-control" name="rate" id="rate" value="<?php echo ($row['rate'] != "0.0000") ? DisplayMoney($row['rate'], $hide_currency = 1) : ""; ?>" />
									</div>
								</div>
							</div>
							<div class="row form-group">
								<label class="col-sm-3 label-title">About Me</label>
								<div class="col-sm-9">
									<textarea name="about" rows="8" class="form-control"><?php echo strip_tags($row['about']); ?></textarea>	
								</div>
							</div>

							<!--
							<div class="form-group">
								<label>Your City</label>
								<select class="form-control">
									<option value="#">Los Angeles, USA</option>
									<option value="#">Dhaka, BD</option>
									<option value="#">Shanghai</option>
									<option value="#">Karachi</option>
									<option value="#">Beijing</option>
									<option value="#">Lagos</option>
									<option value="#">Delhi</option>
									<option value="#">Tianjin</option>
									<option value="#">Rio de Janeiro</option>
								</select>
							</div>	
							
							<div class="form-group">
								<label>You are a</label>
								<select class="form-control">
									<option value="#">Worker</option>
									<option value="#">Employee</option>
								</select>
							</div>
							-->		
							
							<div class="row">
								<div class="col-sm-3">&nbsp;</div>
								<div class="col-sm-9">
							
									<!-- email-notifications -->
									<div class="preferences-settings section" style="padding: 0">
										<h3><i class="fa fa-bell-o" style="color: #01a650"></i> Email Notifications</h3>
										<!-- checkbox -->
										<div class="checkbox"> 
											<label class="<?php echo (@$row['newsletter'] == 1) ? "checked" : "" ?>"><input type="checkbox" name="newsletter" value="1" <?php echo (@$row['newsletter'] == 1) ? "checked" : "" ?>/> I wish to receive newsletter</label>
											<label class="<?php echo (@$row['notification1'] == 1) ? "checked" : "" ?>"><input type="checkbox" name="notification1" value="1" <?php echo (@$row['notification1'] == 1) ? "checked" : "" ?>/> Notify me for new job by email</label>
											<label class="<?php echo (@$row['notification2'] == 1) ? "checked" : "" ?>"><input type="checkbox" name="notification2" value="1" <?php echo (@$row['notification2'] == 1) ? "checked" : "" ?>/> Notify me for new job invitation</label>
											<label class="<?php echo (@$row['notification3'] == 1) ? "checked" : "" ?>"><input type="checkbox" name="notification3" value="1" <?php echo (@$row['notification3'] == 1) ? "checked" : "" ?>/> Notify me for task completed</label>
											<label class="<?php echo (@$row['notification4'] == 1) ? "checked" : "" ?>"><input type="checkbox" name="notification4" value="1" <?php echo (@$row['notification4'] == 1) ? "checked" : "" ?>/> Notify me for new order by email</label>						
											
										</div>
										<div class="buttons">
											<hr>
											<input type="hidden" name="action" value="editprofile" />
											<button type="submit" name="Update" class="btn btn-success btn-lg">Save Changes</button>
											<a href="#" onclick="history.go(-1);return false;" class="btn cancle btn-lg">Cancel</a>
										</div>												
									</div><!-- email-notifications -->
							
								</div>
							</div><!-- row -->							
							
																		
						</form>	
					</div><!-- profile-details -->
					
					
						</div>
						<div class="col-md-4">
						
							<div class="section">
								<h3 class="brd"><i class="fa fa-image" style="color: #01a650"></i> Avatar</h3>
								<form action="myprofile.php" method="post" enctype="multipart/form-data">
									<div class="row">
										<div class="col-sm-3 text-center">
											<center><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="<?php echo 55; //AVATAR_WIDTH; ?>" height="<?php echo 55; //AVATAR_HEIGHT; ?>" style="border-radius: 20%"></center>
										</div>
										<div class="col-sm-9">
											<input type="file" class="form-control" name="avatar" style="margin: 5px 0" />
											<input type="hidden" name="action" value="change_avatar" />
											<button type="submit" class="btn btn-success" name="Change" id="Change">Change avatar</button>
										</div>
									</div>
						        </form>
							</div>
							
							
							<!-- change-password -->
							<div class="change-password section" style="padding-top: 0">
								<h3 class="brd"><i class="fa fa-lock" style="color: #01a650"></i> Change password</h3>
								<form action="myprofile.php" method="post">
								<div class="form-group">
									<label>Current Password</label>
									<input type="password" class="form-control" name="password" id="password" value="">
								</div>						
								<div class="form-group">
									<label>New password</label>
									<input type="password" class="form-control" name="newpassword" id="newpassword" value="">
								</div>						
								<div class="form-group">
									<label>Confirm password</label>
									<input type="password" class="form-control" name="newpassword2" id="newpassword2" value="">
								</div>
								<div class="buttons">
									<input type="hidden" name="action" value="changepwd" />
									<button type="submit" name="Update" class="btn btn-success">Change Password</button>
								</div>									
								</form>															
							</div><!-- change-password -->
							

							<div class="section" style="padding-top: 5px">		
							<h3 class="brd"><i class="fa fa-user" style="color: #01a650"></i> Worker Skills</h3>
							<form action="myprofile.php" method="post">
					          <table align="center" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<td align="right" valign="middle">&nbsp;</td>
									<td align="left" valign="top" style="background: #fcfcfc; border-radius: 5px">
									<?php
					
										unset($worker_skills);
										$worker_skills = array();
					
										$sql_worker_skills = smart_mysql_query("SELECT skill_id FROM jobfia_worker_skills WHERE worker_id='$userid'");		
										
										while ($row_worker_skills = mysqli_fetch_array($sql_worker_skills))
										{
											$worker_skills[] = $row_worker_skills['skill_id'];
										}
					
										$sql_skills = "SELECT * FROM jobfia_categories WHERE parent_id='0' ORDER BY category_id";
										$rs_skills = smart_mysql_query($sql_skills);
										$total_skills = mysqli_num_rows($rs_skills);
					
										$num = ceil($total_skills/2);
					
										$cc = 0;
					
										if ($total_skills > 0)
										{
											while ($row_skills = mysqli_fetch_array($rs_skills))
											{
												if ($cc == 0 || $cc%$num == 0) echo '<div style="width: 12em; float: left; padding: 5px 10px;">';
					
												$cc++;
					
												if (is_array($skills) && in_array($row_skills['category_id'], $skills)) $checked = 'checked'; else $checked = '';
												if (is_array($worker_skills) && in_array($row_skills['category_id'], $worker_skills)) $checked = 'checked'; else $checked = '';
					
												echo "<div class='checkbox' style='margin: 0'><label class='".$checked."'><input type='checkbox' name='skills[]' class='checkboxx' value='".$row_skills['category_id']."' ".$checked."> ".$row_skills['name']."</label></div> ";
					
												if ($cc%$num == 0) echo "</div>";
											}
										}
					
									?>			
									</td>
								</tr>
								<tr>
									<td colspan="2" align="left" valign="middle">
										<input type="hidden" name="action" value="worker_settings" />
										<input type="submit" class="btn btn-success" name="Update" id="Update" value="Save Changes" />
									</td>
								</tr>
					          </table>
							</form>
							</div>
									
													
						</div>
					</div>

				</div>
			</div>				
		</div>
	</section>


<?php require_once ("inc/footer.inc.php"); ?>