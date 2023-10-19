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
		$results_per_page = 20;


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pn			= (int)$_GET['pn'];
		$job_id		= (int)$_GET['id'];

		$jquery = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_added FROM jobfia_jobs WHERE job_id='$job_id' LIMIT 1";
		$jresult = smart_mysql_query($jquery);
		$jtotal = mysqli_num_rows($jresult);
		$jrow = mysqli_fetch_array($jresult);
	}

	if (isset($_POST['action']) && $_POST['action'] == "modify")
	{
		$ids_arr	= array();
		$ids_arr	= $_POST['id_arr'];

		if (count($ids_arr) > 0)
		{
			foreach ($ids_arr as $v)
			{
				$task_id = (int)$v;
				
				switch($_POST['mark_as'])
				{
					case "confirm":		ConfirmTask($task_id); break;
					case "decline":		DeclineTask($task_id); break;
					case "incomplete":	NotCompleteTask($task_id); break;
				}
			}

			header("Location: applicants.php?msg=updated&id=".$job_id);
			exit();
		}	
	}

	////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "worker_id": $rrorder = "worker_id"; break;
				case "proof": $rrorder = "job_proof"; break;
				case "added": $rrorder = "added"; break;
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

		if (isset($_GET['filter']) && $_GET['filter'] != "")
		{
			$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
			$filter_by = " AND (job_proof LIKE '%$filter%') ";
		}
	//////////////////////////////////////////////////

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;


	if (isset($_GET['worker_id']) && is_numeric($_GET['worker_id']))
	{
		$worker_id = (int)$_GET['worker_id'];
		$where = " AND worker_id='$worker_id'";
		$atitle = GetUsername($worker_id, 1);
	}


	if (isset($_GET['job_id']) && is_numeric($_GET['job_id']))
	{
		$job_id = (int)$_GET['job_id'];
		$where = " AND job_id='$job_id'";
	}

	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y <sup>%h:%i %p</sup>') AS date_complete FROM jobfia_job_tasks WHERE 1=1 $where $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
	$total_result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE 1=1 $where $filter_by ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);

	$title = "Job Applicants";
	require_once ("inc/header.inc.php");

