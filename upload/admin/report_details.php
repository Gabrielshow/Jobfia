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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$query = "SELECT reports.*, reports.user_id as ruser_id, reports.job_id as rjob_id, DATE_FORMAT(reports.added, '%d %b %Y %h:%i %p') AS date_added, jobs.* FROM jobfia_reports reports LEFT JOIN jobfia_jobs jobs ON jobs.job_id=reports.job_id WHERE reports.report_id='".$id."' LIMIT 1";
		
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Report Details";
	require_once ("inc/header.inc.php");

?>    
    
     <h2><i class="fa fa-flag" style="color: #c9302c"></i> Report Details</h2>

		<?php if ($total > 0) {

				smart_mysql_query("UPDATE jobfia_reports SET viewed='1' WHERE report_id='$id'");
				$row = mysqli_fetch_array($result);
		 ?>
			<!--<img src="images/icons/alert.png" style="position: absolute; right: 10px;" />-->
            <table style="background: #fcfcfc" width="100%" cellpadding="3" cellspacing="5" border="0">
			  <tr>
                <td width="150" valign="middle" align="left" class="tb1">Report ID:</td>
                <td valign="top"><?php echo $row['report_id']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">From:</td>
                <td valign="top"><?php echo GetUsername($row['reporter_id']); ?></td>
              </tr>
			  <?php if ($row['ruser_id'] != 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Member's Name:</td>
                <td valign="top"><a href="user_details.php?id=<?php echo $row['ruser_id']; ?>"><?php echo GetUsername($row['ruser_id']); ?></a></td>
              </tr>
			  <?php } ?>
			  <?php if ($row['rjob_id'] != 0) { ?>
              <tr>
                <td valign="middle" align="left" class="tb1">Job Name:</td>
                <td valign="top"><a href="job_details.php?id=<?php echo $row['rjob_id']; ?>"><?php echo $row['title']; ?></a></td>
              </tr>
			  <?php } ?>
              <tr>
				<td valign="middle" align="left" class="tb1">Reason:</td>
				<td valign="top"><div style="width:60%;padding:10px;background:#FFFFFF;border:1px solid #EEE; border-radius: 5px;"><?php echo $row['report']; ?></td>
              </tr>
              <tr>
                <td valign="middle" align="left" class="tb1">Created:</td>
                <td valign="top"><?php echo $row['date_added']; ?></td>
              </tr>
            <tr>
              <td>&nbsp;</td>
			  <td align="left" valign="bottom">
				<input type="button" class="btn btn-danger" name="delete" value="Delete Report" onclick="if (confirm('Are you sure you really want to delete this report?') )location.href='report_delete.php?id=<?php echo $row['report_id']; ?>&type=<?php if ($row['job_id']!='0') echo "jobs"; else echo "users"; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" />
			  </td>
            </tr>
          </table>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no report found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="goback" value="Go Back" onclick="history.go(-1);return false;"></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>