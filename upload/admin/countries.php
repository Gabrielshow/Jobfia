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
		$results_per_page = 1000;
	else
		$results_per_page = 10;


		// Delete countries //
		if (isset($_POST['action']) && $_POST['action'] == "delete")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$countryid = (int)$v;
					DeleteCountry($countryid);
				}

				header("Location: countries.php?msg=deleted");
				exit();
			}	
		}


	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		$country_name = mysqli_real_escape_string($conn, getPostParameter('country_name'));

		if (isset($country_name) && $country_name != "")
		{
			$check_query = smart_mysql_query("SELECT * FROM jobfia_countries WHERE name='$country_name'");
			
			if (mysqli_num_rows($check_query) == 0)
			{
				$sql = "INSERT INTO jobfia_countries SET name='$country_name'";

				if (smart_mysql_query($sql))
				{
					header("Location: countries.php?msg=added");
					exit();
				}
			}
			else
			{
				header("Location: countries.php?msg=exists");
				exit();
			}
		}
	}

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	$query = "SELECT * FROM jobfia_countries ORDER BY name LIMIT $from, $results_per_page";
	$result = smart_mysql_query($query);

	$total_result = smart_mysql_query("SELECT * FROM jobfia_countries ORDER BY name");
	$total = mysqli_num_rows($total_result);

	$cc = 0;


	$title = "Countries";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a href="javascript:void(0);" class="search" onclick="$('#add_country').toggle('slow');$('#list_box').toggle('fast');">Add Country</a></div>

		<h2><i class="fa fa-globe"></i> Countries</h2>

					<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
					<div style="width:500px; margin: 0 auto;" class="alert alert-success">
						<i class="fa fa-check-circle-o fa-lg"></i> 
						<?php
							switch ($_GET['msg'])
							{
								case "added":	echo "Country was successfully added"; break;
								case "exists":	echo "Sorry, country exists"; break;
								case "updated": echo "Country has been successfully edited"; break;
								case "deleted": echo "Country has been successfully deleted"; break;
							}
						?>
					</div>
					<?php } ?>

		<div id="add_country" style="width: 400px; background: #F7F7F7; padding: 10px; margin: 0 auto; <?php if (!(isset($_POST['action']) && $_POST['action'] == "add")){ ?>display: none;<?php } ?>">
		  <form action="" method="post">
		  <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
          <tr>
            <td width="80" valign="middle" align="right" class="tb1">Name:</td>
			<td align="left"><input type="text" name="country_name" id="country_name" value="" size="22" class="form-control" required="required" /></td>
			<td align="left">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Country" />
		    </td>
          </tr>
		  </table>
		  </form>
		</div>


        <?php if ($total > 0) { ?>

		<div id="list_box">
			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="500" border="0" cellpadding="3" cellspacing="0">
			<tr align="center">
				<td height="15" colspan="6" align="right" valign="middle">
					<?php if ($total > 20) { ?><a href="countries.php?show=all" style="color: #777">show all <span class="badge"><?php echo $total; ?></span> countires &#155;</a><?php } ?>
				</td>
			</tr>
			<tr bgcolor="#F7F7F7" align="center">
				<th width="3%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkboxx" /></th>
				<th width="7%">&nbsp;</th>
				<th width="40%">Country</th>
				<th width="18%"><i class="fa fa-users"></i> Users</th>
				<th width="18%"><i class="fa fa-briefcase"></i> Jobs</th>
				<th width="20%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle"><input type="checkbox" class="checkboxx" name="id_arr[<?php echo $row['country_id']; ?>]" id="id_arr[<?php echo $row['country_id']; ?>]" value="<?php echo $row['country_id']; ?>" /></td>
					<td align="center" valign="middle"><img src="../images/flags/<?php echo strtolower($row['code']); ?>.png" /></td>
					<td nowrap="nowrap" align="left" valign="middle" ><a href="country_edit.php?id=<?php echo $row['country_id']; ?>" style="color: #333"><?php echo $row['name']; ?></a></td>
					<td nowrap="nowrap" align="center" valign="middle"><span class="badge"><?php echo GetCountryUsers($row['country_id'], $all = 1); ?></span></td>
					<td nowrap="nowrap" align="center" valign="middle"><span class="badge"><?php echo GetCountryJobs($row['country_id'], $all = 1); ?></span></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="country_edit.php?id=<?php echo $row['country_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this country?') )location.href='country_delete.php?id=<?php echo $row['country_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				</tr>
			<?php } ?>
				<tr>
					<td colspan="6" align="left" style="border-top: 1px solid #F7F7F7">
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="delete" />
						<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
					</td>
				</tr>
            </table>
			</form>

			<center><?php echo ShowPagination("countries",$results_per_page,"?",""); ?></center>
          
		  <?php }else{ ?>
					<div class="alert alert-info">There are no countries at this time.</div>
          <?php } ?>
          
			</div>


<?php require_once ("inc/footer.inc.php"); ?>