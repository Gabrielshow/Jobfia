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

			if (!($job_title && $duration && $price && $description && $status))
			{
				$errors[] = "Please ensure that all fields marked with an asterisk are complete";
			}
			else
			{
				/*
				if (count($category) == 0)
				{
					$errors[] = "Please select category";
				}

				if (count($country) == 0)
				{
					$errors[] = "Please select countries";
				}*/

				if (strlen($job_title) < 3)
				{
					$errors[] = "Too short offer title";
				}

				if (isset($duration) && !(is_numeric($duration) && $duration > 0))
				{
					$errors[] = "Please enter correct offer delivery time";
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
					$errors[] = "Please enter correct offer price";
				}
			}

			if (count($errors) == 0)
			{
				smart_mysql_query("UPDATE jobfia_jobs SET job_type='offer', title='$job_title', price='$price', positions='$positions', daily_limit='$daily_limit', duration='$duration', daily_limit='$daily_limit', description='$description', proof='$proof', is_proof_file='$is_proof_file', auto_rate='$auto_rate', vcode='$vcode', tags='$tags', worldwide='$country_all', few_times='$few_times', premium='$premium', featured='$featured', highlighted='$highlighted', homepage='$homepage', status='$status', reason='$block_reason', updated=NOW() WHERE job_id='$job_id' LIMIT 1");

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

				if (count($country) > 0)
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

				header("Location: offers.php?msg=updated");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errors as $errorname)
					$errormsg .= "&#155; ".$errorname."<br/>";
			}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id	= (int)$_GET['id'];

		$query = "SELECT * FROM jobfia_jobs WHERE job_id='$id' LIMIT 1";
		$rs	= smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}


	$title = "Edit Offer";
	require_once ("inc/header.inc.php");

?>


    <h2><i class="fa fa-list" style="color: #5cb85c"></i> Edit Offer</h2>

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
		<img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="Image" width="170" height="90" style="position: absolute; right: 20px;">
      <form action="" method="post" name="form1" enctype="multipart/form-data">
        <table width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="150" valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="job_title" id="job_title" value="<?php echo $row['title']; ?>" size="85" class="form-control" placeholder="I will..." /></td>
          </tr>
		  <?php if (GetCategoriesTotal() > 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Category:</td>
            <td valign="top">
				<select name="category" class="form-control">
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
							echo "<option value=\"".(int)$category_id."\" ".$checked." type=\"checkbox\">".$category_name."</option>";
						else
							echo "<option value=\"".(int)$category_id."\" ".$checked." type=\"checkbox\">".$category_name."</option>";
					}

				?>
				</select>
			</td>
			</tr>
			<?php } ?>
			<!-- //dev
          <tr>
            <td valign="middle" align="left" class="tb1">Change Image:</td>
            <td valign="middle"><input type="file" name="logo_file" class="form-control" accept="image/*" /></td>
          </tr>-->	
           <tr>
				<td valign="middle" align="left" class="tb1">Location:</td>
				<td valign="top">
				<select name="country" class="form-control">
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
					<option value="1" <?php if ((is_array($country) && $country[0] == "") || count($job_countries) == 0 || in_array("111111", $job_countries)) echo 'checked="checked"'; ?>>International (All Countries)</option>
				<?php

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							$cc++;
							if (in_array("111111", $job_countries) || (is_array($job_countries) && in_array($row_country['country_id'], $job_countries))) $checked = 'checked="checked"'; else $checked = '';

							if (($cc%2) == 0)
								echo "<option value=\"".(int)$row_country['country_id']."\" ".$checked."> ".$row_country['name']."</option>";
							else
								echo "<option value=\"".(int)$row_country['country_id']."\" ".$checked."> ".$row_country['name']."</option>";
						}
					}

				?>
				</select>				
				</td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-clock-o"></i> Delivery Time:</td>
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
					<span class="note">time to delivery offer</span>
				</td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Items Available:</td>
				<td valign="middle"><input type="text" name="positions" id="positions" value="<?php echo (int)$row['positions']; ?>" size="5" class="form-control" /></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Price:</td>
				<td valign="middle">
					<div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>	
						<input type="text" name="price" id="price" value="<?php echo $row['price']; ?>" size="6" style="width: 150px" class="form-control" />
					</div>
				</td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Description:</td>
				<td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['description']); ?></textarea></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Instructions:</td>
				<td valign="top"><textarea name="proof" id="editor2" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['proof']); ?></textarea></td>
            </tr>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor1' );
					CKEDITOR.replace( 'editor2' );
				</script>
				<!--
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="few_times" value="1"  <?php if ($row['few_times'] == 1) echo "checked=\"checked\""; ?> />&nbsp; allow user purchase offer few times</td>
            </tr>
            -->
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="featured" value="1" <?php if ($row['featured'] == 1) echo "checked=\"checked\""; ?> />&nbsp; featured offer</td>
            </tr>
			<!--
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="highlighted" value="1" <?php if ($row['highlighted'] == 1) echo "checked=\"checked\""; ?> />&nbsp; highlighted offer</td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><input type="checkbox" class="checkbox" name="homepage" value="1" <?php if ($row['homepage'] == 1) echo "checked=\"checked\""; ?> />&nbsp; homepage offer</td>
            </tr>
            -->
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
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='offers.php?page=<?php echo $pn; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>'" />
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
				<div class="alert alert-info">Sorry, no offer found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>