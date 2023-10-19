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


		// Delete reports //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$report_id = (int)$v;
					DeleteReport($report_id);
				}

				header("Location: user_reports.php?msg=deleted");
				exit();
			}
		}

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT reports.*, DATE_FORMAT(reports.added, '%d %b %Y %h:%i %p') AS date_added, users.* FROM jobfia_reports reports LEFT JOIN jobfia_users users ON users.user_id=reports.user_id WHERE reports.user_id<>'0' ORDER BY reports.added DESC LIMIT $from, $results_per_page";

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_reports WHERE user_id<>'0'";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


	$title = "Users Reports";
	require_once ("inc/header.inc.php");

?>

		<h2><i class="fa fa-flag" style="color: #c9302c"></i> Users Reports</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "deleted": echo "Report has been successfully deleted!"; break;
					}
				?>
			</div>
			<?php } ?>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="15%">Date</th>
				<th width="17%">Member</th>
				<th width="40%">Reason</th>
				<th width="17%">From</th>
				<th width="12%">Actions</th>
			</tr>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>				  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['report_id']; ?>]" id="id_arr[<?php echo $row['report_id']; ?>]" value="<?php echo $row['report_id']; ?>" /></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['date_added']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle"><?php echo GetUsername($row['user_id']); ?></td>
					<td align="left" valign="middle"><?php if (strlen($row['report']) > 50) echo substr($row['report'], 0, 45)."..."; else echo $row['report']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle"><?php echo GetUsername($row['reporter_id']); ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="report_details.php?id=<?php echo $row['report_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this report?') )location.href='report_delete.php?id=<?php echo $row['report_id']; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>&pn=<?php echo $page?>&type=users'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td colspan="6" align="left" style="border-top: 1px solid #F7F7F7">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="action" value="delete" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
				</tr>
            </table>
			</form>

				<?php echo ShowPagination("reports",$results_per_page,"user_reports.php?column=$rrorder&order=$rorder&","WHERE user_id<>'0'"); ?>

          <?php }else{ ?>
					<div class="alert alert-info text-center">There are no reports at this time.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>