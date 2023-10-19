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
					case "fname": $rrorder = "fname"; break;
					case "lname": $rrorder = "lname"; break;
					case "username": $rrorder = "username"; break;
					case "email": $rrorder = "email"; break;
					case "ids": $rrorder = "user_id"; break;
					case "premium": $rrorder = "premium"; break;
					case "balance": $rrorder = "balance"; break;
					case "country": $rrorder = "country_id"; break;
					case "usertype": $rrorder = "usertype"; break;
					case "status": $rrorder = "status"; break;
					case "satisfied_tasks":	$rrorder = "satisfied_tasks"; break;
					default: $rrorder = "user_id"; break;
				}
			}
			else
			{
				$rrorder = "user_id";
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
				$show_only	= getGetParameter('show_only');
				$filter		= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				$filter_by	= " AND (username LIKE '%".$filter."%' OR email LIKE '%".$filter."%' OR fname LIKE '%".$filter."%' OR lname LIKE '%".$filter."%')";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$where = "1=1";

		if ($_GET['show_only'])
		{
			$show_only = $_GET['show_only'];
			switch ($show_only)
			{
				case "online":		$where .= " AND DATE_ADD(last_activity, INTERVAL 20 minute) > NOW()"; break;
				case "employers":	$where .= " AND usertype='employer'"; break;
				case "workers":		$where .= " AND usertype='worker'"; break;
				case "pworkers":	$where .= " AND usertype='worker' AND premium='1'"; break;
				case "active":		$where .= " AND status='active'"; break;
				case "pending":		$where .= " AND status='inactive' AND login_count='0' AND activation_key!=''"; break;
				case "blocked":		$where .= " AND status='inactive' AND login_count>0"; break;
			}
		}

		if (isset($_GET['country']) && is_numeric($_GET['country']))
		{
			$country = (int)$_GET['country'];
			$where .= " AND country_id='$country'";
			$atitle = GetCountry($country);
		}

		$query = "SELECT *, DATE_FORMAT(created, '%d %b %Y') AS signup_date, DATE_FORMAT(last_activity, '%d %b %Y') AS last_activity_date FROM jobfia_users WHERE $where $filter_by ORDER BY ".$rrorder." ".$rorder." LIMIT ".$from.",".$results_per_page;
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_users WHERE $where".$filter_by;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		$title = "Users";

		require_once ("inc/header.inc.php");

?>

       <div id="addnew">
			<a href="top_referrals.php" class="top_users">Top Referrals</a>
			<a href="javascript:void(0);" class="search" onclick="$('#admin_filter').toggle('slow');">Filter users</a>
       </div>

       <h2><i class="fa fa-users" style="color: #5cb85c"></i> <?php echo $atitle; ?> Users <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?></h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<i class="fa fa-check-circle-o fa-lg"></i> 
			<?php
				switch ($_GET['msg'])
				{
					case "updated": echo "User information has been successfully edited"; break;
					case "deleted": echo "User has been successfully deleted"; break;
				}
			?>
		</div>
		<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" width="100%" border="0" cellpadding="3" cellspacing="0" align="center">
		<tr>
			<td colspan="2" align="center">
				<div id="admin_filter" style="<?php if (!(isset($_GET['action']) && $_GET['action'] == "filter")){ ?>display: none;<?php } ?>">
				<!--<b>Filter</b>: -->
				<select name="show_only" class="form-control" id="show_only" onChange="document.form1.submit()">
					<option value="">-- show all --</option>
					<option value="online" <?php if ($_GET['show_only'] == "online") echo "selected"; ?>>online users</option>
					<option value="employers" <?php if ($_GET['show_only'] == "employers") echo "selected"; ?>>employers only</option>
					<option value="workers" <?php if ($_GET['show_only'] == "workers") echo "selected"; ?>>workers only</option>
					<option value="pworkers" <?php if ($_GET['show_only'] == "pworkers") echo "selected"; ?>>premium account only</option>
					<option value="active" <?php if ($_GET['show_only'] == "active") echo "selected"; ?>>active accounts</option>
					<option value="pending" <?php if ($_GET['show_only'] == "pending") echo "selected"; ?>>pending accounts</option>
					<option value="blocked" <?php if ($_GET['show_only'] == "blocked") echo "selected"; ?>>blocked accounts</option>
				</select>
				<?php if ($_GET['show_only']) { ?><a title="Cancel Filter" href="users.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Filter" /></a><?php } ?>
				<select name="country" class="form-control" id="country" onChange="document.form1.submit()">
					<option value="">-- all countries --</option>
					<?php
						$sql_country = "SELECT COUNT(*) AS country_users, users.country_id, countries.name FROM jobfia_users users, jobfia_countries countries WHERE users.country_id=countries.country_id GROUP BY users.country_id ORDER BY country_users DESC";
						$rs_country = smart_mysql_query($sql_country);
						$total_country = mysqli_num_rows($rs_country);

						if ($total_country > 0)
						{
							while ($row_country = mysqli_fetch_array($rs_country))
							{
								if ($country == $row_country['country_id'])
									echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']." (".$row_country['country_users'].")</option>\n";
								else
									echo "<option value='".$row_country['country_id']."'>".$row_country['name']." (".$row_country['country_users'].")</option>\n";
							}
						}
					?>
				</select>
				<?php if (isset($country) && $country != "") { ?><a title="Cancel Filter" href="users.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Filter" /></a><?php } ?>
				<input type="hidden" name="action" value="filter" />
				<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="50" /> 
				<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
				<?php if (isset($filter) && $filter != "") { ?><a title="Cancel Search" href="users.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?> 
				</div>			
			</td>
		</tr>
		<tr>
		<td nowrap="nowrap" valign="middle" align="left" width="45%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Signup Date</option>
			<option value="fname" <?php if ($_GET['column'] == "fname") echo "selected"; ?>>First Name</option>
			<option value="lname" <?php if ($_GET['column'] == "lname") echo "selected"; ?>>Last Name</option>
			<option value="username" <?php if ($_GET['column'] == "username") echo "selected"; ?>>Username</option>
			<option value="email" <?php if ($_GET['column'] == "email") echo "selected"; ?>>Email</option>
			<option value="balance" <?php if ($_GET['column'] == "balance") echo "selected"; ?>>Balance</option>
			<option value="country" <?php if ($_GET['column'] == "country") echo "selected"; ?>>Country</option>
			<option value="usertype" <?php if ($_GET['column'] == "usertype") echo "selected"; ?>>User type</option>
			<option value="satisfied_tasks" <?php if ($_GET['column'] == "satisfied_tasks") echo "selected"; ?>>Worker Rating</option>
			<option value="premium" <?php if ($_GET['column'] == "premium") echo "selected"; ?>>Premium Workers</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
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
			</td>
			<td nowrap="nowrap" valign="middle" width="20%" align="right">
			    <?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr bgcolor="#F7F7F7" align="center">
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="7%">ID</th>
				<th width="10%">&nbsp;</th>
				<th width="20%">Full Name/Username</th>
				<th width="20%">Email</th>
				<th width="7%">Country</th>
				<th width="10%"><i class="fa fa-money"></i> Balance</th>
				<th width="10%"><i class="fa fa-check-circle-o"></i> Work Done</th>
				<th width="10%">Signup Date</th>
				<th width="10%">Last Active</th>
				<th width="10%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php if ($total > 0) { ?>
			 <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td nowrap="nowrap" align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['user_id']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="55" height="55" class="img-responsive" border="0" style="border-radius: 20%" /></a>
						<?php $online= 0; if (ifUserOnline($row['user_id'])) { $online=1; ?><span class="online" title="online" style="font-size: 12px"><img src="images/icon_online.png" align="absmiddle" /> online</span><?php } ?>
					</td>
					<td align="left" valign="middle">
						<?php if ($row['premium'] == 1) { ?><img src="images/icons/user_premium.png" width="13" align="absmiddle" class="jobfia_tooltip" title="premium account" /><?php } ?>
						<?php /*if ($row['usertype'] == "employer") { ?><img src="images/icons/employer.png" width="13" align="absmiddle" class="jobfia_tooltip" title="employer" /><?php }*/ ?>
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>&pn=<?php echo $page; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a>
						<br/><span style="color: #999"><?php echo $row['username']; ?></span>
					</td>
					<td align="left" valign="middle"><a style="color: #777" href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo GetCountry($row['country_id'], $show_name = 0); ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><a style="color: #000" href="user_payments.php?id=<?php echo $row['user_id']; ?>"><?php echo DisplayMoney($row['balance']); ?></a></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="applicants.php?worker_id=<?php echo $row['user_id']; ?>">
						<span class="acount total jobfia_tooltip" title="Total tasks"><?php echo GetWorkerJobsTotal($row['user_id']); ?></span>
						<span class="acount success jobfia_tooltip" title="Successful tasks"><?php echo GetWorkerPaidJobs($row['user_id']); ?></span>
						<span class="acount unsuccess jobfia_tooltip" title="Unsuccessful tasks"><?php echo GetWorkerDeclinedJobs($row['user_id']); ?></span>
						</a>			
					</td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['signup_date']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 7px">
						<small>
						<?php if ($online == 1) { ?>
							<span class="online" title="online"><img src="images/icon_online.png" align="absmiddle" /> online</span>
						<?php }else if ($row['login_count'] > 0) { ?>
							<?php echo relative_date(strtotime($row['last_activity'])); ?>
						<?php }else{ ?>
							<?php echo relative_date(strtotime($row['created'])); ?>
						<?php } ?>
						</small>					
					</td>
					<td nowrap="nowrap" align="center" valign="middle"><?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'>".$row['status']."</span>"; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="user_money.php?id=<?php echo $row['user_id']; ?>" title="Add/Deduct Money"><img src="images/cash.png" border="0" alt="Add/Deduct Money" /></a>
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="user_edit.php?id=<?php echo $row['user_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='user_delete.php?id=<?php echo $row['user_id']; ?>';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
					<td colspan="12" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="delete" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
				<tr>
					<td colspan="12" align="center">
						<?php echo ShowPagination("users",$results_per_page,"users.php?column=$rrorder&order=$rorder&show=$results_per_page&country=$country&","WHERE $where".$filter_by.""); ?>
					</td>
				</tr>

        <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<tr><td colspan="12"><div class="alert alert-info text-center">No members found. <a class="btn btn-info" href="users.php"><i class="fa fa-search"></i> Search again</a></div></td></tr>
				<?php }else{ ?>
					<tr><td colspan="12"><div class="alert alert-info text-center">There are no users at this time.</div></td></tr>
				<?php } ?>
        <?php } ?>

            </table>
			</form>


<?php require_once ("inc/footer.inc.php"); ?>