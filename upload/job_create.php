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
		$country		= array();
		$country		= $_POST['country_id'];
		$description	= mysqli_real_escape_string($conn, nl2br(getPostParameter('description')));
		$proof			= mysqli_real_escape_string($conn, nl2br(getPostParameter('proof')));
		$is_proof_file	= (int)getPostParameter('is_proof_file');
		$auto_rate		= (int)getPostParameter('auto_rate');
		$vcode			= mysqli_real_escape_string($conn, getPostParameter('vcode'));
		$duration		= (int)getPostParameter('duration');
		$duration_v		= mysqli_real_escape_string($conn, getPostParameter('duration_v'));
		$positions		= (int)getPostParameter('positions');
		$daily_limit	= (int)getPostParameter('daily_limit');
		$price			= mysqli_real_escape_string($conn, getPostParameter('price'));
		$invite			= (int)getPostParameter('invite');
		$few_times		= (int)getPostParameter('few_times');
		$premium		= (int)getPostParameter('premium');
		$featured		= (int)getPostParameter('featured');

		if (!($title && $description && $proof && $positions && $price))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if (strlen($title) < 3)
			{
				$errs[] = "Too short job title";
			}

			if (!(is_numeric($positions) && $positions >= MIN_POSITIONS))
			{
				$errs[] = "Sorry, minimum <span style='color: #565656'>".MIN_POSITIONS."</span> positions";
				$positions = MIN_POSITIONS;
			}

			if ($country_all != 1 && JOB_COUNTRIES_LIMIT > 0 && count($country) > JOB_COUNTRIES_LIMIT)
			{
				$errs[] = "Please select up to ".JOB_COUNTRIES_LIMIT." countries";
			}

			if (isset($duration) && !(is_numeric($duration) && $duration > 0))
			{
				$errs[] = "Please enter correct time to complete job";
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
				$errs[] = "Wrong job price";
			}
			else
			{
				// calculate job cost //
				$job_price = 0;

				if (strstr(JOB_FEE, '%'))
					$job_price = ($positions*$price) + CalculatePercentage(($positions*$price), JOB_FEE) + JOB_APPROVAL_FEE;
				else
					$job_price = ($positions*($price+JOB_FEE)) + JOB_APPROVAL_FEE;
			
				if ($featured == 1) $job_price += FEATURED_JOB_FEE;
				////////////////////////

				// checks job price and compare it with category min price
				$min_price = GetCategoryPrice($category);
				if ($price < $min_price)
				{
					$errs[] = "Please increase job price. Minimum price to post job in this category: <span style='color: #000'>".DisplayMoney($min_price)."</span> ";
					$price = $min_price;
				}
			}

			if (GetUserBalance($userid, $hide_currency_sign = 1) < $job_price)
			{
				$errs[] = "Job price: <span style='color: #565656'><b>".DisplayMoney($job_price)."</b></span>. There is not enough money in your account to submit job. Please <a href='".SITE_URL."deposit.php?amount=".$job_price."' target='_blank'>make a deposit</a>.";
			}
			elseif (!MoneyEnough($userid))
			{
				$errs[] = "Job price: <span style='color: #565656'><b>".DisplayMoney($job_price)."</b></span>. There is not enough money in your account to submit job. Please <a href='".SITE_URL."deposit.php?amount=".$job_price."' target='_blank'>make a deposit</a>.";
			}
		}

		if (!$category)
		{
			$errs[] = "Please select category";
		}

		if (count($country) == 0)
		{
			$errs[] = "Please select countries";
		}

		if (!$price)
		{
			$errs[] = "Please enter price per task";
		}

		if (count($errs) == 0 && isset($_POST['confirm']))
		{
			if (AUTO_APPROVE_JOBS == 1) $status = "active"; else $status = "pending";
			$query = "INSERT INTO jobfia_jobs SET job_type='job', title='$title', user_id='$userid', duration='$duration', positions='$positions', daily_limit='$daily_limit', price='$price', description='$description', proof='$proof', is_proof_file='$is_proof_file', auto_rate='$auto_rate', vcode='$vcode', job_price='$job_price', worldwide='$country_all', few_times='$few_times', premium='$premium', featured='$featured', status='$status', added=NOW()";
			$result = smart_mysql_query($query);
			$new_job_id = mysqli_insert_id($conn);

			if ($country_all == 1)
			{
				$countries_insert = "INSERT INTO jobfia_job_to_country SET job_id='$new_job_id', country_id='111111'";
				smart_mysql_query($countries_insert);
			}
			else
			{
				foreach ($country as $country_id)
				{
					if (is_numeric($country_id) && $country_id > 0)
					{
						$countries_insert = "INSERT INTO jobfia_job_to_country SET job_id='$new_job_id', country_id='".(int)$country_id."'";
						smart_mysql_query($countries_insert);
					}
				}
			}
			
			smart_mysql_query("INSERT INTO jobfia_job_to_category SET job_id='$new_job_id', category_id='$category'");

			// save workers invitations
			if ($invite == 1)
			{
				$my_workers_result = smart_mysql_query("SELECT jobfia_my_workers.*, jobfia_users.* FROM jobfia_my_workers jobfia_my_workers, jobfia_users jobfia_users WHERE jobfia_my_workers.employer_id='$userid' AND jobfia_my_workers.worker_id=jobfia_users.user_id AND jobfia_users.status='active' ORDER BY jobfia_my_workers.added DESC");

				$my_workers_total = mysqli_num_rows($my_workers_result);

				if ($my_workers_total > 0)
				{
					while ($my_workers_row = mysqli_fetch_array($my_workers_result))
					{
						smart_mysql_query("INSERT INTO jobfia_worker_invites SET job_id='$new_job_id', employer_id='".(int)$my_workers_row['employer_id']."', worker_id='".(int)$my_workers_row['worker_id']."', status='pending', invited=NOW()");
					}
				}
			}

			// send notification
			if (NEW_JOB_ALERT == 1)
			{
				$subject = "New job submitted";
				$message = "New job submitted";

				SendEmail(SITE_MAIL, $subject, $message, $noreply_mail = 1);
			}
			
			header("Location: myjobs.php?msg=added");
			exit();
		}
		else
		{
			foreach ($errs as $errorname)
			{
				$errormsg .= "&#155; ".$errorname."<br/>\n";
			}
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Create a Job";

	require_once ("inc/header.inc.php");
	require_once ("inc/usermenu.inc.php");

?>


<section class="<?php if (MoneyEnough($userid)) echo "job-bg ad-details-page"; ?>">
		<div class="container">
			<div class="breadcrumb-section">
				<!--<ol class="breadcrumb" style="color: #000">
					<li><a href="<?php echo SITE_URL; ?>" style="color: #000">Home</a></li>
					<li style="color: #000">New Job</li>
				</ol>-->					
				<h2 class="title" style="color: #333">Create a Job</h2>
			</div><!-- banner -->

				<?php if (!MoneyEnough($userid)) { ?>
					<div class="alert alert-warning text-center">
						<h4><i class="fa fa-info-circle fa-lg"></i> There is not enough money in your account to submit job. Please <a href="<?php echo SITE_URL; ?>deposit.php">make a deposit</a>.</h4>
					</div>
				<?php } ?>	

			<div class="job-postdetails">
				<div class="row">	
					<div class="col-md-8 <?php if (!MoneyEnough($userid)) echo "disabledbox"; ?>">
						
						<form action="#job_cost" method="post">
							<fieldset>
								<div class="section postdetails">
									<h4>Post Your Job<span class="pull-right">* Required fields</span></h4>
									

								
									<?php if (isset($errormsg) && $errormsg != "") { ?>
										<div class="alert alert-danger"><?php echo $errormsg; ?></div>
									<?php } ?>									
									
									<div class="row form-group">
										<label class="col-sm-3 label-title">Job Title<span class="required">*</span></label>
										<div class="col-sm-9">
											<input type="text" name="ititle" class="form-control" placeholder="ex, Like Instagram Post" value="<?php echo getPostParameter('ititle'); ?>" required="required">
										</div>
									</div>										
									<div class="row form-group add-title">
										<label class="col-sm-3 label-title">Job Category<span class="required">*</span></label>
										<div class="col-sm-9">
											<!--<div class="dropdown category-dropdown">
												<a data-toggle="dropdown" href="#" aria-expanded="false"><span class="change-text">Select a category</span> <i class="fa fa-angle-down pull-right"></i></a>
												<ul class="dropdown-menu category-change">
													<li><a href="#">Select a category</a></li>
													<li><a href="#">Software Engineer</a></li>
													<li><a href="#">Program Development</a></li>
													<li><a href="#">Project Manager</a></li>
													<li><a href="#">Graphics Designer</a></li>
												</ul>								
											</div>-->
											<select name="category" id="category" class="form-control" onchange="getprice(this.value)" required="required">
												<option value="">-- Please select category --</option>
												<?php ShowCategoriesDropDown(0); ?>
											</select>											
										</div>
									</div>				
									<div class="row form-group item-description">
										<label class="col-sm-3 label-title">Description<span class="required">*</span></label>
										<div class="col-sm-9">
											<textarea class="form-control" name="description" id="description" placeholder="Write few lines about your job" rows="8" required><?php echo getPostParameter('description'); ?></textarea>
											<span class="note">Optional tag: {WORKER_ID} will be replaced with worker ID<br/></span>		
										</div>
									</div>
									<!--<div class="row characters">
										<div class="col-sm-9 col-sm-offset-3">
											<p>5000 characters left</p>
										</div>
									</div>-->
									<div class="row form-group item-proof">
										<label class="col-sm-3 label-title">Required Proof<span class="required">*</span></label>
										<div class="col-sm-9">
											<textarea class="form-control" name="proof" id="proof" placeholder="" rows="4" required><?php echo getPostParameter('proof'); ?></textarea>
											<div class="checkbox"><label><input type="checkbox" name="is_proof_file" value="1" <?php echo (getPostParameter('is_proof_file') == 1) ? "checked" : ""; ?> /><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i> Proof file required <span class="jobfia_tooltip" title="worker must upload proof file (screenshot, etc)"><i class="fa fa-info-circle" style="color: #CCC"></i></span></label></div>
										</div>
									</div>


	<?php if (PROOF_VCODE == 1) { ?>
	<div class="row form-group">
		<label class="col-sm-3 label-title">Auto Rate Tasks</label>
		<div class="col-sm-9">
			<select name="auto_rate" id="auto_rate" class="form-control" onchange="hiddenDiv('auto_rate')">
				<option value="1" <?php if (@$auto_rate == 1) echo "selected='selected'"; ?>>Do not verify or rate</option>
				<option value="2" <?php if (@$auto_rate == 2) echo "selected='selected'"; ?>>Verify only</option>
				<option value="3" <?php if (@$auto_rate == 3) echo "selected='selected'"; ?>>Verify + Rate satisfied</option>
			</select>		
		</div>
	</div>
	<div class="row form-group" id="vcode" <?php if (!$auto_rate || @$auto_rate == 1) { ?>style="display: none;" <?php } ?>>
		<label class="col-sm-3 label-title">Verification Code<span class="required">*</span></label>
		<div class="col-sm-9">
			<input type="text" name="vcode" class="form-control" value="<?php echo getPostParameter('vcode'); ?>" size="43" placeholder="enter verification code" />
			<span class="note"><i class="fa fa-info-circle"></i> will be used for auto verify the proof and/or rate a task as satisfied</span>
		</div>
	</div>
	<?php } ?>
	
	
	<div class="row form-group">
		<label class="col-sm-3 label-title">Target Workers</label>
		<div class="col-sm-9">
		<div class="scrollbox">
			<div class="even"><input type="checkbox" name="country_all" id="country_all" value="1" <?php if (!$_POST['action'] || @$country_all == 1) echo "checked='checked'"; ?> onclick="select_counties(this)" /><i class="fa fa-globe fa-lg" style="color: #338dc5"></i> <b>International (All Workers)</b></div>
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
								echo "<div class=\"even\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked."><img src=\"".SITE_URL."images/flags/".strtolower($row_country['code']).".png\" /> ".$row_country['name']."</div>";
							else
								echo "<div class=\"odd\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked."><img src=\"".SITE_URL."images/flags/".strtolower($row_country['code']).".png\" /> ".$row_country['name']."</div>";
						}
					}
			?>
				</div>
		</div>
	</div>
    <script type="text/javascript">
	function select_counties() {
        if($("input[name='country_all']:checked").length)
			$('input:checkbox[name="country_id\[\]"]').attr('checked', true);
		else
			$('input:checkbox[name="country_id\[\]"]').attr('checked', false);
	}
	function hiddenDiv(id){
		if(document.getElementById(id).value != "1")
			document.getElementById("vcode").style.display = "";
		else
			document.getElementById("vcode").style.display = "none";
	}
    </script>									
									<!--
									<div class="row form-group add-title location">
										<label class="col-sm-3 label-title">Location<span class="required">*</span></label>
										<div class="col-sm-9">
											<div class="dropdown category-dropdown pull-left">
												<a data-toggle="dropdown" href="#" aria-expanded="false"><span class="change-text">Country</span> <i class="fa fa-angle-down pull-right"></i></a>
												<ul class="dropdown-menu category-change">
													<li><a href="#">Argentina</a></li>
													<li><a href="#">Australia</a></li>
													<li><a href="#">Belgium</a></li>
													<li><a href="#">Brazil</a></li>
													<li><a href="#">Cambodia</a></li>
												</ul>								
											</div>
											<div class="dropdown category-dropdown pull-right">
												<a data-toggle="dropdown" href="#" aria-expanded="false"><span class="change-text">State</span> <i class="fa fa-angle-down pull-right"></i></a>
												<ul class="dropdown-menu category-change">
													<li><a href="#">State 1</a></li>
													<li><a href="#">State 2</a></li>
													<li><a href="#">State 3</a></li>
												</ul>								
											</div>
										</div>
									</div>
									-->										
								</div><!-- postdetails -->
								
								
							<div class="section checkbox">
								<h4>Job Posting Options</h4>
								<?php
							
									$my_workers_total = mysqli_num_rows(smart_mysql_query("SELECT jobfia_my_workers.*, jobfia_users.* FROM jobfia_my_workers jobfia_my_workers, jobfia_users jobfia_users WHERE jobfia_my_workers.employer_id='$userid' AND jobfia_my_workers.worker_id=jobfia_users.user_id AND jobfia_users.status='active' ORDER BY jobfia_my_workers.added DESC"));
							
									if ($my_workers_total > 0) { 
								?>
									<label>
										<input type="checkbox" name="invite" id="invite" value="1" <?php echo (!empty($invite)) ? "checked" : "" ?> />
										<img src="<?php echo SITE_URL; ?>images/users.png" align="absmiddle" /> Send invititations to my workers<br/>
										<small style="color: #aaa">Your favorite <a href="<?php echo SITE_URL; ?>myworkers.php" target="_blank"><span class="badge"><?php echo $my_workers_total; ?></span></a> workers will receive invitation to do this job.</small>
									</label>
								<?php } ?>
							    
							    <label>
									<input type="checkbox" name="few_times" value="1" <?php echo (getPostParameter('few_times') == 1) ? "checked" : ""; ?> /> <i class="fa fa-refresh"></i> Worker can complete task few times
								</label>
								<br>
								
								<?php if (PREMIUM_ACCOUNTS == 1) { ?>
							    <label>
									<input type="checkbox" name="premium" id="premium" value="1" <?php echo (!empty($premium)) ? "checked" : "" ?> />
									<img src="<?php echo SITE_URL; ?>images/user_premium.png" align="absmiddle" /> Premium Workers Only<br/>
									<small style="color: #aaa">Only preminum workers can accept your job.</small>
								</label>
								<?php } ?>
								
							    <label>
									<input type="checkbox" name="featured" id="featured" value="1" <?php echo (!empty($featured)) ? "checked" : "" ?> />
									<img src="<?php echo SITE_URL; ?>images/icon_featured.png" align="absmiddle" /> Featured Job (<?php echo DisplayMoney(FEATURED_JOB_FEE); ?>)<br/>
									<small style="color: #aaa">Your job will be displayed on EVERY worker page. Job will also appear in the featured jobs list.</small>
								</label>								
									
							</div><!-- section -->


