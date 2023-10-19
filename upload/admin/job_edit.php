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


	$pn = (int)$_GET['pn'];


	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
			unset($errors);
			$errors = array();

			$job_id			= (int)getPostParameter('jobid');
			$job_title		= mysqli_real_escape_string($conn, getPostParameter('job_title'));
			$category		= array();
			$category		= $_POST['category_id'];
			$country_all	= (int)getPostParameter('country_all');
			$country		= array();
			$country		= $_POST['country_id'];
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
			$tags			= mysqli_real_escape_string($conn, getPostParameter('tags'));
			$few_times		= (int)getPostParameter('few_times');
			$premium		= (int)getPostParameter('premium');
			$featured		= (int)getPostParameter('featured');
			$highlighted	= (int)getPostParameter('highlighted');
			$homepage		= (int)getPostParameter('homepage');
			$block_reason	= mysqli_real_escape_string($conn, nl2br(getPostParameter('reason')));
			$status			= mysqli_real_escape_string($conn, getPostParameter('status'));

			if (!($job_title && $positions && $price && $description && $proof && $status))
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

				if (strlen($job_title) < 3)
				{
					$errors[] = "Too short job title";
				}

				if (isset($duration) && !(is_numeric($duration) && $duration > 0))
				{
					$errors[] = "Please enter correct time to complete job";
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
					$errors[] = "Please enter correct job price";
				}
			}

			if (count($errors) == 0)
			{
				smart_mysql_query("UPDATE jobfia_jobs SET title='$job_title', price='$price', positions='$positions', daily_limit='$daily_limit', duration='$duration', daily_limit='$daily_limit', description='$description', proof='$proof', is_proof_file='$is_proof_file', auto_rate='$auto_rate', vcode='$vcode', tags='$tags', worldwide='$country_all', few_times='$few_times', premium='$premium', featured='$featured', highlighted='$highlighted', homepage='$homepage', status='$status', reason='$block_reason', updated=NOW() WHERE job_id='$job_id' LIMIT 1");

				if (count($category) > 0)
				{
					smart_mysql_query("DELETE FROM jobfia_job_to_category WHERE job_id='$job_id'");
					foreach ($category as $cat_id)
					{
						$cats_insert_sql = "INSERT INTO jobfia_job_to_category SET job_id='$job_id', category_id='".(int)$cat_id."'";
						smart_mysql_query($cats_insert_sql);
					}
				}

				smart_mysql_query("DELETE FROM jobfia_job_to_country WHERE job_id='$job_id'");

				if ($country_all == 1)
				{
					$countries_insert = "INSERT INTO jobfia_job_to_country SET job_id='$job_id', country_id='111111'";
					smart_mysql_query($countries_insert);
				}
				else
				{	
					foreach ($country as $country_id)
					{
						if (is_numeric($country_id) && $country_id > 0)
						{
							$countries_insert = "INSERT INTO jobfia_job_to_country SET job_id='$job_id', country_id='".(int)$country_id."'";
							smart_mysql_query($countries_insert);
						}
					}
				}

				header("Location: jobs.php?msg=updated");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errors as $errorname)
					$errormsg .= "<i class='fa fa-times'></i> ".$errorname."<br/>";
			}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id	= (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_jobs WHERE job_id='$id' LIMIT 1";
		$rs	= smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}


	$title = "Edit Job";
	require_once ("inc/header.inc.php");

