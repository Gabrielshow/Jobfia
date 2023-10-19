<?php
/*******************************************************************\
 * Jobfia v1.0
 * http://www.Jobfia.com
 *
 * Copyright (c) 2011 Jobfia Software. All rights reserved.
  * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_adm.inc.php");
	require_once("../inc/config.inc.php");
	require_once("../inc/pagination.inc.php");
	require_once("./inc/adm_functions.inc.php");

	$results_per_page = 10;


		// Delete users //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$userid = (int)$v;
					DeleteUser($userid);
				}

				header("Location: users.php?msg=deleted");
				exit();
			}	
		}

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "ip": $rrorder = "ip"; break;
					case "fname": $rrorder = "fname"; break;
					case "lname": $rrorder = "lname"; break;
					case "email": $rrorder = "email"; break;
					case "ids": $rrorder = "user_id"; break;
					case "balance": $rrorder = "balance"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "user_id"; break;
				}
			}
			else
			{
				$rrorder = "ip";
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
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }

		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y <sup>%h:%i %p</sup>') AS signup_date FROM jobfia_users WHERE ip IN (SELECT ip FROM jobfia_users GROUP by ip HAVING COUNT(DISTINCT user_id) > 1) ORDER BY ".$rrorder." ".$rorder." LIMIT ".$from.",".$results_per_page;
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_users WHERE ip IN (SELECT ip FROM jobfia_users GROUP by ip HAVING COUNT(DISTINCT user_id) > 1)";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


		$title = "Accounts with same IP";

		require_once ("inc/header.inc.php");

?>

       <h2><i class="fa fa-flag" style="color: #c9302c"></i> Accounts with same IP</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "updated": echo "User information has been successfully edited!"; break;
						case "deleted": echo "User has been successfully deleted!"; break;
					}

				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" width="100%" border="0" cellpadding="3" cellspacing="0" align="center">
		<tr>
		<td nowrap="nowrap" valign="top" align="left" width="65%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
	        <option value="ids" <?php if ($_GET['column'] == "ip") echo "selected"; ?>>IP Address</option>
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Signup Date</option>
			<option value="fname" <?php if ($_GET['column'] == "fname") echo "selected"; ?>>First Name</option>
			<option value="lname" <?php if ($_GET['column'] == "lname") echo "selected"; ?>>Last Name</option>
			<option value="email" <?php if ($_GET['column'] == "email") echo "selected"; ?>>Email</option>
			<option value="balance" <?php if ($_GET['column'] == "balance") echo "selected"; ?>>Balance</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
			</td>
			<td nowrap="nowrap" valign="middle" width="30%" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr bgcolor="#F7F7F7" align="center">
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="7%">ID</th>
				<th width="20%">Full Name</th>
				<th width="20%">Username</th>
				<th width="15%">IP Address</th>
				<th width="13%">Balance</th>
				<th width="15%">Signup Date</th>
				<th width="15%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			 <?php static $ipp = ""; while ($row = mysqli_fetch_array($result)) { $cc++;  //$ipp = $row['ip'];   ?>
				  <tr class="<?php if ($row['ip'] == $ipp) echo "even"; else echo "odd";  ?>">
					<td align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['user_id']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><i class="fa fa-user-circle-o" style="color: #bbb"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><a style="color: #aaa" href="user_details.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-user" style="color: #bbb"></i> <?php echo $row['username']; ?></a></td>
					<td align="left" valign="middle" style="padding-left: 10px"><?php echo $row['ip']; ?></td>
					<td align="left" valign="middle" style="padding-left: 10px"><?php echo DisplayMoney($row['balance']); ?></td>
					<td align="center" valign="middle"><?php echo $row['signup_date']; ?></td>
					<td align="left" valign="middle" style="padding-left: 15px">
						<?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle" >
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="user_edit.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='user_delete.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>&column=<?php echo $_GET['column']; ?>&order=<?php echo $_GET['order']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
					<td colspan="9" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="delete" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
            </table>
			</form>

			<?php echo ShowPagination("users",$results_per_page,"same_ip_users.php.php?column=$rrorder&order=$rorder&","WHERE ip IN (SELECT ip FROM jobfia_users GROUP by ip HAVING COUNT(DISTINCT user_id) > 1)"); ?>

        <?php }else{ ?>
					<div class="alert alert-info">There are no registered accounts with same IP at this time.</div>
        <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>