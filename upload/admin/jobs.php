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


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;


		// Delete jobs
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$job_id = (int)$v;
					DeleteJob($job_id);
				}

				header("Location: jobs.php?msg=deleted");
				exit();
			}
		}
		
		$where = " WHERE job_type='job' AND ";

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "title": $rrorder = "title"; break;
					case "user_id": $rrorder = "user_id"; break;
					case "added": $rrorder = "added"; break;
					case "views": $rrorder = "views"; break;
					case "price": $rrorder = "price"; break;
					case "duration": $rrorder = "duration"; break;
					case "positions": $rrorder = "positions"; break;
					case "featured": $rrorder = "featured"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "added"; break;
				}
			}
			else
			{
				$rrorder = "added";
			}

			if (isset($_GET['order']) && $_GET['order'] != "")
			{
				switch ($_GET['order'])
				{
					case "asc": $rorder = "asc"; break;
					case "desc": $rorder = "desc"; break;
					default: $rorder = "desc"; break;
				}
			}
			else
			{
				$rorder = "desc";
			}

			if ($_GET['filter'])
			{
				$filter			= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				//$cat			= mysqli_real_escape_string($conn, getGetParameter('cat'));
				$reward			= mysqli_real_escape_string($conn, getGetParameter('reward'));
				$country		= mysqli_real_escape_string($conn, getGetParameter('country'));
				$sfeatured		= (int)getGetParameter('sfeatured');

				$where			.= " 1=1 AND ";

				if ($filter) $where .= " (title LIKE '%".$filter."%') AND ";

				if (is_numeric($reward) && $reward > 0)
				{
					$reward = (float)$reward;
					$where .= " price >= '$reward' AND ";
				}
				else
				{
					$reward = "";
				}

				if ($country > 0)
				{
					$jobs_per_country = array();
					$sql_jobs_per_country = smart_mysql_query("SELECT job_id FROM jobfia_job_to_country WHERE country_id = '".(int)$country."' OR country_id='111111'");
					$jobs_per_country[] = "111111111111111111111";
					if (mysqli_num_rows($sql_jobs_per_country) > 0)
					{
						while ($row_jobs_per_country = mysqli_fetch_array($sql_jobs_per_country))
						{
							$jobs_per_country[] = $row_jobs_per_country['job_id'];
						}
						$jobs_per_country = array_map('intval', $jobs_per_country);
						$jobs_per_country = array_unique($jobs_per_country);
					}
					$where .= "job_id IN (".implode(",",$jobs_per_country).") AND ";
				}

				if ($sfeatured == 1)	$where .= " featured='1' AND ";
				if ($spremium == 1)		$where .= " premium='1' AND ";


				if (isset($_GET['cat']) && is_numeric($_GET['cat']) && $_GET['cat'] > 0)
				{
					$cat_id = (int)$_GET['cat'];
					
					unset($jobs_per_category);
					$jobs_per_category = array();
					$jobs_per_category[] = "111111111111111111111";

					$sub_categories = array();
					$sub_categories = GetSubCategories($cat_id);
					$sub_categories[] = $cat_id;

					$sql_jobs_per_category = smart_mysql_query("SELECT job_id FROM jobfia_job_to_category WHERE category_id IN (".implode(",",$sub_categories).")"); //category_id='$cat_id'
					if (mysqli_num_rows($sql_jobs_per_category) > 0)
					{
						while ($row_jobs_per_category = mysqli_fetch_array($sql_jobs_per_category))
						{
							$jobs_per_category[] = $row_jobs_per_category['job_id'];
						}
						$jobs_per_category = array_map('intval', $jobs_per_category);
						$jobs_per_category = array_unique($jobs_per_category);
					}
					$where .= "job_id IN (".implode(",",$jobs_per_category).") AND ";
				}

					//$where .= " 1=1 ";
			}
		///////////////////////////////////////////////////////
		
		$where .= " 1=1 ";

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs $where ORDER BY featured DESC, $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_jobs".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		$title = "Jobs";
		require_once ("inc/header.inc.php");

