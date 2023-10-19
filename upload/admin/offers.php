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
		$results_per_page = 50;


		// Delete offers
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

				header("Location: offers.php?msg=deleted");
				exit();
			}
		}

		$where = " WHERE job_type='offer' AND ";

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

			if (isset($_GET['filter']))
			{
				$filter			= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				//$cat			= mysqli_real_escape_string($conn, getGetParameter('cat'));
				$reward			= mysqli_real_escape_string($conn, getGetParameter('reward'));
				$country		= mysqli_real_escape_string($conn, getGetParameter('country'));
				$sfeatured		= (int)getGetParameter('sfeatured');

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
			}
		///////////////////////////////////////////////////////

		$where .= "1=1 ";

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs $where ORDER BY featured DESC, $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM jobfia_jobs".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		$title = "Offers";
		require_once ("inc/header.inc.php");

?>

		<h2><i class="fa fa-list"></i> Offfers <?php if ($total > 0) echo "<sup class='badge'>".$total."</sup>"; ?></h2>


        <?php if ($total > 0) { ?>


			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "added": echo "Offer has been successfully added"; break;
						case "updated": echo "Offer has been successfully updated"; break;
						case "deleted": echo "Offer has been successfully deleted"; break;
					}

				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background: #fcfcfc" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
			<td colspan="2" align="center" style="background: #F5F5F5">
				<table>
				<tr>
					<td><b>Search fo offers</b>: </td>
					<td>&nbsp;<input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="50" title="Job Title" /></td> 
					<td><select name="cat" id="cat" class="form-control">
						<option value="">-- all categories --</option>
						<?php ShowCategoriesDropDown (0); ?>
					</select></td>
					<td>
					<input type="hidden" name="action" value="filter" />
					<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
					<?php if (isset($filter)) { ?><a title="Cancel Search" href="offers.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
		<td valign="middle" align="left" width="50%">
           Sort by: 
          <select name="column" id="column" onChange="document.form1.submit()">
			<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
			<option value="title" <?php if ($_GET['column'] == "title") echo "selected"; ?>>Name</option>
			<option value="user_id" <?php if ($_GET['column'] == "user_id") echo "selected"; ?>>Username</option>
			<option value="views" <?php if ($_GET['column'] == "views") echo "selected"; ?>>Popularity</option>
			<option value="price" <?php if ($_GET['column'] == "price") echo "selected"; ?>>Price</option>
			<option value="featured" <?php if ($_GET['column'] == "featured") echo "selected"; ?>>Featured</option>
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
				<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
				<?php if ($search) { ?><input type="hidden" name="action" value="search" /><?php } ?>
				<?php if ($stext) { ?><input type="hidden" name="searchtext" value="<?php echo $stext; ?>" /><?php } ?>
				<?php if ($reward > 0) { ?><input type="hidden" name="reward" value="<?php echo $reward; ?>" /><?php } ?>
				<?php if ($spremium == 1) { ?><input type="hidden" name="spremium" value="1" /><?php } ?>
				<?php if ($sfeatured == 1) { ?><input type="hidden" name="sfeatured" value="1" /><?php } ?>
				<?php if ($page > 0) { ?><input type="hidden" name="page" value="<?php echo $page; ?>" /><?php } ?>
			</td>
			<td nowrap="nowrap" valign="middle" width="35%" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%" style="padding-left: 7px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="5%">ID</th>
				<th width="7%">&nbsp;</th>
				<th width="35%">Offer title</th>
				<th width="8%"><i class="fa fa-money"></i> Price</th>
				<th width="8%"><i class="fa fa-shopping-cart"></i> Orders</th>
				<th width="15%"><i class="fa fa-user-circle-o"></i> Created by</th>
				<th width="8%">Date</th>
				<th width="10%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>				  
				  <tr class="<?php if ($row['featured'] == 1) { echo "featured"; }else{ if (($cc%2) == 0) echo "even"; else echo "odd"; } ?>">
					<td nowrap="nowrap" align="center" valign="middle" style="padding-left: 7px"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['job_id']; ?>]" id="id_arr[<?php echo $row['job_id']; ?>]" value="<?php echo $row['job_id']; ?>" /></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php echo $row['job_id']; ?></td>
					<td nowrap="nowrap" align="center" valign="middle">
						<center><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt="Image" width="70" height="50"></center>							
					</td>
					<td align="left" valign="middle" style="padding-left: 7px">
						<?php if ($row['featured'] == 1) { ?><span class="featured" alt="Featured" title="Featured"></span> <?php } ?>
						I will 
						<a href="offer_details.php?id=<?php echo $row['job_id']; ?>">
							<?php echo $row['title']; ?>
						</a>
						<br>
						<i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star" style="color: #eec80c"></i><i class="fa fa-star-half" style="color: #eec80c"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)
					</td>
					<td nowrap="nowrap" align="center" valign="middle"><b class="badge" style="background: #7cad34"><?php echo DisplayMoney($row['price']); ?></b></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 15px"><a href="offer_orders.php?id=<?php echo $row['job_id']; ?>"><span class="badge" style="background: #3eadd6"><?php echo GetOfferSalesTotal($row['job_id']); ?></span></a> <sup><?php echo GetOfferSalesTotal($row['job_id'], $today = 1); ?></sup></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 10px"><?php echo GetUsername($row['user_id'], $username= 1); ?></td>
					<td nowrap="nowrap" align="center" valign="middle"><?php echo $row['date_added']; ?></td>
					<td nowrap="nowrap" align="left" valign="middle" style="padding-left: 15px">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='label label-success'>active</span>"; break;
							case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
							case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting review</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "complete": echo "<span class='label label-success'>complete</span>"; break;
							default: echo "<span class='label label-primary'>".$row['status']."</span>"; break;
						}

						if ($row['status'] == "declined" && $row['reason'] != "")
						{
							echo " <span class=\"jobfia_tooltip\" title=\"".$row['reason']."\"><img src=\"images/icon_question.png\" align=\"absmiddle\" /></span>";
						}
					?>
					</td>
					<td nowrap="nowrap" align="center" valign="middle">
						<a href="offer_details.php?id=<?php echo $row['job_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="offer_edit.php?id=<?php echo $row['job_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are You sure you really want to delete this offer') )location.href='offer_delete.php?id=<?php echo $row['job_id']; ?>'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td colspan="9" align="left" style="border-top: 1px solid #F7F7F7;">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="hidden" name="action" value="delete" />
					<input type="submit" class="btn btn-danger" name="GoDelete" id="GoDelete" value="Delete Selected" />
				</td>
				</tr>
            </table>
			</form>

			<?php echo ShowPagination("jobs",$results_per_page,"offers.php?column=$rrorder&order=$rorder&show=$results_per_page&".$where); ?>

          <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<div class="alert alert-info text-center">No offers found. <a class="btn btn-info" href="offers.php"><i class="fa fa-search"></i> Search again</a></div>
				<?php }else{ ?>
					<div class="alert alert-info text-center">There are no offers at this time.</div>
				<?php } ?>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>