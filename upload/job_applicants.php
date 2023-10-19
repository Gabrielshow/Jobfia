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
	require_once("inc/pagination.inc.php");


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;

	$cc = 0;


	if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
	{
		$job_id = (int)$_REQUEST['id'];
		
		// check if viewing job poster //
		$job_result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS date_added FROM jobfia_jobs WHERE job_id='$job_id' AND user_id='$userid' LIMIT 1");
		if (mysqli_num_rows($job_result) == 0)
		{
			header ("Location: index.php");
			exit();
		}
		else
		{
			$job_row = mysqli_fetch_array($job_result);
			$job_id = $job_row['job_id'];
		}
		//////////////////////////////////
	}
	else
	{		
		header ("Location: index.php");
		exit();
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

			header("Location: job_applicants.php?msg=updated&id=".$job_id);
			exit();
		}	
	}

	////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "worker_id": $rrorder = "worker_id"; break;
				case "added": $rrorder = "added"; break;
				case "proof": $rrorder = "job_proof";
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

	$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y %h:%i %p') AS date_complete FROM jobfia_job_tasks WHERE job_id='$job_id' $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
	
	$total_result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE job_id='$job_id' $filter_by ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Job Applicants";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	    <ol class="breadcrumb breadcrumb-arrow">
			<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
			<li><a href="<?php echo SITE_URL; ?>myjobs.php">My Jobs</a></li>
			<li class="active"><span>Job Applicants</span></li>
		</ol>
		
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<div style="text-align: right"><a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $job_id; ?>" target="_blank">view job details <i class="fa fa-external-link"></i></a></div>

		<!--<h1><?php echo $job_row['title']; ?></h1>-->
		

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success alert-dismissible fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<i class="fa fa-check-circle-o fa-lg"></i> 
			<?php
				switch ($_GET['msg'])
				{
					case "updated": echo "Tasks have been successfully updated"; break;
				}
			?>
		</div>
		<?php } ?>

		<?php if (isset($allerrors)) { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>


		<?php if ($total > 0) { ?>
		<table width="100%" cellpadding="3" cellspacing="4" border="0" align="center" style="border: 1px solid #eff9f4">
		<tr>
			<?php if ($job_row['duration'] != "") { ?><td width="20%" bgcolor="#fcfcfc" style="border-right: 1px dotted #deede6" align="center"><span class="num"><?php echo ConvertDuration($job_row['duration']); ?></span> <i class="fa fa-clock-o fa-lg"></i> Time to Complete</td><?php } ?>
			<td height="80" width="20%" bgcolor="#fcfcfc" style="border-right: 1px dotted #deede6" align="center"><span class="num" style="color: #5cb85c"><?php echo DisplayMoney($job_row['price']); ?></span> <i class="fa fa-money fa-lg"></i> Payment per Task</td>
			<td width="20%" bgcolor="#fcfcfc" style="border-right: 1px dotted #deede6" align="center"><span class="num"><?php echo $job_row['positions']; ?></span> <i class="fa fa-users fa-lg"></i> Tasks Needed <?php if ($job_row['daily_limit'] > 0) { ?><br/><span class="badge">Daily Limit: <b><?php echo $job_row['daily_limit']; ?></b> tasks</span><?php } ?></td>
			<td width="20%" bgcolor="#fcfcfc" style="border-right: 1px dotted #deede6" align="center"><span class="num"><?php echo GetJobAvailablePositions($job_row['job_id']); ?></span> <i class="fa fa-users fa-lg" style="color: #64c428"></i> Available Positions</td>
			<td width="20%" bgcolor="#fcfcfc" style="padding: 7px;" align="center"><Br> Job Done<span class="num"><?php echo GetJobWorkersTotal($job_row['job_id']); ?>/<sup><?php echo $job_row['positions']; ?></sup></span>
				<div class="progress" style="height: 15px">
					<div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($job_row['job_id'])/$job_row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($job_row['job_id'])/$job_row['positions'])*100); ?>%"></div>
				</div>
				<?php if (GetJobWorkersTotal($job_row['job_id']) > 0) { ?>
					<span style="color: #FFF; background: #F4C869; padding: 5px 11px; border-radius: 5px" class="jobfia_tooltip" title="awaiting review tasks"><?php echo GetJobPendingTasks($job_row['job_id']); ?></span>
					<span style="color: #FFF; background: #92D811; padding: 5px 11px; border-radius: 5px" class="jobfia_tooltip" title="satisfied tasks"><?php echo GetJobPaidTasks($job_row['job_id']); ?></span>
					<span style="color: #FFF; background: #F9AA98; padding: 5px 11px; border-radius: 5px" class="jobfia_tooltip" title="declined tasks"><?php echo GetJobDeclinedTasks($job_row['job_id']); ?></span>
					<br><br><p>Success Rate: <?php echo GetJobSuccess($job_row['job_id']); ?></p>
				<?php } ?>
			</td>
		</tr>
		</table>
		<?php } ?>
		
		<br>
		<a name="applicants"></a>
		<h3 class="brd"><i class="fa fa-users" style="color: #5cb85c"></i> Job Applicants - <?php echo $job_row['title']; ?></h3>
		<?php if (@$filter) { ?><p><b><i class="fa fa-search"></i> Search results for '<?php echo substr($filter, 0, 70); ?>'</b></p><hr><?php } ?>
		
		<?php if ($total > 0) { ?>
		<form action="" id="form1" name="form1" method="get">
		<div class="row">
			<div class="col-md-3">			
					<!--<span>Sort by:</span>-->
					<div class="col-md-6">
					<select name="column" id="column" onChange="document.form1.submit()" class="form-control input-sm">
						<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Date</option>
						<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
						<option value="worker_id" <?php if ($_GET['column'] == "worker_id") echo "selected"; ?>>Worker Name</option>
						<option value="proof" <?php if ($_GET['column'] == "proof") echo "selected"; ?>>Proof submitted</option>
					</select>
					</div>
					<div class="col-md-6">
					<select name="order" id="order" onChange="document.form1.submit()" class="form-control input-sm">
						<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
					</div>
			</div>
			<div class="col-md-3">
				  <!--&nbsp;&nbsp;<span>Results:</span>-->
				  <select name="show" id="show" onChange="document.form1.submit()" class="form-control input-sm">
					<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
					<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
					<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
					<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
				  </select>
			</div>
			<div class="col-md-4">
					<div class="input-group form-group-sm">
						<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control input-sm" required />
						<span class="input-group-btn">
							<input type="submit" class="btn btn-info" value="Search" />
							<?php if (isset($filter) && $filter != "") { ?><a href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $job_id; ?>"><span class="label label-info"><i class="fa fa-remove"></i> cancel search</span></a><?php } ?>
						</span>
					</div>
					<input type="hidden" name="id" value="<?php echo $job_id; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
			</div>
			<div class="col-md-2 results text-right">
				<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</div>
		</div>
		</form>
		<br>
		<?php } ?>
		
			<form id="form2" name="form2" method="post" action="#applicants">
			<div class="table-responsive">
            <table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="15%">Date/Time</th>
				<th width="25%">Worker</th>
				<th width="25%">Proof</th>
				<th width="10%">Proof File</th>
                <th width="10%">Status</th>
				<th width="10%">Actions</th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td align="center" valign="middle">
					<input type="checkbox" class="checkbox" <?php if ($row['status'] != "pending") echo "disabled=\"disabled\""; ?> name="id_arr[<?php echo $row['job_task_id']; ?>]" id="id_arr[<?php echo $row['job_task_id']; ?>]" value="<?php echo $row['job_task_id']; ?>" />
                  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo $row['date_complete']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php if ($row['country_code'] != "") { ?><img src="<?php SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /> <?php } ?> <i class="fa fa-user-circle-o"></i> <?php echo GetWorkerInfo($row['worker_id'], 2); ?></td>
                  <td valign="middle" align="left">
					<?php
							if (strlen($row['job_proof']) > 75)
							{
								$job_proof = substr($row["job_proof"], 0, 70);
								$job_proof .= "... <a href='".SITE_URL."job_applicants_details.php?job_id=".$row['job_id']."&id=".$row['job_task_id']."><img src='".SITE_URL."images/icon_more.png' align='absmiddle' /></a>";
							}
							else
							{
								$job_proof = $row["job_proof"];
							}

							if (@$filter) echo highlight_word($job_proof, $filter, "#EF9013"); else echo $job_proof;
					?>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left"><?php if ($row['proof_file'] != "") { ?><a href="<?php echo substr(SITE_URL,0,-1).JOBFIA_PROOFS_URL.$row['proof_file']; ?>" target="_blank"><i class="fa fa-paperclip fa-lg" aria-hidden="true"></i></a> <?php } ?></td>      
				  <td nowrap="nowrap" valign="middle" align="left">
					<?php
						switch ($row['status'])
						{
							case "notcomplete": echo "<span class='label label-default'>not complete</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;							
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-money'></i> paid</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}
						if ($row['comment'] != "") echo " <i class=\"fa fa-info-circle jobfia_tooltip\" title=\"".$row['comment']."\"></i>";
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left"><a href="<?php echo SITE_URL; ?>job_applicants_details.php?job_id=<?php echo $row['job_id']; ?>&id=<?php echo $row['job_task_id']; ?>"><i class="fa fa-search fa-lg jobfia_tooltip" title="Details" style="color: #777"></i></a></td>
                </tr>
			<?php } ?>
				<tr>
					<td style="background: #fcfcfc; border-top: solid 2px #FFF;" colspan="2" align="left">
						<b>Mark selected as:</b>
					</td>
					<td style="background: #fcfcfc; border-top: solid 2px #FFFF;" align="left"> 
						<select name="mark_as" class="form-control input-sm" required>
							<option value="">--------------</option>
							<option value="confirm" id="background:#82ED09;color:#FFF;">&#10003; Satisfied</option>
							<option value="incomplete" style="background:#CECECE;color:#555;">- Not Complete</option>
							<option value="decline" style="background:#FC2020;color:#FFF;">&times; Not Satisfied</option>
						</select>
					</td>
					<td style="background: #fcfcfc; border-top: solid 2px #FFF;" colspan="4" align="left">
					
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="modify" />
						<input type="submit" class="btn btn-info" name="MassModify" id="MassModify" value="Go" />
					</td>
				</tr>
		<?php }else{ ?>
				<tr>
				<td colspan="7" align="center">			
					<?php if (@$filter) { ?>
						<p align="center">No applicants found. <a class="btn btn-info" href="<?php echo SITE_URL; ?>job_applicants.php?id=<?php echo $job_id; ?>">Search again &#155;</a></p>
					<?php }else{ ?>
						<p align="center">No applicants for this job at this time.</p>
					<?php } ?>
				</td>
				</tr>
		<?php } ?>
           </table>
           </form>
           </div>
           				
				<?php echo ShowPagination("job_tasks",$results_per_page,"job_applicants.php?id=$job_id&column=$rrorder&order=$rorder&show=$results_per_page&","WHERE job_id='$job_id'".$filter_by); ?>


		<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>myjobs.php"><i class="fa fa-angle-left"></i> Go back</a></p>


<?php require_once ("inc/footer.inc.php"); ?>