?>
    
     <h2><i class="fa fa-check-circle-o" style="color: #5cb85c"></i> <?php echo $atitle; ?> Job Applicants  <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?> <?php if (@$filter) { ?> / Search results<?php } ?></h2>

		<?php if ($total > 0 && !$worker_id) { ?>
		<div style="width: 150px; position: absolute; right: 5px; top: 10px;">
		<table width="100%" cellpadding="2" cellspacing="3" border="0" align="right">
		<tr height="20">
			<td width="33%" bgcolor="#F4C869" align="center" style="border-right: 1px solid #FFF; border-radius: 7px"><span style="font-size: 18px; color: #FFF;" class="jobfia_tooltip" title="awaiting review"><?php echo GetJobPendingTasks($job_id); ?></span></td>
			<td width="33%" bgcolor="#92D811" align="center" style="border-right: 1px solid #FFF; border-radius: 7px"><span style="font-size: 18px; color: #FFF;" class="jobfia_tooltip" title="satisfied"><?php echo GetJobPaidTasks($job_id); ?></span></td>
			<td width="33%" bgcolor="#F9AA98" align="center" style="border-radius: 7px"><span style="font-size: 18px; color: #FFF;" class="jobfia_tooltip" title="not satisfied"><?php echo GetJobDeclinedTasks($job_id); ?></span></td>
		</tr>
		</table>
		</div>
		<?php } ?>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
				switch ($_GET['msg'])
				{
					case "updated": echo "Tasks have been successfully updated"; break;
					case "deleted": echo "Tasks have been successfully deleted"; break;
				}
			?>
		</div>
		<?php } ?>

		<form action="" id="form1" name="form1" method="get">
		<table style="background: #fcfcfc" width="100%" border="0" cellpadding="3" cellspacing="0" align="center">
		<tr>
			<td height="30" nowrap="nowrap" valign="middle" align="left" width="45%">
				Sort by: 
				<select name="column" id="column" onChange="document.form1.submit()">
					<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Date</option>
					<option value="worker_id" <?php if ($_GET['column'] == "worker_id") echo "selected"; ?>>Worker Name</option>
					<option value="proof" <?php if ($_GET['column'] == "proof") echo "selected"; ?>>Proof</option>
					<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
				</select>
				<select name="order" id="order" onChange="document.form1.submit()">
					<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
					<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
				</select>
				<input type="hidden" name="id" value="<?php echo $job_id; ?>" />
				<input type="hidden" name="page" value="<?php echo $page; ?>" />
				</select>
			  &nbsp;&nbsp;View: 
			  <select name="show" id="show" onChange="document.form1.submit()">
				<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
				<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
				<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
				<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
			  </select>
			</td>
			<td nowrap="nowrap" width="30%" valign="middle" align="left">
				<div class="admin_filter">
					<select name="job_id" class="form-control" style="width: 170px;" id="job_id" onChange="document.form1.submit()">
						<option value="">-- all jobs --</option>
						<?php
							$sql_jobs = "SELECT * FROM jobfia_job_tasks ORDER BY job_title";
							$rs_jobs = smart_mysql_query($sql_jobs);
							$total_jobs = mysqli_num_rows($rs_jobs);

							if ($total_jobs > 0)
							{
								while ($row_jobs = mysqli_fetch_array($rs_jobs))
								{
									if ($job_id == $row_jobs['job_id'])
										echo "<option value='".$row_jobs['job_id']."' selected>".$row_jobs['job_title']." (ID: ".$row_jobs['job_id'].")</option>\n";
									else
										echo "<option value='".$row_jobs['job_id']."'>".$row_jobs['job_title']." (ID: ".$row_jobs['job_id'].")</option>\n";
								}
							}
						?>
					</select>
					<?php if (isset($job_id) && $job_id != "") { ?><a title="Cancel Filter" href="applicants.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Filter" /></a><?php } ?>
					<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="30" />
					<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
					<?php if (isset($filter) && $filter != "") { ?><a title="Cancel Search" href="applicants.php?id=<?php echo $job_id; ?>"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?> 
				</div>
			</td>
			<td nowrap="nowrap" valign="middle" width="20%" align="right">
				<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</td>
		</tr>
		</table>
		</form>

            <form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%" style="padding-left: 7px;"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="5%">&nbsp;</th>
				<th width="33%">Job Title</th>
				<th width="18%"><i class="fa fa-user-circle-o"></i> Worker</th>
				<th width="22%">Proof submitted</th>
				<th width="8%">Proof File</th>
				<th width="15%">Date/Time</th>
                <th width="10%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
                  <td align="center" valign="middle" style="padding-left: 7px;">
					<input type="checkbox" class="checkbox" <?php if ($row['status'] != "pending") echo "disabled=\"disabled\""; ?> name="id_arr[<?php echo $row['job_task_id']; ?>]" id="id_arr[<?php echo $row['job_task_id']; ?>]" value="<?php echo $row['job_task_id']; ?>" />
                  </td>
                  <td nowrap="nowrap" align="center" valign="middle"><i class="fa fa-check-circle fa-2x" style="color: #d2ddd2"></i></td>
				  <td valign="middle" align="left"><a href="task_details.php?job_id=<?php echo $row['job_id']; ?>&id=<?php echo $row['job_task_id']; ?>"><?php echo $row["job_title"]; ?></a></td>
                  <td valign="middle" align="left"><?php echo GetUsername($row['worker_id'], 1); ?></td>
                  <td valign="middle" align="left">
					<?php
							if (strlen($row['job_proof']) > 125)
							{
								$job_proof = substr($row["job_proof"], 0, 120);
								$job_proof .= "... <a href='task_details.php?job_id=".$row['job_id']."&id=".$row['job_task_id']."><img src='images/icon_more.png' align='absmiddle' /></a>";
							}
							else
							{
								$job_proof = $row["job_proof"];
							}

							if (@$filter) echo highlight_word($job_proof, $filter, "#EF9013"); else echo $job_proof;
					?>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="center"><?php if ($row['proof_file'] != "") { ?><a href="<?php echo substr(SITE_URL, 0, -1).JOBFIA_PROOFS_URL.$row['proof_file']; ?>" class="jobfia_tooltip" title="<?php echo $row['proof_downloads']; ?> downloads"><img src="images/icons/file.png" alt="<?php echo $row['proof_downloads']; ?> downloads" /></a><?php }else{ ?>---<?php } ?></td>
				  <td nowrap="nowrap" valign="middle" align="center"><?php echo $row['date_complete']; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 10px;">
					<?php
							switch ($row['status'])
							{
								case "notcomplete": echo "<span class='label label-default'>".$row['status']."</span>"; break;
								case "pending": echo "<span class='label label-warning'>".$row['status']."</span>"; break;							
								case "declined": echo "<span class='label label-danger'>".$row['status']."</span>"; break;
								case "confirmed": echo "<span class='label label-success'>".$row['status']."</span>"; break;
								default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
							}

							if ($row['comment'] != "")
							{
								echo "<span class=\"jobfia_tooltip\" title=\"".$row['comment']."\"><img src=\"images/icon_scomment.png\" align=\"absmiddle\" /></span>";
							}
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="center">
					<a href="task_details.php?job_id=<?php echo $row['job_id']; ?>&id=<?php echo $row['job_task_id']; ?>"><img src="images/view.png" alt="Details" title="Details" /></a>
					<a href="#" onclick="if (confirm('Are you sure you really want to delete this applicant?') )location.href='task_delete.php?job_id=<?php echo $row['job_id']; ?>&id=<?php echo $row['job_task_id']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
				  </td>
                </tr>
			<?php } ?>
				<tr>
					<td style="border-top: solid 1px #F7F7F7;" bgcolor="#F9F9F9" colspan="9" align="left">
						<b>Mark selected as:</b> 
						<select name="mark_as">
							<option value="">--------------</option>
							<option value="confirm" style="background:#92D811;color:#FFF;">Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">Not Complete</option>
							<option value="decline" style="background:#F9AA98;color:#FFF;">Not Satisfied</option>
						</select>
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="modify" />
						<input type="submit" class="btn btn-success" name="MassModify" id="MassModify" value="Go" />
					</td>
				</tr>
				<tr>
					<td colspan="9" align="center">
						 <?php 
							 
							$params = "";
							if ($worker_id)		$params .= "worker_id=$worker_id&";							 
							 
							 echo ShowPagination("job_tasks",$results_per_page,"applicants.php?".$params."id=$job_id&column=$rrorder&order=$rorder&show=$results_per_page&","WHERE 1=1 $where ".$filter_by);
							 
						  ?>
					</td>
				</tr>

	<?php }else{ ?>
			<?php if (isset($filter)) { ?>
				<tr><td colspan="9"><div class="alert alert-info text-center">No applicants found. <a href="applicants.php?id=<?php echo $job_id; ?>">Search again &#155;</a></div></td></tr>
			<?php }else{ ?>
				<tr><td colspan="9"><div class="alert alert-info text-center">No submitted applicants at this time.</div></td></tr>
			<?php } ?>
	<?php } ?>

           </table>
		   </form>

	<?php if (isset($job_id)) { ?>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;" /></p>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>