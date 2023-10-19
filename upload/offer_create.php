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

	$positions		= MIN_POSITIONS;
	$duration		= JOB_TIME_DEFAULT;

	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		unset($errs);
		$errs = array();

		$title			= mysqli_real_escape_string($conn, getPostParameter('ititle'));
		$category		= (int)getPostParameter('category');
		$country_all	= (int)getPostParameter('country_all');
		$country		= (int)getPostParameter('country');
		$description	= mysqli_real_escape_string($conn, nl2br(getPostParameter('description')));
		$instructions	= mysqli_real_escape_string($conn, nl2br(getPostParameter('instructions')));
		$duration		= (int)getPostParameter('duration');
		$duration_v		= mysqli_real_escape_string($conn, getPostParameter('duration_v'));
		$positions		= (int)getPostParameter('positions');
		$price			= mysqli_real_escape_string($conn, getPostParameter('price'));
		//$few_times		= (int)getPostParameter('few_times');
		$featured		= (int)getPostParameter('featured');

		if (!($title && $description && $price))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if (strlen($title) < 3)
			{
				$errs[] = "Too short offer title";
			}

			if (!$_FILES['logo_file']['tmp_name'])
			{
				$errs[] = "Select offer image";
			}			

			if (isset($duration) && !(is_numeric($duration) && $duration > 0))
			{
				$errs[] = "Please enter correct delivery time";
				$duration = JOB_TIME_DEFAULT;
			}
			else
			{
				switch($duration_v)
				{
					case "minute":	$duration = $duration; break;
					case "hour":	$duration = $duration*60; break;
					case "day":		$duration = $duration*1440; break;
				}
			}

			if (!(is_numeric($price) && $price > 0))
			{
				$errs[] = "Wrong offer price";
			}
		}

		if (!$category)
		{
			$errs[] = "Please select category";
		}

		if (count($country) == 0)
		{
			$errs[] = "Please select country";
		}
		

			if (count($errs) == 0)
			{
				if ($_FILES['logo_file']['tmp_name'])
				{
					if (is_uploaded_file($_FILES['logo_file']['tmp_name']))
					{
						list($width, $height, $type) = getimagesize($_FILES['logo_file']['tmp_name']);

						$check = getimagesize($_FILES["logo_file"]["tmp_name"]);
						if ($check === false) $errs[] = "File is not an image";
	
						if ($_FILES['logo_file']['size'] > 2097152)
						{
							$errs[] = "The image file size is too big. It exceeds 2Mb";
						}
						elseif (preg_match('/\\.(gif|jpg|png|jpeg)$/i', $_FILES['logo_file']['name']) != 1)
						{
							$errs[] = "Please upload PNG, JPG, GIF file only";
							//$errs[] = "Please upload ".strtoupper(str_replace("|", ", .", PROOF_ALLOWED_FILES))." file only"; //PROOF_ALLOWED_FILES = dd|ddd
							unlink($_FILES['logo_file']['tmp_name']);
						}
						else
						{
							$ext				= substr(strrchr($_FILES['logo_file']['name'], "."), 1);
							$upload_file_name	= "offer".mt_rand(1,100).time().".".$ext; //md5(substr($ip, 0, -5).mt_rand(1,10000).time()).".".$ext;
							
							$img				= $upload_file_name;
							$upload_path		= PUBLIC_HTML_PATH.'/images/offers/'.$upload_file_name;
							$resized_path 		= $upload_path; //PUBLIC_HTML_PATH.'/images/currencies/'.$upload_file_name_resized;
							
							// upload file
							move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_path);

							$imgData 			= resize_image($resized_path, 300, 300);
							imagepng($imgData, $upload_path);
						}
					}
				}
				else
				{
					$img = "no_image.png";
				}
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= "<i class='fa fa-times'></i> ".$errorname."<br/>";
			}		
		

		if (count($errs) == 0)
		{
			if (AUTO_APPROVE_JOBS == 1) $status = "active"; else $status = "pending";
			$query = "INSERT INTO jobfia_jobs SET job_type='offer', title='$title', image='$img', user_id='$userid', duration='$duration', positions='0', daily_limit='0', price='$price', description='$description', instructions='$instructions', worldwide='0', few_times='$few_times', premium='$premium', featured='$featured', status='$status', added=NOW()"; //country=''
			$result = smart_mysql_query($query);
			$new_job_id = mysqli_insert_id($conn);
			
			smart_mysql_query("INSERT INTO jobfia_job_to_category SET job_id='$new_job_id', category_id='$category'");

			// send notification
			if (NEW_JOB_ALERT == 1)
			{
				$subject = "New offer submitted";
				$message = "New offer submitted";

				SendEmail(SITE_MAIL, $subject, $message, $noreply_mail = 1);
			}
			
			header("Location: myoffers.php?msg=added");
			exit();
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Post an Offer";

	require_once ("inc/header.inc.php");
	require_once ("inc/usermenu.inc.php");

?>


<section class=" job-bg ad-details-page">
		<div class="container">

			<div class="job-postdetails">
				<div class="row">	
					<div class="col-md-9">					
						
						<form action="" method="post" enctype="multipart/form-data">
							<fieldset>
								<div class="section postdetails">
									<h4>Post Your Offer<span class="pull-right">* Required fields</span></h4>
								
									<?php if (isset($errormsg) && $errormsg != "") { ?>
										<div class="alert alert-danger"><?php echo $errormsg; ?></div>
									<?php } ?>										
									
									<div class="row form-group">
										<label class="col-sm-3 label-title">Offer Title<span class="required">*</span></label>
										<div class="col-sm-9">
											<div class="row">
											<div class="col-sm-1" style="padding-top: 12px; white-space: nowrap"><label>I will</label></div>
											<div class="col-sm-7"><input type="text" name="ititle" class="form-control" placeholder="ex, I will make a video for you" value="<?php echo getPostParameter('ititle'); ?>" required="required"></div>
											<div class="col-sm-1" style="padding-top: 12px; white-space: nowrap"><label>for</label></div>
											<div class="col-sm-3" style="white-space: nowrap">									
													<div class="input-group"> 
														<span class="input-group-addon"><b><?php echo SITE_CURRENCY; ?></b></span>
														<input type="text" name="price" value="<?php echo getPostParameter('price'); ?>" size="4" placeholder="5.00" class="form-control" required />
											    	</div>	
											</div>										
											</div>
										</div>
									</div>		
									<div class="row form-group">
										<label class="col-sm-3 label-title">Delivery Time<span class="required">*</span></label>
										<div class="col-sm-9">
											<div class="row">
												<div class="col-sm-4 hidden-xs" style="padding-top: 12px"><label>I will deliver the work in</label></div>									<div class="col-sm-4 col-xs-6 pull-left"><input type="text" name="duration" id="duration" size="4" value="<?php echo (isset($_POST['duration'])) ? getPostParameter('duration') : JOB_TIME_DEFAULT; ?>" class="form-control" /></div>
												<div class="col-sm-4 col-xs-6 pull-right">
													<select name="duration_v" class="form-control">
														<option value="day" <?php if (@$duration_v == "day") echo "selected='selected'"; ?>>days</option>
														<option value="hour" <?php if (@$duration_v == "hour") echo "selected='selected'"; ?>>hours</option>
														<option value="minute" <?php if (@$duration_v == "minute") echo "selected='selected'"; ?>>minutes</option>
													</select>
												</div>
											</div>
										</div>
									</div>
									<div class="row form-group add-title">
										<label class="col-sm-3 label-title">Image<span class="required">*</span></label>
										<div class="col-sm-9">
											<input type="file" class="form-control" name="logo_file" /><!-- few images //dev -->
										</div>
									</div>							
									<div class="row form-group add-title">
										<label class="col-sm-3 label-title">Offer Category<span class="required">*</span></label>
										<div class="col-sm-9">
											<select name="category" id="category" class="form-control" onchange="getprice(this.value)" required="required">
												<option value="">-- Please select category --</option>
												<?php ShowCategoriesDropDown(0); ?>
											</select>											
										</div>
									</div>				
									<div class="row form-group item-description">
										<label class="col-sm-3 label-title">Description<span class="required">*</span></label>
										<div class="col-sm-9">
											<textarea class="form-control" name="description" id="description" placeholder="Write few lines about your offer" rows="8" required><?php echo getPostParameter('description'); ?></textarea>		
										</div>
									</div>
									<!--<div class="row characters">
										<div class="col-sm-9 col-sm-offset-3">
											<p>5000 characters left</p>
										</div>
									</div>-->
									<div class="row form-group item-proof">
										<label class="col-sm-3 label-title">Instructions for client<span class="required">*</span></label>
										<div class="col-sm-9">
											<textarea class="form-control" name="instructions" id="instructions" placeholder="Some instructions for users" rows="8" required><?php echo getPostParameter('instructions'); ?></textarea>
										</div>
									</div>
									
									
									<div class="row form-group add-title location">
										<label class="col-sm-3 label-title">Country</label>
										<div class="col-sm-9">
											<div class="dropdown category-dropdown pull-left">
												<a data-toggle="dropdown" href="#" aria-expanded="false"><span class="change-text">- Select Country -</span> <i class="fa fa-angle-down pull-right"></i></a>
												<ul class="dropdown-menu category-change" name="country">
													<?php
															$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
															$rs_country = smart_mysql_query($sql_country);
															$total_country = mysqli_num_rows($rs_country);
										
															if ($total_country > 0)
															{
																while ($row_country = mysqli_fetch_array($rs_country))
																{
																	$cc++;
																	if (!$_POST['action'] || @$country_all == 1 || (is_array($country) && in_array($row_country['country_id'], $country))) $checked = 'checked="checked"'; else $checked = '';
										
																	if (($cc%2) == 0)
																		echo "<li value=\"".(int)$row_country['country_id']."\" ".$checked."><a href='#'>".$row_country['name']."</a></li>";
																	else
																		echo "<li value=\"".(int)$row_country['country_id']."\" ".$checked."><a href='#'>".$row_country['name']."</a></li>";
																}
															}
													?>
												</ul>								
											</div>
											<!--
											<div class="pull-right">
												<input type="text" name="city" id="city" value="<?php echo getPostParameter('city'); ?>" class="form-control" placeholder="City" />								
											</div>											
											
											<div class="dropdown category-dropdown pull-right">
												<a data-toggle="dropdown" href="#" aria-expanded="false"><span class="change-text">State</span> <i class="fa fa-angle-down pull-right"></i></a>
												<ul class="dropdown-menu category-change">
													<li><a href="#">State 1</a></li>
													<li><a href="#">State 2</a></li>
													<li><a href="#">State 3</a></li>
												</ul>								
											</div>
											-->
										</div>
									</div>							


							<div class="row form-group">
								<div class="col-sm-3">&nbsp;</div>
								<div class="col-sm-9">
									<!--
									<div class="checkbox">
									<label>
										<input type="checkbox" name="featured" id="featured" value="1" <?php echo (!empty($featured)) ? "checked" : "" ?> />
		<img src="<?php echo SITE_URL; ?>images/icon_featured.png" align="absmiddle" /> Featured Offer (<?php echo DisplayMoney(FEATURED_JOB_FEE); ?>)<br/>
									</label>-->
											
									<input type="hidden" name="action" value="add" />
									<button type="submit" class="btn btn-primary">Post Your Offer</button>					
								</div>
								</div>
									
								
								</div><!-- section -->
								
							</fieldset>
						</form><!-- form -->	
					</div>


					<!-- right-box -->	
					<div class="col-md-3">
						<div class="section quick-rules">
							<h4>INFORMATION</h4>
	
								<?php if (AUTO_APPROVE_JOBS != 1 ) { ?>
									<p>Every offer will be reviewed by our team before it is approved.</p>
									<p>Please ensure you read the acceptable and not acceptable types of offers list before you submit to avoid your offer being denied.</p>
								<?php } ?>	
							
							<?php /* ?><?php $content = GetContent('rules'); ?> <p><?php echo $content['text']; ?></p><?php */ ?>
						</div>
					</div><!-- right-box -->	
				</div><!-- photos-ad -->				
			</div>	
		</div><!-- container -->
	</section><!-- main -->
	

<?php require_once ("inc/footer.inc.php"); ?>