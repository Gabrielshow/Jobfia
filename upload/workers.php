<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");
	if (SHOW_WORKERS_UNREG != 1)
	{
		require_once("inc/auth.inc.php");
	}
	require_once("inc/pagination.inc.php");


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;

	$cc = 0;


	// Add workers to group //
	if (isset($_POST['action']) && $_POST['action'] == "add_workers")
	{
		$ids_arr	= array();
		$ids_arr	= $_POST['id_arr'];

		if (count($ids_arr) > 0)
		{
			foreach ($ids_arr as $v)
			{
				$workerid = (int)$v;
				if (@$userid != $workerid) $_SESSION['hire_workers'][] = $workerid;
			}

			header("Location: hire.php");
			exit();
		}	
	}

	////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "satisfied_tasks":	$rrorder = "satisfied_tasks"; break;
				case "fname":			$rrorder = "fname"; break;
				case "lname":			$rrorder = "lname"; break;
				case "rate":			$rrorder = "rate"; break;
				case "country":			$rrorder = "country_id"; break;
				case "created":			$rrorder = "created"; break;
				case "activity":		$rrorder = "last_login"; break;
				default:				$rrorder = "created"; break;
			}
		}
		else
		{
			$rrorder = "created";
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
	//////////////////////////////////////////////////

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	
	$from = ($page-1)*$results_per_page;
	$where = " 1=1 AND (usertype='worker' OR worker='1') AND ";


	// SEARCH WORKERS //
	if (isset($_GET['action']) && $_GET['action'] == "search")
	{
		$search		= 1;
		$stext		= mysqli_real_escape_string($conn, getGetParameter('searchtext'));
		$stext		= substr(trim($stext), 0, 50);
		$category	= mysqli_real_escape_string($conn, getGetParameter('category'));
		$country	= mysqli_real_escape_string($conn, getGetParameter('country'));
		$rate_min	= mysqli_real_escape_string($conn, getGetParameter('rate_min'));
		$rate_max	= mysqli_real_escape_string($conn, getGetParameter('rate_max'));
		$sactive	= (int)getGetParameter('sactive');
		$spremium	= (int)getGetParameter('spremium');
		$sonline	= (int)getGetParameter('sonline');

		if ($stext) $where .= " (fname LIKE '%".$stext."%' OR lname LIKE '%".$stext."%' OR email LIKE '%".$stext."%') AND ";
		
		if (is_numeric($rate_min) && $rate_min > 0)
		{
			$rate_min = (float)$rate_min;
			$where .= " rate >= '$rate_min' AND ";
		}

		if (is_numeric($rate_max) && $rate_max > 0)
		{
			$rate_max = (float)$rate_max;
			$where .= " rate <= '$rate_max' AND ";
		}

		if ($country > 0)	$where .= " country_id='".(int)$country."' AND ";
		if ($spremium == 1)	$where .= " premium='1' AND ";
		if ($sonline == 1)	$where .= " DATE_ADD(last_activity, INTERVAL 20 minute) > NOW() AND ";
	}


	if (isset($_GET['cat']) && is_numeric($_GET['cat']) && $_GET['cat'] > 0)
	{
		$cat_id = (int)$_GET['cat'];
		
		unset($workers_per_category);
		$workers_per_category = array();
		$workers_per_category[] = "111111111111111111111";

		$sub_categories = array();
		$sub_categories = GetSubCategories($cat_id);
		$sub_categories[] = $cat_id;

		$sql_workers_per_category = smart_mysql_query("SELECT worker_id FROM jobfia_worker_skills WHERE skill_id IN (".implode(",",$sub_categories).")");
		if (mysqli_num_rows($sql_workers_per_category) > 0)
		{
			while ($row_workers_per_category = mysqli_fetch_array($sql_workers_per_category))
			{
				$workers_per_category[] = $row_workers_per_category['worker_id'];
			}
			$workers_per_category = array_map('intval', $workers_per_category);
		}
		$where .= "user_id IN (".implode(",",$workers_per_category).") AND ";
	}

	$where .= " status='active'";
	
	$query = "SELECT *, DATE_FORMAT(last_activity, '%b %e, %Y') AS last_activity_date FROM jobfia_users WHERE $where ORDER BY premium DESC, $rrorder $rorder LIMIT $from, $results_per_page";
	$total_result = smart_mysql_query("SELECT * FROM jobfia_users WHERE $where ORDER BY created ASC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Workers";

	require_once ("inc/header.inc.php");

?>

	<section class="job-bg page job-list-page">
		<div class="container">
			<!--
			<div class="breadcrumb-section">
				<ol class="breadcrumb">
					<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
					<li>Workers</li>
				</ol>						
				<h2 class="title">Find Workers</h2>
			</div>
			-->

			<div class="banner-form banner-form-full job-list-form">
				<form action="#">
					<!-- category-change -->
					<div class="dropdown category-dropdown">						
						<a data-toggle="dropdown" href="#"><span class="change-text">Skills</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change">
							<li><a href="#">Facebook</a></li>
							<li><a href="#">Forums</a></li>
							<li><a href="#">Like me</a></li>
							<li><a href="#">Sign up</a></li>
							<li><a href="#">Surveys</a></li>
						</ul>								
					</div><!-- category-change -->
					
					<!-- language-dropdown -->
					<div class="dropdown category-dropdown language-dropdown">
						<a data-toggle="dropdown" href="#"><span class="change-text">Location</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change language-change">
							<!--
							<li><a href="#">Location 1</a></li>
							<li><a href="#">Location 2</a></li>
							<li><a href="#">Location 3</a></li>
							-->
							<?php
			
								$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
								$rs_country = smart_mysql_query($sql_country);
								$total_country = mysqli_num_rows($rs_country);
			
								if ($total_country > 0)
								{
									while ($row_country = mysqli_fetch_array($rs_country))
									{
										if ($country == $row_country['country_id'])
											echo "<li value='".$row_country['country_id']."' selected><a href='#'>".$row_country['name']."</a></li>\n";
										else
											echo "<li value='".$row_country['country_id']."'><a href='#'>".$row_country['name']."</a></li>\n";
									}
								}
			
							?>							
						</ul>								
					</div><!-- language-dropdown -->
				
					<input type="text" class="form-control" placeholder="Type your key word">
					<button type="submit" class="btn btn-primary" value="Search">Search</button>
				</form>
			</div><!-- banner-form -->
	
			<div class="category-info">	
				<div class="row">
					

					<!-- recommended-ads -->
					<div class="col-sm-8 col-md-9">				
						<div class="section job-list-item">
							

							<h1><i class="fa fa-users" style="color: #01a650"></i> Workers</h1>
		
							<div class="featured-top">
								<!--<h4>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?> workers</h4>
								
									<div class="dropdown pull-right">
									<div class="dropdown category-dropdown">
										<h5>Sort by:</h5>						
										<a data-toggle="dropdown" href="#"><span class="change-text">Most Relevant</span><i class="fa fa-caret-square-o-down"></i></a>
										<ul class="dropdown-menu category-change">
											<li><a href="#">Most Relevant</a></li>
											<li><a href="#">Most Popular</a></li>
										</ul>								
									</div>		
								</div>
								-->						
							</div>	


		<?php if (@$search && $total > 0) { ?>
			<p style="background: #f7f7f7; padding: 10px"><span class="results_count"><?php echo number_format($total); ?></span> workers found <?php if (isset($stext) && $stext != "") { ?>for '<b><?php echo $stext; ?></b>'<?php } ?><hr></p>		
		<?php } ?>

		<?php if ($total > 0) { ?>

		<div class="browse_top">
			<div class="sortby">
				<form action="" id="form1" name="form1" method="get">
					<span>Sort by:</span>
					<select name="column" id="column" onChange="document.form1.submit()">
						<option value="satisfied_tasks" <?php if ($_GET['column'] == "satisfied_tasks") echo "selected"; ?>>Rating</option>
						<option value="fname" <?php if ($_GET['column'] == "fname") echo "selected"; ?>>First Name</option>
						<option value="lname" <?php if ($_GET['column'] == "lname") echo "selected"; ?>>Last Name</option>
						<option value="rate" <?php if ($_GET['column'] == "rate") echo "selected"; ?>>Min Rate</option>
						<option value="country" <?php if ($_GET['column'] == "country") echo "selected"; ?>>Country</option>
						<option value="activity" <?php if ($_GET['column'] == "activity") echo "selected"; ?>>Recent Activity</option>
					</select>
					<select name="order" id="order" onChange="document.form1.submit()">
						<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
					&nbsp;&nbsp;<span>View:</span>
				  <select name="show" id="show" onChange="document.form1.submit()">
					<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
					<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
					<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
					<option value="200" <?php if ($_GET['show'] == "200") echo "selected"; ?>>200</option>
				  </select>
					<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
					<?php if ($search) { ?><input type="hidden" name="action" value="search" /><?php } ?>
					<?php if ($stext) { ?><input type="hidden" name="searchtext" value="<?php echo $stext; ?>" /><?php } ?>
					<?php if ($rate_min > 0) { ?><input type="hidden" name="rate_min" value="<?php echo $rate_min; ?>" /><?php } ?>
					<?php if ($rate_max > 0) { ?><input type="hidden" name="rate_max" value="<?php echo $rate_max; ?>" /><?php } ?>
					<?php if ($sactive == 1) { ?><input type="hidden" name="sactive" value="1" /><?php } ?>
					<?php if ($spremium == 1) { ?><input type="hidden" name="spremium" value="1" /><?php } ?>
					<?php if ($sonline == 1) { ?><input type="hidden" name="sonline" value="1" /><?php } ?>
					<?php if ($page > 0) { ?><input type="hidden" name="page" value="<?php echo $page; ?>" /><?php } ?>
				</form>
			</div>
			<div class="results pull-right">
				Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?> workers
			</div>
		</div>
		<hr>

            <form id="form2" name="form2" method="post" action="">
	        <div class="table-responsive">
			<table class="table table-striped table-hover" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%" style="padding-left: 10px"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></th>
				<th width="13%">&nbsp;</th>
                <th width="25%">Name</th>
				<th width="15%">Min Reward</th>
				<th width="17%">Avg Job Price</th>
				<th width="15%">Work Done</th>
				<th width="18%">Last Active</th>
				<th width="15%">Actions</th>
              </tr>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if ($row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
                  <td nowrap="nowrap" align="center" valign="middle"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
				  <td nowrap="nowrap" valign="middle" align="center">
					<a href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $row['user_id']; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$row['avatar']; ?>" width="<?php echo AVATAR_WIDTH; ?>" height="<?php echo AVATAR_HEIGHT; ?>" class="imgs" border="0" style="border-radius: 20%" /></a>
					<?php if ($row['premium'] == 1) { ?><br/><span class="premium"><small><i class="fa fa-user"></i> premium</small></span><?php } ?>					
					<div style="clear: both"></div>
					<?php $user_online = 0; if (ifUserOnline($row['user_id'])) { $user_online = 1; ?><small><span class="online"><i class="fa fa-circle Blink"></i> online</span></small><?php } ?>
					<?php echo GetWorkerRating($row['user_id'], $show_stars = 1); ?>
					<br>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left">
					<a class="user_title" href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $row['user_id']; ?>"><b><?php echo $row['username']; //echo $row['fname']." ".substr($row['lname'], 0, 1)."."; ?></b></a>
					<br/><?php echo GetCountry($row['country_id']); ?>
				  </td>
				   <td nowrap="nowrap" valign="middle" align="left"><?php echo ($row['rate'] != "0.0000") ? DisplayMoney($row['rate']) : "---"; ?></td>
				  <td nowrap="nowrap" valign="middle" align="left"><?php echo GetWorkerJobPrice($row['user_id']); ?></td>
				  <td nowrap="nowrap" valign="middle" align="left">
					<span class="acount total jobfia_tooltip" title="Total tasks"><?php echo GetWorkerJobsTotal($row['user_id']); ?></span>
					<span class="acount success jobfia_tooltip" title="Successful tasks"><?php echo GetWorkerPaidJobs($row['user_id']); ?></span>
					<span class="acount unsuccess jobfia_tooltip" title="Unsuccessful tasks"><?php echo GetWorkerDeclinedJobs($row['user_id']); ?></span>
                  </td>
                  <td nowrap="nowrap" valign="middle" align="left">
	                <?php if ($user_online == 1) { ?>
	                	<small><span class="online"><i class="fa fa-circle Blink"></i> online</span></small>
					<?php }elseif ($row['login_count'] > 0) { ?>
						<!--<?php echo $row['last_activity_date']; ?> <br/>--><small><?php echo relative_date(strtotime($row['last_activity'])); ?></small>
					<?php }else{ ?>
						<small><?php echo relative_date(strtotime($row['created'])); ?></small>
					<?php } ?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="left">
					<a class="badge" style="background: #01a650; font-size: 12px" href="<?php echo SITE_URL; ?>hire.php?id=<?php echo $row['user_id']; ?>"><i class="fa fa-handshake-o"></i> Hire me</a><br/>
					<a class="badge" style="background: #2995c4; font-size: 12px" href="<?php echo SITE_URL; ?>myworkers.php?id=<?php echo $row['user_id']; ?>&act=add"><i class="fa fa-plus"></i> Add to My Workers</a><br/>
				  </td>
                </tr>
			<?php } ?>
				<tr>
					<td colspan="8" style="border-top: solid 1px #F7F7F7;" align="left">
						<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
						<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="hidden" name="action" value="add_workers" />
						<input type="submit" class="btn btn-success" name="Hire" id="Hire" value="Hire Selected Workers" />
					</td>
				</tr>
			</table>
	        </div>
			</form>

					<?php
							$params = "";
							if ($cat_id)			$params .= "cat=$cat_id&";
							if ($search == 1)		$params .= "action=search&";
							if ($stext)				$params .= "searchtext=$text&";
							if ($rate_min > 0)		$params .= "rate_min=$rate_min&";
							if ($rate_max > 0)		$params .= "rate_max=$rate_max&";
							if ($sactive == 1)		$params .= "sactive=1&";
							if ($spremium == 1)		$params .= "spremium=1&";
							if ($sonline == 1)		$params .= "sonline=1&";

							echo ShowPagination("users",$results_per_page,"workers.php?".$params."column=$rrorder&order=$rorder&show=$results_per_page&","WHERE ".$where);
					?>
					
							<!--
							<div class="text-center">
								<ul class="pagination ">
									<li><a href="#"><i class="fa fa-chevron-left"></i></a></li>
									<li><a href="#">1</a></li>
									<li class="active"><a href="#">2</a></li>
									<li><a href="#">3</a></li>
									<li><a href="#">4</a></li>
									<li><a href="#">5</a></li>
									<li><a>...</a></li>
									<li><a href="#">10</a></li>
									<li><a href="#">20</a></li>
									<li><a href="#">30</a></li>
									<li><a href="#"><i class="fa fa-chevron-right"></i></a></li>
								</ul>
							</div>
							-->						
					

	<?php }else{ ?>
			<?php if (@$search) { ?>
				<div class="alert alert-info text-center"><i class="fa fa-search"></i> No workers found with your search criteria.</div>
			<?php }else{ ?>
				<div class="alert alert-info text-center">No workers found at this time.</div>
			<?php } ?>
	<?php } ?>

							
							
			
						</div>
					</div><!-- recommended-ads -->
					
					
					
<div class="col-md-3 col-sm-4">
						


		<div style="background: #F7F7F7; border: 1px solid #EDEDED; margin: 8px 0; border-radius: 8px; padding: 5px 10px;">
		<h3><i class="fa fa-search"></i> Search for workers</h3>
		<?php if (@$search) { ?><div align="right"><a class="badge" href="workers.php"><i class="fa fa-times"></i> delete filter</a></div><?php } ?>
		<form method="get" id="filterform">
		<table align="center" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td nowrap="nowrap" align="left" valign="middle"><input type="text" name="searchtext" id="searchtext" placeholder="keywords..." class="form-control" value="<?php if (isset($stext)) echo $stext; ?>" size="25" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap" align="left" valign="middle">
				<select name="country" class="form-control" id="country" style="margin: 7px 0">
				<option value="">-- all countries --</option>
				<?php

					$sql_country = "SELECT * FROM jobfia_countries ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($country == $row_country['country_id'])
								echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
							else
								echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
						}
					}

				?>
				</select>			
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" align="left" valign="middle">
				<select name="cat" id="cat" class="form-control" style="margin: 7px 0">
					<option value="">-- all skills --</option>
					<?php ShowCategoriesDropDown(0); ?>
				</select>
			</td>
		</tr>
		<tr>
            <td colspan="2"nowrap="nowrap" align="left" valign="middle">Rate: 
	            
	            <div class="row">
		        <div class="col-sm-6">
	            <div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
						<input type="text" name="rate_min" id="rate_min" class="form-control" value="<?php if (isset($rate_min)) echo $rate_min; ?>"  /> &nbsp;&nbsp; <span style="margin-top: 15px">-</span>
	            </div>
	            </div>
	            <div class="col-sm-6">
  	            <div class="input-group"> 
						<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>
						<input type="text" name="rate_max" id="rate_max" class="form-control" value="<?php if (isset($rate_max)) echo $rate_max; ?>" />
 				</div>
	            </div>
	            </div>
 
 </td>
		</tr>
		<tr>
			<td align="left" valign="middle">
				<div class="checkbox">
				 <label class="<?php if (@$sactive == 1) echo "checked"; ?>"><input type="checkbox" name="sactive" id="sactive" class="checkboxx" value="1" <?php if (@$sactive == 1) echo "checked='checked'"; ?> /> <span style="color: #3d87c7"><i class="fa fa-user"></i></span> active workers <sup class="jobfia_tooltip" title="worker complete work in last week">?</sup></label><br/>
				 <?php if (PREMIUM_ACCOUNTS == 1) { ?><label class="<?php if (@$spremium == 1) echo "checked"; ?>"><input type="checkbox" name="spremium" id="spremium" class="checkboxx" value="1" <?php if (@$spremium == 1) echo "checked='checked'"; ?> /> <span style="color: #F49C04"><i class="fa fa-user"></i> premium workers</span></label><br/><?php } ?>
				 <label class="<?php if (@$sonline == 1) echo "checked"; ?>"><input type="checkbox" name="sonline" id="sonline" class="checkboxx" value="1" <?php if (@$sonline == 1) echo "checked='checked'"; ?> /> <span style="color: #34ec12"><i class="fa fa-user"></i></span> <!--<img src="<?php echo SITE_URL; ?>images/icon_online.png" align="absmiddle" />--> online workers</label>
				</div>
			</td>
		</tr>
		<tr>
			<td align="left" valign="middle">
				<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
				<input type="hidden" name="action" id="action" value="search" />
				<input type="submit" class="btn btn-success" value="Search" />
			</td>
		</tr>
		</table>
		</form>
		</div>


