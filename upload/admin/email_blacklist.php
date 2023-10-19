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

	if (isset($_GET['show']) && $_GET['show'] == "all")
		$results_per_page = 100000;
	else
		$results_per_page = 10;


		// Delete blacklist //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$blacklistid = (int)$v;
					DeleteEmailBlacklist($blacklistid);
				}

				header("Location: email_blacklist.php?msg=deleted");
				exit();
			}	
		}


	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		unset($domains);
		$domains = array();
		$done = 0;

		if ($_FILES['blacklist_file']['tmp_name'])
		{
			$blacklist_file	= $_FILES['blacklist_file']['name'];

			if (preg_match('/\\.(txt)$/i', $blacklist_file) == 1)
			{
				$domains = file($_FILES['blacklist_file']['tmp_name']);
			}
		}

		$domains[] = getPostParameter('domain');

		foreach ($domains as $domain)
		{
			if ($domain != "")
			{
				$domain = mysqli_real_escape_string($conn, trim($domain));
				$check_query = smart_mysql_query("SELECT * FROM jobfia_email_blacklist WHERE domain='$domain'");
				if (mysqli_num_rows($check_query) == 0)
				{
					smart_mysql_query("INSERT INTO jobfia_email_blacklist SET domain='$domain'");
					$done = 1;
				}
			}
		}

		if ($done == 1)
		{
			header("Location: email_blacklist.php?msg=added");
			exit();
		}
	}

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	$query = "SELECT * FROM jobfia_email_blacklist ORDER BY blacklist_id ASC LIMIT $from, $results_per_page";
	$result = smart_mysql_query($query);

	$total_result = smart_mysql_query("SELECT * FROM jobfia_email_blacklist ORDER BY blacklist_id ASC");
	$total = mysqli_num_rows($total_result);

	$cc = 0;


	$title = "Email Blacklist";
	require_once ("inc/header.inc.php");

?>

		<h2>Email Blacklist</h2>

		<div style="float: right; width: 40%; background: #F9F9F9; border-radius: 5px; padding: 10px; margin-top: 30px;">
		  <center><h3>Add to Blacklist</h3></center>
		  <form enctype="multipart/form-data" action="" method="post" name="form1">
		  <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
          <tr>
            <td width="100" valign="middle" align="right" class="tb1">Domain:</td>
			<td align="left"><input type="text" name="domain" id="domain" value="" size="35" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle">&nbsp;</td>
			<td align="left">-- OR upload file --</td>
          </tr>
          <tr>
            <td valign="top" align="right" class="tb1" style="padding-top: 10px;">File:</td>
			<td align="left"><input type="file" class="form-control" name="blacklist_file" size="20" /><br/><span style="color: #555">(.txt file, one domain per row) <a href="example/blacklist.txt">example.txt</a></span></td>
          </tr>
          <tr>
			<td align="left">&nbsp;</td>
			<td align="left">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add to Blacklist" />
		    </td>
          </tr>
		  </table>
		  </form>
		</div>
		<br/>

		<div style="width: 50%; float: left; ">

        <?php if ($total > 0) { ?>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="380" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td height="15" colspan="3" align="right" valign="middle">
					<?php if ($total > 20) { ?><a href="email_blacklist.php?show=all" style="color: #777">show all <b><?php echo $total; ?></b> domains &#155;</a><?php } ?>
					<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
					<div style="width:377px;" class="alert alert-success">
						<?php

							switch ($_GET['msg'])
							{
								case "added":	echo "Blacklist was successfully updated"; break;
								case "exists":	echo "Sorry, domain exists in blacklist"; break;
								case "updated": echo "Blacklist was successfully updated"; break;
								case "deleted": echo "Blacklist was successfully updated"; break;
							}

						?>
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr bgcolor="#F7F7F7" align="center">
				<th width="3%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkboxx" /></th>
				<th width="55%">Domain</th>
				<th width="20%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle"><input type="checkbox" class="checkboxx" name="id_arr[<?php echo $row['blacklist_id']; ?>]" id="id_arr[<?php echo $row['blacklist_id']; ?>]" value="<?php echo $row['blacklist_id']; ?>" /></td>
					<td nowrap="nowrap" align="left" valign="middle"><?php echo $row['domain']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this domain from blacklist?') )location.href='email_blacklist_delete.php?id=<?php echo $row['blacklist_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				</tr>
			<?php } ?>
				<tr>
					<td colspan="3" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="delete" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
            </table>
			</form>

			<?php echo ShowPagination("email_blacklist",$results_per_page,"?",""); ?>
          
		  <?php }else{ ?>
					<div class="alert alert-info">Blacklist is empty.</div>
          <?php } ?>

		  </div>
		  <div style="clear: both"></div>


<?php require_once ("inc/footer.inc.php"); ?>