<div class="section">
	
	<div class="row" style="background: #fcfcfc; padding: 10px;">
	<div class="col-md-8">
	
		<div class="row form-group">
			<label class="col-sm-6 label-title"><i class="fa fa-clock-o fa-lg"></i> Time to Complete:</label>
			<div class="col-sm-3"><input type="text" name="duration" id="duration" size="4" value="<?php echo (isset($_POST['duration'])) ? getPostParameter('duration') : JOB_TIME_DEFAULT; ?>" class="form-control" /></div>
			<div class="col-sm-3">
				<select name="duration_v" class="form-control">
					<option value="minute" <?php if (@$duration_v == "minute") echo "selected='selected'"; ?>>minutes</option>
					<option value="hour" <?php if (@$duration_v == "hour") echo "selected='selected'"; ?>>hours</option>
					<option value="day" <?php if (@$duration_v == "day") echo "selected='selected'"; ?>>days</option>
				</select>
			</div>
		</div>
		<div class="row form-group">
			<label class="col-sm-6 label-title"><i class="fa fa-users fa-lg"></i> Available Job Seats:</label>
			<div class="col-sm-6">
				<input type="number" name="positions" id="positions" size="4" min="0" step="1" data-number-to-fixed="2" data-number-stepfactor="100" value="<?php echo @(int)$positions; ?>" maxlength="5" class="form-control" required="required" /> <span class="note jobfia_tooltip" title="">minimum <?php echo MIN_POSITIONS; ?> positions</span>
			</div>
		</div>
		<div class="row form-group">
			<label class="col-sm-6 label-title"><i class="fa fa-tachometer fa-lg"></i> Daily Limit:</label>
			<div class="col-sm-6">
				<input type="number" name="daily_limit" id="daily_limit" size="4" min="0" step="1" data-number-to-fixed="2" data-number-stepfactor="100" value="<?php echo @(int)$daily_limit; ?>" maxlength="5" class="form-control" /> tasks <span class="note">0 = no limit</span>
			</div>
		</div>
		<div class="row form-group">
			<label class="col-sm-6 label-title"><i class="fa fa-money fa-lg"></i> Price per Job <span class="note jobfia_tooltip" title="higher price will attract more workers"> <i class="fa fa-info-circle"></i></span>:</label>
			<div class="col-sm-6">
				<div class="input-group"> 
					<span class="input-group-addon"><b><?php echo SITE_CURRENCY; ?></b></span>
					<input type="text" name="price" value="<?php echo getPostParameter('price'); ?>" size="4" placeholder="5.00" class="form-control" required />
		    	</div>
			</div>
		</div>

			<div class="text-center">
				<?php if (!MoneyEnough($userid)) { ?>
					<button type="submit" class="btn btn-primary btn-lg" disabled>Continue &#155;</button>
				<?php }else{ ?>
					<input type="hidden" name="action" value="add" />
					<button type="submit" class="btn btn-primary btn-lg">Continue &#155;</button>				
				<?php } ?>
			</div>
				
	</div>		
				

	<?php if (isset($job_price) && is_numeric($job_price) && count($errs) == 0) { ?>
	<div class="col-md-4">

	<div id="job_cost" style="background: #01a650; color: #FFF; border-radius: 8px; padding: 10px;">
		<table width="100%" align="center" cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td nowrap="nowrap" valign="top" align="left">Job Price:</td>
				<td nowrap="nowrap" width="30%" valign="top" align="right"><?php echo DisplayMoney($positions*$price); ?></td>
			</tr>
			<?php if (@$featured == 1) { ?>
			<tr>
				<td nowrap="nowrap" valign="top" align="left">Featured Job Fee:</td>
				<td nowrap="nowrap" valign="top" align="right"><?php echo DisplayMoney(FEATURED_JOB_FEE); ?></td>
			</tr>
			<?php } ?>
			<?php if (JOB_APPROVAL_FEE != 0) { ?>
			<tr>
				<td nowrap="nowrap" valign="top" align="left">Job approval fee:</td>
				<td nowrap="nowrap" valign="top" align="right"><?php echo DisplayMoney(JOB_APPROVAL_FEE); ?></td>
			</tr>
			<?php } ?>
			<?php if (JOB_FEE != 0) { ?>
			<tr>
				<td nowrap="nowrap" valign="top" align="left">Site's fee (<?php echo JOB_FEE; ?>): </td>
				<td nowrap="nowrap" valign="top" align="right"><?php echo DisplayMoney(CalculatePercentage(($positions*$price), JOB_FEE)) ; ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td nowrap="nowrap" colspan="2" style="border-top: 2px solid #EAEAEA;" valign="top" align="right">
					<span class="job_price" style="color: #FFF; font-size: 33px"><?php echo DisplayMoney($job_price); ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br>
					<input type="submit" name="confirm" class="btn btn-success btn-lg" value="Confirm & Post Job!" />		
				</td>
			</tr>
		</table>
	</div>
	</div>
	<?php }?>

	</div>		
				
				
				
</div>								

								
							</fieldset>
						</form><!-- form -->	
					</div>
				

					<!-- right-box -->	
					<div class="col-md-4 <?php if (!MoneyEnough($userid)) echo "disabledbox"; ?>">
						<div class="section quick-rules">
							<h4>INFORMATION</h4>

							<div style="background:#F7F7F7; padding:10px; border-radius: 8px; text-align: center">
									<h3 class="text-center">Account Balance</h3>
									<img src="<?php echo SITE_URL; ?>images/icon_money.png">
									<h2 class="text-center" style="color: #01a650"><?php echo GetUserBalance($userid); ?></h2>
									<a href="<?php echo SITE_URL; ?>deposit.php" target="_blank" class="btn btn-info">+ Deposit Money</a>
							</div>							
							<br>
							
							<div class="alert alert-info">
								<?php if (JOB_APPROVAL_FEE != 0) { ?><p>Job approval fee: <b><?php echo DisplayMoney(JOB_APPROVAL_FEE); ?></b>.</p><?php } ?>
								<?php if (JOB_FEE != 0) { ?><p>You will be charged only for jobs you rate Satisfied (<b><?php echo JOB_FEE; ?></b> fee).</p><?php } ?>
							</div>
	
							<?php if (AUTO_APPROVE_JOBS != 1 ) { ?>
								<p>Every job will be reviewed by our team before it is approved.</p>
								<p>Please ensure you read the acceptable and not acceptable types of jobs list before you submit to avoid your job being denied.</p><br>
							<?php } ?>	
							
							<?php $content = GetContent('rules'); ?>
							<p><?php echo $content['text']; ?></p>
							
						</div>
					</div><!-- right-box -->
						
				</div>			
			</div>	
		</div><!-- container -->
	</section><!-- main -->
	

<?php require_once ("inc/footer.inc.php"); ?>