<!--
		<div class="cats">
		<a <?php if (!$cat_id && !$search) echo "class='cat_link_active'"; else echo "class='cat_link'"; ?> href="<?php echo SITE_URL; ?>workers.php"><b>All Workers</b> (<?php echo GetWorkersTotal(); ?>)</a><br/><br/>
				<?php

					$sql_cats = "SELECT * FROM jobfia_categories WHERE parent_id='0' ORDER BY category_id";
					$rs_cats = smart_mysql_query($sql_cats);
					$total_cats = mysqli_num_rows($rs_cats);

					if ($total_cats > 0)
					{
				?>
				<ul id="cats">
				<?php
					while ($row_cats = mysqli_fetch_array($rs_cats))
					{
						if ($cat_id == $row_cats['category_id'])
							echo "<li><a class='cat_link_active' href='".SITE_URL."workers.php?cat=".$row_cats['category_id']."'>".$row_cats['name']."</a></li>";
						else
							echo "<li><a class='cat_link' href='".SITE_URL."workers.php?cat=".$row_cats['category_id']."'>".$row_cats['name']."</a></li>";
					}
				?>
				</ul>
				<?php } ?>
		</div>						
-->						
						
						
						
						
						<div class="accordion">
							<!-- panel-group -->
							<div class="panel-group" id="accordion">
							 	
								<!-- panel -->
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div  class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-one">
												<h4>Categories<span class="pull-right"><i class="fa fa-minus"></i></span></h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-one" class="panel-collapse collapse in">
										<!-- panel-body -->
										<div class="panel-body">
											<h5><a href="<?php echo SITE_URL; ?>workers.php"><i class="fa fa-caret-down"></i> <i class="fa fa-users"></i> All Workers  <span>(<?php echo GetWorkersTotal(); ?>)</span></a></h5>
											<ul>
												<?php
													
												$cresult = smart_mysql_query("SELECT * FROM jobfia_categories WHERE parent_id='0' ORDER BY name");
												$ctotal = mysqli_num_rows($cresult);
										
												if ($ctotal > 0)
												{
													while ($crow = mysqli_fetch_array($cresult))
													{
														$category_id	= $crow['category_id'];
														$category_name	= $crow['name'];
														
														if ($parent > 0 && $category_id == $parent) $selected = " selected=\"selected\""; else $selected = "";
														if ($category_id == (int)$_REQUEST['cat'] || $category_id == (int)$_REQUEST['category']) $selected = " selected=\"selected\""; else $selected = "";
														
														echo "<li><a href=\"".SITE_URL."workers.php?cat=".$category_id."\"".$selected.">".$sep.$category_name."</a></li>\n";
													}
												}
												?>
											</ul>
											<!--
											<div class="see-more">
												<button type="button" class="show-more one"><i class="fa fa-plus-square-o" aria-hidden="true"></i>See More</button>
												<ul class="more-category one">
													<li><a href="#">Back end developer<span>(5402)</span></a></li>
													<li><a href="#">IT Department Manager<span>(3829)</span></a></li>
													<li><a href="#">QA Department Manager<span>(352)</span></a></li>
												</ul>
											</div>
											-->

										</div><!-- panel-body -->
									</div>
								</div><!-- panel -->




							<?php
					
								$top_workers_query = "SELECT jobfia_job_tasks.*, SUM(jobfia_job_tasks.job_price) as total_earnings, jobfia_users.* FROM jobfia_job_tasks jobfia_job_tasks, jobfia_users jobfia_users WHERE jobfia_job_tasks.worker_id=jobfia_users.user_id AND jobfia_job_tasks.status='confirmed' AND jobfia_job_tasks.added BETWEEN DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') AND LAST_DAY(CURRENT_DATE) AND jobfia_users.worker='1' AND jobfia_users.status='active' GROUP BY jobfia_users.user_id ORDER BY total_earnings DESC LIMIT 10";
								$top_workers_result = smart_mysql_query($top_workers_query);
								$top_workers_total = mysqli_num_rows($top_workers_result);
								$c = 0;
					
								if ($top_workers_total > 0) {
							?>
								<div class="section text-center">
								<img src="<?php echo SITE_URL; ?>images/icon_top.png" align="absmiddle" /><br>
								<h4><b>Top Workers</b></h4>
								<hr>
									<ul id="top_workers">
									<?php while ($top_workers_row = mysqli_fetch_array($top_workers_result)) { $c++; ?>
										<li>
											<a href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $top_workers_row['worker_id']; ?>"><img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$top_workers_row['avatar']; ?>" width="40" height="40" alt="" align="left" style="margin-right: 3px; border-radius: 50%;" border="0"  class="img-responsive" /></a>
											<?php echo GetCountry($top_workers_row['country_id'], 2); ?> <a href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $top_workers_row['worker_id']; ?>"><b><?php echo $top_workers_row['fname']." ".substr($top_workers_row['lname'], 0, 1)."."; ?></b></a>
											<?php if (ifUserOnline($top_workers_row['user_id'])) { ?><br><small><span class="online"><i class="fa fa-circle Blink"></i> online</span></small><br><?php } ?>
											<br>
											<i class="fa fa-star" style="color: #ffad16"></i>
											<i class="fa fa-star" style="color: #ffad16"></i>
											<i class="fa fa-star" style="color: #ffad16"></i>
											<i class="fa fa-star" style="color: #ffad16"></i>
											<i class="fa fa-star" style="color: #ffad16"></i>
											<hr>
										</li>
									<?php } ?>
									</ul>
									<a href="<?php echo SITE_URL; ?>top_workers.php"><span class="label label-primary"><i class="fa fa-users"></i> See All</span></a>
								</div>
							<?php } ?>

								<!-- panel -->
								<?php /* ?>
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-three">
												<h4>
												Salary Range
												<span class="pull-right"><i class="fa fa-plus"></i></span>
												</h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-three" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<div class="price-range"><!--price-range-->
												<div class="price">
													<span>$100 - <strong>$700</strong></span>
													<div class="dropdown category-dropdown pull-right">	
														<a data-toggle="dropdown" href="#"><span class="change-text">USD</span><i class="fa fa-caret-square-o-down"></i></a>
														<ul class="dropdown-menu category-change">
															<li><a href="#">USD</a></li>
															<li><a href="#">AUD</a></li>
															<li><a href="#">EUR</a></li>
															<li><a href="#">GBP</a></li>
															<li><a href="#">JPY</a></li>
														</ul>								
													</div><!-- category-change -->													
													 <input type="text" value="" data-slider-min="0" data-slider-max="700" data-slider-step="5" data-slider-value="[250,450]" id="price" ><br />
												</div>
											</div><!--/price-range-->
										</div><!-- panel-body -->
									</div>
								</div><!-- panel -->

								<!-- panel -->
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-seven">
												<h4>Location<span class="pull-right"><i class="fa fa-plus"></i></span></h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-seven" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<input type="text" placeholder="Search Location" class="form-control">
											<label for="angeles"><input type="checkbox" name="angeles" id="angeles"> Los Angeles, CA</label>
											<label for="kingdom"><input type="checkbox" name="kingdom" id="kingdom"> United Kingdom</label>
											<label for="states"><input type="checkbox" name="states" id="states"> United States</label>
											<label for="columbia"><input type="checkbox" name="columbia" id="columbia"> British Columbia</label>
											<label for="australia"><input type="checkbox" name="australia" id="australia"> Australia</label>
											<label for="germany"><input type="checkbox" name="germany" id="germany"> Germany</label>
											<div class="see-more">
												<button type="button" class="show-more three"><i class="fa fa-plus-square-o" aria-hidden="true"></i>See More</button>
												<div class="more-category three">
													<label for="belgium"><input type="checkbox" name="belgium" id="belgium">Belgium</label>
													<label for="brazil"><input type="checkbox" name="brazil" id="brazil">Brazil</label>
													<label for="denmark"><input type="checkbox" name="denmark" id="denmark">Denmark</label>
													<label for="indonesia"><input type="checkbox" name="indonesia" id="indonesia">Indonesia</label>
												</div>
											</div>											
										</div><!-- panel-body -->
									</div>
								</div> <!-- panel -->
								<?php */ ?>

							 </div><!-- panel-group -->
						</div>
					</div><!-- accordion-->					
					


				</div>	
			</div>
		</div><!-- container -->
	</section><!-- main -->
	
	
	<?php if (!isLoggedIn()) { ?>
	<section id="something-sell" class="clearfix parallax-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2 class="title">Sign up today and start to post and do jobs.</h2>
					<h4>We have many great jobs for you. And thousands of workers are waiting for you.</h4>
					<a href="<?php echo SITE_URL; ?>signup.php" class="btn btn-primary">Join today for free!</a>
				</div>
			</div>
		</div>
	</section>
	<?php } ?>


		


<?php require_once ("inc/footer.inc.php"); ?>