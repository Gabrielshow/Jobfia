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

			header("Location: user_jobs.php?id=".$_GET['id']."&msg=deleted");
			exit();
		}
	}

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$uid = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE user_id='$uid' ORDER BY added DESC";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}  

	$title = "Employer Jobs";
	require_once ("inc/header.inc.php");

?>

		<h2><i class="fa fa-briefcase" style="color: #5cb85c"></i>  <?php echo GetUsername($uid); ?> Jobs</h2>

		<?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "deleted": echo "Jobs has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="4%">&nbsp;</th>
				<th width="35%">Job</th>
                <th width="13%"><i class="fa fa-money"></i> Reward</th>
				<th width="13%">Job Done</th>
				<th width="13%">Success</th>
				<th width="12%">Posted</th>
				<th width="17%">Status</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr bgcolor="<?php if (($cc%2) == 0) echo "#F7F7F7"; else echo "#FFFFFF"; ?>">
                  <td nowrap="nowrap" align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['job_id']; ?>]" id="id_arr[<?php echo $row['job_id']; ?>]" value="<?php echo $row['job_id']; ?>" /></td>
                  <td nowrap="nowrap" align="center" valign="middle"><i class="fa fa-briefcase fa-2x" style="color: #d2ddd2"></i></td>
                  <td valign="middle" align="left"><a href="job_details.php?id=<?php echo $row['job_id']; ?>"><?php echo $row['title']; ?></a></td>
                  <td valign="middle" align="left" style="padding-left: 10px"><h4><?php echo DisplayMoney($row['price']); ?></h4></td>
				  <td valign="middle" align="center">
					  <a href="job_tasks.php?id=<?php echo $row['job_id']; ?>"><?php echo GetJobWorkersTotal($row['job_id']); ?> / <sup><?php echo $row['positions']; ?></sup></a>
						<div class="progress">
							  <div class="progress-bar progress-bar-striped progress-bar-success progress-bar-animated" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>%"></div>
						</div>					  
				  </td>
				  <td valign="middle" align="center">
						<span class="acount pending jobfia_tooltip" title="awaiting review"><?php echo GetJobPendingTasks($row['job_id']); ?></span>
						<span class="acount success jobfia_tooltip" title="satisfied"><?php echo GetJobPaidTasks($row['job_id']); ?></span>
						<span class="acount unsuccess jobfia_tooltip" title="declined"><?php echo GetJobDeclinedTasks($row['job_id']); ?></span>
						<?php if (GetJobWorkersTotal($row['job_id']) > 0) { ?>
							<p><i class="fa fa-bar-chart"></i> success: <?php echo GetJobSuccess($row['job_id']); ?></p>
						<?php } ?>						
				  </td>
				  <td valign="middle" align="center"><?php echo $row['date_added']; ?></td>
                  <td nowrap valign="middle" align="left" style="padding-left: 10px">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='label label-success'>active</span>"; break;
							case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
							case "pending": echo "<span class='label label-warning'>awaiting review</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "complete": echo "<span class='label label-success'>complete</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}

						if ($row['status'] == "declined" && $row['reason'] != "")
						{
							echo " <span class=\"jobfia_tooltip\" title=\"".$row['reason']."\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}

						if ($row['status'] == "inactive" && $row['paused'] != "0000-00-00 00:00:00")
						{
							echo " <span class=\"jobfia_tooltip\" title=\"auto paused by daily tasks limit, will run automatically tomorrow\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}
					?>
				  </td>
                </tr>
				<?php } ?>
				<tr>
					<td colspan="8" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="action" value="delete" />
						<input type="hidden" name="id" value="<?php echo $uid; ?>" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
           </table>
		   </form>
	  
	  <?php }else{ ?>
				<div class="alert alert-info text-center">This employer has no jobs posted.</div>
      <?php } ?>

		<p align="center"><input class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" type="button"></p>


<?php require_once ("inc/footer.inc.php"); ?>