?>

		<div id="addnew">
			<a href="javascript:void(0);" class="search" onclick="$('#admin_filter').toggle('slow');">Search</a>
			<a class="addnew" href="job_add.php">Create a Job</a>
		</div>

		<h2><i class="fa fa-briefcase" style="color: #5cb85c"></i> Jobs <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "added": echo "Job has been successfully added"; break;
						case "approved": echo "Job has been successfully approved"; break;
						case "updated": echo "Job has been successfully updated"; break;
						case "deleted": echo "Job has been successfully deleted"; break;
					}

				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
			<td colspan="2" align="center">
				<div id="admin_filter" style="background: #F7F7F7; width: 100%; height: 50px; padding-top: 10px; <?php if (!(isset($_GET['action']) && $_GET['action'] == "filter")){ ?>display: none;<?php } ?>">
				<!--<b>Search fo jobs</b>: -->
				<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="50" title="Job Title" /> 
				<select name="cat" id="cat" class="form-control" style="width: 172px">
					<option value="">-- all categories --</option>
					<?php ShowCategoriesDropDown (0); ?>
				</select>
				<select name="country" class="form-control" id="country" style="width: 172px">
				<option value="">-- all countries --</option>
				<?php
					$sql_country = "SELECT * FROM jobfia_countries ORDER BY name ASC";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($country == $row_country['country_id'])
								echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
							else
								echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
						}
					}
				?>
				</select>
				<input type="hidden" name="action" value="filter" />
				<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
				<?php if (isset($filter)) { ?><a title="Cancel Search" href="jobs.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?>
				</div>
			</td>
		</tr>
		<tr>
		<td valign="middle" align="left" width="50%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
			<option value="title" <?php if ($_GET['column'] == "title") echo "selected"; ?>>Name</option>
			<option value="user_id" <?php if ($_GET['column'] == "user_id") echo "selected"; ?>>Employer</option>
			<option value="duration" <?php if ($_GET['column'] == "duration") echo "selected"; ?>>Duration</option>
			<option value="positions" <?php if ($_GET['column'] == "positions") echo "selected"; ?>>Positions</option>
			<option value="views" <?php if ($_GET['column'] == "views") echo "selected"; ?>>Popularity</option>
			<option value="price" <?php if ($_GET['column'] == "price") echo "selected"; ?>>Most paying</option>
			<option value="featured" <?php if ($_GET['column'] == "featured") echo "selected"; ?>>Featured</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
			<!-- finished soon -- sql percent //dev -->
          </select>
          <select name="order" id="order" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  &nbsp;&nbsp;View: 
          <select name="show" id="show" onChange="document.form1.submit()">
			<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
				<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
				<?php if ($search) { ?><input type="hidden" name="action" value="search" /><?php } ?>
				<?php if ($stext) { ?><input type="hidden" name="searchtext" value="<?php echo $stext; ?>" /><?php } ?>
				<?php if ($reward > 0) { ?><input type="hidden" name="reward" value="<?php echo $reward; ?>" /><?php } ?>
				<?php if ($spremium == 1) { ?><input type="hidden" name="spremium" value="1" /><?php } ?>
				<?php if ($sfeatured == 1) { ?><input type="hidden" name="sfeatured" value="1" /><?php } ?>
				<?php if ($page > 0) { ?><input type="hidden" name="page" value="<?php echo $page; ?>" /><?php } ?>
			</td>
			<td nowrap="nowrap" valign="middle" width="35%" align="right">
			  <?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="4%">&nbsp;</th>
				<th width="6%">ID</th>
				<th width="30%">Job Title</th>
				<th width="10%"><i class="fa fa-money"></i> Reward</th>
				<th width="15%"><i class="fa fa-user-circle-o"></i> Employer</th>
				<th width="15%">Job Done</th>
				<th width="10%">Tasks Success</th>
				<th width="8%">Date</th>
				<th width="10%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>				  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['job_id']; ?>]" id="id_arr[<?php echo $row['job_id']; ?>]" value="<?php echo $row['job_id']; ?>" /></td>
					<td nowrap="nowrap" align="center" valign="middle"><i class="fa fa-briefcase fa-2x" style="color: #d2ddd2"></i></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['job_id']; ?></td>
					<td align="left" valign="middle">
						<a href="job_details.php?id=<?php echo $row['job_id']; ?>">
							<?php if (strlen($row['title']) > 150) echo substr($row['title'], 0, 150)."..."; else echo $row['title']; ?>
						</a>
						<?php if ($row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured Job"></span><?php } ?>
						<?php if ($row['premium'] == 1) { ?><span class="premium jobfia_tooltip" title="Only for premium workers"><img src="images/icons/user_premium.png" align="absmiddle" width="13" /></span><?php } ?>
						<?php if ($row['homepage'] == 1) { ?><span class="homepage jobfia_tooltip" title="Homepage Job"><img src="images/icons/home.png" align="absmiddle" width="13" /></span><?php } ?>
						<?php if ($row['highlighted'] == 1) { ?><?php } ?>					
					</td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php echo DisplayMoney($row['price']); ?></td>
					<td align="left" valign="middle"><?php echo GetUsername($row['user_id'], 1); ?></td>
					<td nowrap="nowrap" align="center" valign="middle" style="padding: 7px;">
						<a href="job_tasks.php?id=<?php echo $row['job_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>"><?php echo GetJobWorkersTotal($row['job_id']); ?> / <sup><?php echo $row['positions']; ?></sup></a>
						<div class="progress">
							  <div class="progress-bar progress-bar-striped progress-bar-success progress-bar-animated" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>%"></div>
						</div>					
					</td>
					<td nowrap="nowrap" align="center" valign="middle" style="padding: 10px 5px;">
						<span class="acount pending jobfia_tooltip" title="awaiting review"><?php echo GetJobPendingTasks($row['job_id']); ?></span>
						<span class="acount success jobfia_tooltip" title="satisfied"><?php echo GetJobPaidTasks($row['job_id']); ?></span>
						<span class="acount unsuccess jobfia_tooltip" title="declined"><?php echo GetJobDeclinedTasks($row['job_id']); ?></span>
						<?php if (GetJobWorkersTotal($row['job_id']) > 0) { ?>
							<p><small><i class="fa fa-bar-chart"></i> success rate: <?php echo GetJobSuccess($row['job_id']); ?></small></p>
						<?php } ?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['date_added']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px;">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='label label-success'>active</span>"; break;
							case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "complete": echo "<span class='label label-info'>complete</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}

						if ($row['status'] == "declined" && $row['reason'] != "")
						{
							echo "<span class=\"jobfia_tooltip\" title=\"".$row['reason']."\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}

						if ($row['status'] == "inactive" && $row['paused'] != "0000-00-00 00:00:00")
						{
							echo "<span class=\"jobfia_tooltip\" title=\"auto paused by daily tasks limit, will run automatically tomorrow\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}
					?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="job_details.php?id=<?php echo $row['job_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="job_edit.php?id=<?php echo $row['job_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this job?') )location.href='job_delete.php?id=<?php echo $row['job_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td colspan="11" align="left" style="border-top: 1px solid #F7F7F7">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="action" value="delete" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
				</tr>
				<tr>
				  <td colspan="11" align="center">
					<?php echo ShowPagination("jobs",$results_per_page,"jobs.php?column=$rrorder&order=$rorder&show=$results_per_page&",$where); ?>
				  </td>
				</tr>

          <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<tr><td colspan="11"><div class="alert alert-info text-center">No jobs found. <a class="btn btn-info" href="jobs.php"><i class="fa fa-search"></i> Search again</a></div></td></tr>
				<?php }else{ ?>
					<tr><td colspan="11"><div class="alert alert-info text-center">There are no jobs at this time.</div></td></tr>
				<?php } ?>
          <?php } ?>

		    </table>
			</form>

<?php require_once ("inc/footer.inc.php"); ?>