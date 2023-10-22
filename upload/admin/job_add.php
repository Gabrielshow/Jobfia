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


	$url = "http://";


	if (isset($_POST['action']) && $_POST['action'] == "add")
	 {
			unset($errors);
			$errors = array();

			$job_title		= mysqli_real_escape_string($conn, getPostParameter('job_title'));
			$category		= array();
			$category		= $_POST['category_id'];
			$country_all	= (int)getPostParameter('country_all');
			$country		= array();
			$country		= $_POST['country_id'];
			$youtube_video_id = mysqli_real_escape_string($conn, getPostParameter('youtube_video_id'));
			$duration		= (int)getPostParameter('duration');
			$duration_v		= mysqli_real_escape_string($conn, getPostParameter('duration_v'));
			$positions		= (int)getPostParameter('positions');
			$daily_limit	= (int)getPostParameter('daily_limit');
			$price			= mysqli_real_escape_string($conn, getPostParameter('price'));
			$description	= mysqli_real_escape_string($conn, $_POST['description']);
			$proof			= mysqli_real_escape_string($conn, $_POST['proof']);
			$is_proof_file	= (int)getPostParameter('is_proof_file');
			$auto_rate		= (int)getPostParameter('auto_rate');
			$vcode			= mysqli_real_escape_string($conn, getPostParameter('vcode'));
			$few_times		= (int)getPostParameter('few_times');
			$tags			= mysqli_real_escape_string($conn, getPostParameter('tags'));
			$premium		= (int)getPostParameter('premium');
			$featured		= (int)getPostParameter('featured');
			$highlighted	= (int)getPostParameter('highlighted');
			$homepage		= (int)getPostParameter('homepage');
			$send_alert		= (int)getPostParameter('send_alert');

			if (!($job_title && $positions && $price && $description && $proof))
			{
				$errors[] = "Please ensure that all fields marked with an asterisk are complete";
			}
			else
			{
				/*
				if (count($category) == 0)
				{
					$errors[] = "Please select category";
				}*/

				if (count($country) == 0 && $country_all != 1)
				{
					$errors[] = "Please select countries";
				}

				if (isset($duration) && !(is_numeric($duration) && $duration > 0))
				{
					$errs[] = "Please enter correct time to complete job";
					$duration = "3";
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

				if (strlen($job_title) < 3)
				{
					$errors[] = "Too short job title";
				}

				if (!(is_numeric($price) && $price > 0))
				{
					$errors[] = "Please enter correct job price";
				}
			}

			
			if (count($errors) == 0)
			{			
				$insert_sql = "INSERT INTO jobfia_jobs SET title='$job_title', user_id='0', price='$price', positions='$positions', daily_limit='$daily_limit', duration='$duration', description='$description', proof='$proof', is_proof_file='$is_proof_file', auto_rate='$auto_rate', vcode='$vcode', tags='$tags', worldwide='$country_all', few_times='$few_times', premium='$premium', featured='$featured', highlighted='$highlighted', homepage='$homepage', status='active', added=NOW()";
				$result = smart_mysql_query($insert_sql);
				$new_job_id = mysqli_insert_id($conn);

				if (count($category) > 0)
				{
					foreach ($category as $cat_id)
					{
						$cats_insert_sql = "INSERT INTO jobfia_job_to_category SET job_id='$new_job_id', category_id='".(int)$cat_id."'";
						smart_mysql_query($cats_insert_sql);
					}
				}

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

				// send email to members //
				if ($send_alert == 1 && $status == "active")
				{
					SendNewJobNotification($new_job_id);
				}

				header("Location: jobs.php?msg=added");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errors as $errorname)
					$errormsg .= "&#155; ".$errorname."<br/>";
			}
	}

	$cc = 0;

	$title = "Create a Job";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-briefcase" style="color: #5cb85c"></i> Create a Job</h2>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } elseif (isset($_GET['msg']) && ($_GET['msg']) == "added") { ?>
		<div class="alert alert-success">Job has been successfully added</div>
	<?php } ?>

	<div style="background: #F9F9F9; padding: 10px 0;">
      <form action="" method="post" name="form1">
        <table width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="150" valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="job_title" id="job_title" value="<?php echo getPostParameter('job_title'); ?>" size="75" class="form-control" /></td>
          </tr>
		  <?php if (GetCategoriesTotal() > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Category:</td>
            <td valign="top">
				<div class="scrollbox">
				<?php

					$allcategories = array();
					$allcategories = CategoriesList(0);
					foreach ($allcategories as $category_id => $category_name)
					{
						$cc++;
						if (is_array($category) && in_array($category_id, $category)) $checked = 'checked="checked"'; else $checked = '';

						if (($cc%2) == 0)
							echo "<div class=\"even\"><input type=\"checkbox\" name=\"category_id[]\" value=\"".(int)$category_id."\" ".$checked.">".$category_name."</div>";
						else
							echo "<div class=\"odd\"><input type=\"checkbox\" name=\"category_id[]\" value=\"".(int)$category_id."\" ".$checked.">".$category_name."</div>";
					}

				?>
				</div>
			</td>
			</tr>
		<?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Job Targeting:</td>
            <td valign="top">
				<div class="scrollbox">
					<div class="odd"><input type="checkbox" name="country_all" id="country_all" value="1" <?php if (@$country_all == 1) echo 'checked="checked"'; ?> onclick="select_counties(this)" /><i class="fa fa-globe fa-lg" style="color: #338dc5"></i> <b>International (All Workers)</b></div>
				<?php

					$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							$cc++;
							if (is_array($country) && in_array($row_country['country_id'], $country)) $checked = 'checked="checked"'; else $checked = '';

							if (($cc%2) == 0)
								echo "<div class=\"even\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked."><img src=\"".SITE_URL."images/flags/".strtolower($row_country['code']).".png\" /> ".$row_country['name']."</div>";
							else
								echo "<div class=\"odd\"><input type=\"checkbox\" name=\"country_id[]\" value=\"".(int)$row_country['country_id']."\" ".$checked."><img src=\"".SITE_URL."images/flags/".strtolower($row_country['code']).".png\" /> ".$row_country['name']."</div>";
						}
					}
				?>
				</div>
			</td>
			</tr>
			<script type="text/javascript">  
			function select_counties()
			{
				if ($("input[name='country_all']:checked").length)
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
			<tr>
    			<td valign="middle" align="left" class="tb1">YouTube Video ID:</td>
    			<td valign="top"><input type="text" name="youtube_video_id" id="youtube_video_id" size="40" class="form-control" /></td>
				<?php  if ($category_name != 'youtube') echo 'disabled';
				if (!($youtube_video_id && strlen($youtube_video_id) == 11 && ctype_alnum($youtube_video_id)))
				{
   			 		$errors[] = "Please enter a valid YouTube Video ID";
				}?>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Duration:</td>
				<td valign="top">
					<input type="text" name="duration" id="duration" value="<?php echo getPostParameter('duration'); ?>" size="5" class="form-control" />
					<select name="duration_v" class="form-control">
						<option value="minute" <?php if (@$duration_v == "minute") echo "selected='selected'"; ?>>minutes</option>
						<option value="hour" <?php if (@$duration_v == "hour") echo "selected='selected'"; ?>>hours</option>
						<option value="day" <?php if (@$duration_v == "day") echo "selected='selected'"; ?>>days</option>
					</select>
					<span class="note">time required to complete this job</span>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Positions:</td>
				<td valign="middle"><input type="text" name="positions" id="positions" value="<?php echo getPostParameter('positions'); ?>" size="5" class="form-control" /><span class="note">tasks needed</span></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Daily Limit:</td>
				<td valign="middle"><input type="text" name="daily_limit" id="daily_limit" value="<?php echo (isset($_POST['daily_limit'])) ? getPostParameter('daily_limit') : "0"; ?>" size="5" class="form-control" /><span class="note">0 = no limit</span></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Payment:<br><small>(per task)</small></td>
				<td valign="middle">
					<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>	
						<input type="text" name="price" id="price" value="<?php echo getPostParameter('price'); ?>" size="6"  style="width: 150px" class="form-control" />
					</div>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Description:</td>
				<td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo getPostParameter('description'); ?></textarea><br/><span class="note">Optional tag: {WORKER_ID} will be replaced with worker ID</span></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Required Proof:</td>
				<td valign="top"><textarea name="proof" id="editor2" cols="75" rows="8" class="form-control"><?php echo getPostParameter('proof'); ?></textarea></td>
            </tr>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor1' );
					CKEDITOR.replace( 'editor2' );
				</script>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="is_proof_file" value="1" <?php if (getPostParameter('is_proof_file') == 1) echo "checked=\"checked\""; ?> />&nbsp; Proof file required <span class="note jobfia_tooltip" title="worker must to upload proof file (screenshot, etc)"></span></td>
            </tr>
			<?php if (PROOF_VCODE == 1) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Auto Rate Tasks:</td>
				<td valign="top">
					<select name="auto_rate" id="auto_rate" class="form-control" onchange="hiddenDiv('auto_rate')">
						<option value="1" <?php if (@$auto_rate == 1) echo "selected='selected'"; ?>>Do not verify or rate</option>
						<option value="2" <?php if (@$auto_rate == 2) echo "selected='selected'"; ?>>Verify only</option>
						<option value="3" <?php if (@$auto_rate == 3) echo "selected='selected'"; ?>>Verify + Rate satisfied</option>
					</select>
				</td>
            </tr>
			<tr id="vcode" <?php if (!$auto_rate || @$auto_rate == 1) { ?>style="display: none;" <?php } ?>>
				<td valign="middle" align="left" class="tb1">Proof Verification Code:</td>
				<td valign="top"><input type="text" name="vcode" class="form-control" value="<?php echo getPostParameter('vcode'); ?>" size="40" /> <span class="note">will be used for auto verify the proof and/or rate a task as satisfied</span></td>
            </tr>
			<?php } ?>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="few_times" value="1" <?php if (getPostParameter('few_times') == 1) echo "checked=\"checked\""; ?> />&nbsp; allow worker complete job few times</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="premium" value="1" <?php if (getPostParameter('premium') == 1) echo "checked=\"checked\""; ?> />&nbsp; only premium members can accept job</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="send_alert" value="1" <?php if ($send_alert != 0) echo "checked=\"checked\""; ?> />&nbsp; email job details to members</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="featured" value="1" <?php if (getPostParameter('featured') == 1) echo "checked=\"checked\""; ?> />&nbsp; featured job</td>
            </tr>
			<!--
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="highlighted" value="1" <?php if (getPostParameter('highlighted') == 1) echo "checked=\"checked\""; ?> />&nbsp; highlighted job</td>
            </tr>
			-->
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="homepage" value="1" <?php if (getPostParameter('homepage') == 1) echo "checked=\"checked\""; ?> />&nbsp; homepage job</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td valign="middle">
					<select name="status" class="form-control" >
						<option value="active" <?php if ($status == "active") echo "selected"; ?>>active</option>
						<option value="inactive" <?php if ($status == "inactive") echo "selected"; ?>>inactive</option>
					</select>
				</td>
            </tr>
            <tr>
				<td>&nbsp;</td>
				<td align="left" valign="top">
					<input type="hidden" name="action" id="action" value="add">
					<input type="submit" class="btn btn-success" name="add" id="add" value="Add Job" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='jobs.php'" />
				</td>
            </tr>
          </table>
      </form>
	 </div>

<?php require_once ("inc/footer.inc.php"); ?>