?>


    <h2><i class="fa fa-briefcase" style="color: #5cb85c"></i> Edit Job</h2>

	<?php if ($total > 0) {
		
		$row = mysqli_fetch_array($rs);

	?>
		<script>
		<!--
			function hiddenDiv(id,showid){
				if(document.getElementById(id).value == "declined"){
					document.getElementById(showid).style.display = ""
				}else{
					document.getElementById(showid).style.display = "none"
				}
			}
		-->
		</script>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } ?>

	<div style="background: #F9F9F9; padding: 10px 0;">
      <form action="" method="post" name="form1">
        <table width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="150" valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="job_title" id="job_title" value="<?php echo $row['title']; ?>" size="75" class="form-control" /></td>
          </tr>
		  <?php if (GetCategoriesTotal() > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Category:</td>
            <td valign="top">
				<div class="scrollbox">
				<?php

					unset($job_cats);
					$job_cats = array();

					$sql_job_cats = smart_mysql_query("SELECT category_id FROM jobfia_job_to_category WHERE job_id='$id'");		
					
					while ($row_job_cats = mysqli_fetch_array($sql_job_cats))
					{
						$job_cats[] = $row_job_cats['category_id'];
					}

					$allcategories = array();
					$allcategories = CategoriesList(0);
					foreach ($allcategories as $category_id => $category_name)
					{
						$cc++;
						if (is_array($job_cats) && in_array($category_id, $job_cats)) $checked = 'checked="checked"'; else $checked = '';

						if (($cc%2) == 0)
							echo "<div class=\"even\"><input name=\"category_id[]\" value=\"".(int)$category_id."\" ".$checked." type=\"checkbox\">".$category_name."</div>";
						else
							echo "<div class=\"odd\"><input name=\"category_id[]\" value=\"".(int)$category_id."\" ".$checked." type=\"checkbox\">".$category_name."</div>";
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
				<?php

					unset($job_countries);
					$job_countries = array();

					$sql_job_countries = smart_mysql_query("SELECT country_id FROM jobfia_job_to_country WHERE job_id='$id'");		
					while ($row_job_countries = mysqli_fetch_array($sql_job_countries))
					{
						$job_countries[] = $row_job_countries['country_id'];
					}

					$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

				?>
					<div class="odd"><input type="checkbox" name="country_all" value="1" <?php if ((is_array($country) && $country[0] == "") || count($job_countries) == 0 || in_array("111111", $job_countries)) echo 'checked="checked"'; ?> onclick="select_counties(this)" /><i class="fa fa-globe fa-lg" style="color: #338dc5"></i> <b>International (All Workers)</b></div>
				<?php

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							$cc++;
							if (in_array("111111", $job_countries) || (is_array($job_countries) && in_array($row_country['country_id'], $job_countries))) $checked = 'checked="checked"'; else $checked = '';

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
				<td valign="middle" align="left" class="tb1">Duration:</td>
				<td valign="top">
					<?php
						$minutes = $row['duration'];

						$d = floor ($minutes / 1440);
						$h = floor (($minutes - $d * 1440) / 60);
						$m = $minutes - ($d * 1440) - ($h * 60);

						if ($d > 0) { $job_duration = $d; $duration_sel3 = "selected='selected'"; }
						if ($h > 0) { $job_duration = $h; $duration_sel2 = "selected='selected'"; }
						if ($m > 0) { $job_duration = $m; $duration_sel1 = "selected='selected'"; }

					?>
					<input type="text" name="duration" id="duration" value="<?php echo $job_duration; ?>" size="5" class="form-control" />
					<select name="duration_v" class="form-control">
						<option value="minute" <?php echo @$duration_sel1; ?>>minutes</option>
						<option value="hour" <?php echo @$duration_sel2; ?>>hours</option>
						<option value="day" <?php echo @$duration_sel3; ?>>days</option>
					</select>
					<span class="note">time required to complete this job</span>
				</td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Positions:</td>
				<td valign="middle"><input type="text" name="positions" id="positions" value="<?php echo (int)$row['positions']; ?>" size="5" class="form-control" /><span class="note">tasks needed</span></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Daily Limit:</td>
				<td valign="middle"><input type="text" name="daily_limit" id="daily_limit" value="<?php echo (int)$row['daily_limit']; ?>" size="5" class="form-control" /><span class="note">0 = no limit</span></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Payment:<br><small>(per task)</small></td>
				<td valign="middle">
					<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>	
						<input type="text" name="price" id="price" value="<?php echo $row['price']; ?>" size="6" style="width: 150px" class="form-control" />
					</div>
				</td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Description:</td>
				<td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['description']); ?></textarea><br/><span class="note">Optional tag: {WORKER_ID} will be replaced with worker ID</span></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Required Proof:</td>
				<td valign="top"><textarea name="proof" id="editor2" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['proof']); ?></textarea></td>
            </tr>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor1' );
					CKEDITOR.replace( 'editor2' );
				</script>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="is_proof_file" value="1" <?php if ($row['is_proof_file'] == 1) echo "checked=\"checked\""; ?> />&nbsp; Proof file required <span class="note jobfia_tooltip" title="worker must to upload proof file (screenshot, etc)"></span></td>
            </tr>
			<?php if (PROOF_VCODE == 1) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Auto Rate Tasks:</td>
				<td valign="top">
					<select name="auto_rate" id="auto_rate" class="form-control" onchange="hiddenDiv('auto_rate')">
						<option value="1" <?php if ($row['auto_rate'] == 1) echo "selected='selected'"; ?>>Do not verify or rate</option>
						<option value="2" <?php if ($row['auto_rate'] == 2) echo "selected='selected'"; ?>>Verify only</option>
						<option value="3" <?php if ($row['auto_rate'] == 3) echo "selected='selected'"; ?>>Verify + Rate satisfied</option>
					</select>
				</td>
            </tr>
			<tr id="vcode" <?php if ($row['auto_rate'] == 1 || @$auto_rate == 1) { ?>style="display: none;" <?php } ?>>
				<td valign="middle" align="left" class="tb1">Proof Verification Code:</td>
				<td valign="top"><input type="text" name="vcode" class="form-control" value="<?php echo $row['vcode']; ?>" size="40" /> <span class="note">will be used for auto verify the proof and/or rate a task as satisfied</span></td>
            </tr>
			<?php } ?>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="few_times" value="1"  <?php if ($row['few_times'] == 1) echo "checked=\"checked\""; ?> />&nbsp; allow worker complete job few times</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="premium" value="1" <?php if ($row['premium'] == 1) echo "checked=\"checked\""; ?> />&nbsp; only premium members can accept job</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="featured" value="1" <?php if ($row['featured'] == 1) echo "checked=\"checked\""; ?> />&nbsp; featured job</td>
            </tr>
			<!--
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="highlighted" value="1" <?php if ($row['highlighted'] == 1) echo "checked=\"checked\""; ?> />&nbsp; highlighted job</td>
            </tr>
			-->
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="homepage" value="1" <?php if ($row['homepage'] == 1) echo "checked=\"checked\""; ?> />&nbsp; homepage job</td>
            </tr>
			<?php if (!($row['status'] == "pending" && $row['reviewed'] == "0000-00-00 00:00:00")) { ?>
            <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" id="status" class="form-control" onchange="update_status();">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="pending" <?php if ($row['status'] == "pending") echo "selected"; ?>>awaiting review</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
					<option value="declined" <?php if ($row['status'] == "declined") echo "selected"; ?>>declined</option>
					<option value="complete" <?php if ($row['status'] == "complete") echo "selected"; ?>>complete</option>
				</select>
			</td>
            </tr>
			<?php }else{ ?>
				<input type="hidden" name="status" value="pending" />
			<?php } ?>
              <tr id="block_reason" <?php if ($row['status'] != "declined") echo 'style="display: none;"'; ?>>
                <td valign="middle" align="left" class="tb1">Reason:<br/><small>(optional)</small></td>
                <td valign="top"><textarea cols="55" rows="3" name="reason" class="form-control"><?php echo strip_tags($row['reason']); ?></textarea></td>
              </tr>
            <tr>
              <td>&nbsp;</td>
              <td align="left" valign="top">
				<input type="hidden" name="jobid" id="jobid" value="<?php echo (int)$row['job_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit">
				<input type="submit" class="btn btn-success" name="update" id="update" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='jobs.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
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
			if(nstatus == 'declined'){
				$("#block_reason").show();
			}else{
				$("#block_reason").hide();
			}
		}
		</script>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no job found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>