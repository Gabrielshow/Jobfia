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
	require_once("inc/pagination.inc.php");


	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0 && in_array($_GET['show'], $results_on_page))
	{
		$results_per_page = (int)$_GET['show'];
		if (!(isset($_GET['go']) && $_GET['go'] == 1)) $page = 1;
	}
	else
	{
		$results_per_page = RESULTS_PER_PAGE;
	}

	$cc = 0;


	function getCategory($category_id, $show_description = 0)
	{
		if (isset($category_id) && is_numeric($category_id) && $category_id != 0)
		{
			$query = "SELECT name, description FROM jobfia_categories WHERE category_id='".(int)$category_id."'";
			$result = smart_mysql_query($query);
			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				if ($show_description == 1) return $row['description']; else return $row['name'];
			}
			else
			{
				return "Category not found";
			}
		}
		else
		{
			if ($show_description != 1) return "All Offers";
		}
	}

	////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "added": $rrorder = "added"; break;
				case "views": $rrorder = "views"; break;
				case "price": $rrorder = "price"; break;
				case "duration": $rrorder = "duration"; break;
				case "positions": $rrorder = "positions"; break;
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
	//////////////////////////////////////////////////

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	
	$from = ($page-1)*$results_per_page;
	$where = " job_type='offer' AND ";


	// SEARCH JOBS //
	if (isset($_GET['action']) && $_GET['action'] == "search")
	{
		$search			= 1;
		$stext			= mysqli_real_escape_string($conn, getGetParameter('searchtext'));
		$stext			= substr(trim($stext), 0, 80);
		$category		= mysqli_real_escape_string($conn, getGetParameter('category'));
		$reward			= mysqli_real_escape_string($conn, getGetParameter('reward'));
		$country		= mysqli_real_escape_string($conn, getGetParameter('country'));
		$sfeatured		= (int)getGetParameter('sfeatured');
		$spremium		= (int)getGetParameter('spremium');

		if ($stext) $where .= " (title LIKE '%".$stext."%' OR description LIKE '%".$stext."%') AND ";
		
		if (is_numeric($reward) && $reward > 0)
		{
			$reward = (float)$reward;
			$where .= " price >= '$reward' AND ";
		}
		else
		{
			$reward = "";
		}

		if ($country > 0)
		{
			$jobs_per_country = array();
			$jobs_per_country[] = "111111111111";
			$sql_jobs_per_country = smart_mysql_query("SELECT job_id FROM jobfia_job_to_country WHERE country_id = '".(int)$country."' OR country_id='111111'");
			if (mysqli_num_rows($sql_jobs_per_country) > 0)
			{
				while ($row_jobs_per_country = mysqli_fetch_array($sql_jobs_per_country))
				{
					$jobs_per_country[] = $row_jobs_per_country['job_id'];
				}
				$jobs_per_country = array_map('intval', $jobs_per_country);
				$jobs_per_country = array_unique($jobs_per_country);
			}
			$where .= "job_id IN (".implode(",",$jobs_per_country).") AND ";
		}

		if ($sfeatured == 1)	$where .= " featured='1' AND ";
		if ($spremium == 1)		$where .= " premium='1' AND ";
	}


	if (isset($_GET['cat']) && is_numeric($_GET['cat']) && $_GET['cat'] > 0)
	{
		$cat_id = (int)$_GET['cat'];
		
		unset($jobs_per_category);
		$jobs_per_category = array();
		$jobs_per_category[] = "111111111111";

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


	if (isLoggedIn())
	{
		// update user activity time
		UpdateUserActivity($userid);
	}

	$where .= " status='active'";

	$query = "SELECT * FROM jobfia_jobs WHERE $where ORDER BY featured DESC, $rrorder $rorder LIMIT $from, $results_per_page";
	$total_result = smart_mysql_query("SELECT * FROM jobfia_jobs WHERE $where ORDER BY title ASC");
	$total = mysqli_num_rows($total_result);

	$result = smart_mysql_query($query);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = getCategory($_GET['cat']);
	
	if (!isLoggedIn() && SHOW_JOBS_UNREG == 0) $single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<?php if (isLoggedIn() || SHOW_JOBS_UNREG == 1) { ?>

<section class="job-bg page job-list-page">
		<div class="container">
			<!--
			<div class="breadcrumb-section">
				<ol class="breadcrumb">
					<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
					<li>Offers</li>
				</ol>					
				<h2 class="title">Offers</h2>
			</div>
			-->

			<div class="banner-form banner-form-full job-list-form">
				<form action="#">
					<!-- category-change -->
					<div class="dropdown category-dropdown">						
						<a data-toggle="dropdown" href="#"><span class="change-text">Offer Category</span> <i class="fa fa-angle-down"></i></a>
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
						<a data-toggle="dropdown" href="#"><span class="change-text">Offer Location</span> <i class="fa fa-angle-down"></i></a>
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
				
					<input type="text" class="form-control" placeholder="Type your keyword">
					<button type="submit" class="btn btn-primary" value="Search">Search</button>
				</form>
			</div><!-- banner-form -->
	
			<div class="category-info">	
				<div class="row">
					<div class="col-md-3 col-sm-4">
						
						<center><a href="<?php SITE_URL; ?>offer_create.php" class="btn btn-success btn-lg btn-block"><i class="fa fa-plus"></i> Post an Offer</a></center>


		<div style="background: #FFF; border: 1px solid #ddd; margin: 8px 0; border-radius: 5px; padding: 5px 10px;">
			<h3><i class="fa fa-search"></i> Search for offers</h3>
			<?php if (@$search) { ?><div align="right"><a class="remove" href="<?php echo SITE_URL; ?>offers.php"><span class="badge">x delete filter</span></a></div><?php } ?>
			<form method="get" id="filterform">
			<table align="center" cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td nowrap="nowrap" align="left" valign="middle"><input type="text" name="searchtext" id="searchtext" placeholder="keywords..." class="form-control" value="<?php if (isset($stext)) echo $stext; ?>" /></td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="left" valign="middle">
					<select name="cat" id="cat" class="form-control" style="margin: 7px 0">
						<option value="">All categories</option>
						<?php ShowCategoriesDropDown (0); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="left" valign="middle">
					<select name="country" class="form-control" id="country" style="margin: 7px 0">
					<option value="">All countries</option>
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
		            Price Range: 
			            <div class="input-group"> 
							<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>	
							<input type="text" name="reward" id="reward" class="form-control" value="<?php if (isset($reward)) echo $reward; ?>" placeholder="0.00" />
			            </div>
		            </td>
			</tr>
			<tr>
				<td align="left" valign="middle">
					<div class="checkbox">
						<label class="<?php if (@$sfeatured == 1) echo "checked"; ?>"><input type="checkbox" name="sfeatured" id="sfeatured" value="1" <?php if (@$sfeatured == 1) echo "checked='checked'"; ?> /> <i class="fa fa-star" style="color: #40b922"></i> only featured offers</label>
					</div>
				</td>
			</tr>
			<tr>
				<td align="left" valign="top">
					<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
					<input type="hidden" name="action" id="action" value="search" />
					<input type="submit" class="btn btn-success" value="Search" />
				</td>
			</tr>
			</table>
			</form>
		</div>




		<?php if (SHOW_FB_LIKEBOX == 1 && FACEBOOK_PAGE != "") { ?>
		<div style="background: #FFF; border: 1px solid #EDEDED; margin: 8px 0; border-radius: 5px; padding: 5px 10px;">
				<iframe src="//www.facebook.com/plugins/likebox.php?href=<?php echo urlencode(FACEBOOK_PAGE); ?>&amp;width=185&amp;height=300&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:185px; height:300px;" allowTransparency="true"></iframe>
		</div>
		<?php } ?>

			
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
											<h5><a href="<?php echo SITE_URL; ?>offers.php"><i class="fa fa-caret-down"></i> All Offers</a></h5>
					
											<!--<a <?php echo (!$cat_id && !$search) ? "class='cat_link_active'" : "class='cat_link'"; ?> href="<?php echo SITE_URL; ?>offers.php"><b>All Offers</b></a>-->
											<ul>
												<?php
													$cresult = smart_mysql_query("SELECT * FROM jobfia_categories WHERE parent_id='0'  ORDER BY name");
													$ctotal = mysqli_num_rows($cresult);
											
													if ($ctotal > 0)
													{
														while ($row_cats = mysqli_fetch_array($cresult))
														{
															if ($cat_id == $row_cats['category_id'])
																echo "<li><a class='cat_link_active' href='".SITE_URL."offers.php?cat=".$row_cats['category_id']."'>".$row_cats['name']." (".CategoryTotalJobs($row_cats['category_id']).")</a></li>";
															else
																echo "<li><a class='cat_link' href='".SITE_URL."offers.php?cat=".$row_cats['category_id']."'>".$row_cats['name']."  (".CategoryTotalJobs($row_cats['category_id']).")</a></li>";
														}
													}
												?>
											</ul>
											<!--
											<div class="see-more">
												<button type="button" class="show-more one"><i class="fa fa-plus-square-o" aria-hidden="true"></i>See More</button>
												<ul class="more-category one">
													<li><a href="#">Fron end developer<span>(289)</span></a></li>
													<li><a href="#">Back end developer<span>(5402)</span></a></li>
												</ul>
											</div>
											-->

										</div><!-- panel-body -->
									</div>
								</div><!-- panel -->

								<?php /* ?>
								<!-- panel -->
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div  class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-two">
												<h4>Date Posted <span class="pull-right"><i class="fa fa-plus"></i></span></h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-two" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<label for="today"><input type="checkbox" name="today" id="today"> Today</label>
											<label for="7-days"><input type="checkbox" name="7-days" id="7-days"> 7 days</label>
											<label for="30-days"><input type="checkbox" name="30-days" id="30-days"> 30 days</label>
										</div><!-- panel-body -->
									</div>
								</div><!-- panel -->

								<!-- panel -->
								<div class="panel panel-default panel-faq">
									
									<div class="panel-heading">
										<div class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-three">
												<h4>
												Salary Range
												<span class="pull-right"><i class="fa fa-plus"></i></span>
												</h4>
											</a>
										</div>
									</div>

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
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-four">
												<h4>Employment Type<span class="pull-right"><i class="fa fa-plus"></i></span></h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-four" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<label for="full-time"><input type="checkbox" name="full-time" id="full-time"> Full Time</label>
											<label for="part-time"><input type="checkbox" name="part-time" id="part-time"> Part Time</label>
											<label for="contractor"><input type="checkbox" name="contractor" id="contractor"> Contractor</label>
											<label for="intern"><input type="checkbox" name="intern" id="intern"> Intern</label>
											<label for="seasonal"><input type="checkbox" name="seasonal" id="seasonal"> Seasonal / Temp</label>
										</div><!-- panel-body -->
									</div>
								</div><!-- panel -->

								<!-- panel -->
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#accordion-five">
												<h4>Experience Level<span class="pull-right"><i class="fa fa-plus"></i></span></h4>
											</a>
										</div>
									</div><!-- panel-heading -->

									<div id="accordion-five" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<label for="training"><input type="checkbox" name="training" id="training"> Training</label>
											<label for="entry-level"><input type="checkbox" name="entry-level" id="entry-level"> Entry Level</label>
											<label for="mid-senior"><input type="checkbox" name="mid-senior" id="mid-senior"> Mid-Senior Level</label>
											<label for="top-level"><input type="checkbox" name="top-level" id="top-level"> Top Level</label>
										</div><!-- panel-body -->
									</div>
								</div> <!-- panel -->

								<!-- panel -->
								<div class="panel panel-default panel-faq">
									<!-- panel-heading -->
									<div class="panel-heading">
										<div class="panel-title"></div>
										<a data-toggle="collapse" data-parent="#accordion" href="#accordion-six">
											<h4>Company<span class="pull-right"><i class="fa fa-plus"></i></span></h4>
										</a>
									</div><!-- panel-heading -->

									<div id="accordion-six" class="panel-collapse collapse">
										<!-- panel-body -->
										<div class="panel-body">
											<input type="text" placeholder="Search Company" class="form-control">
											<label for="apple"><input type="checkbox" name="apple" id="apple"> Apple</label>
											<label for="dropbox"><input type="checkbox" name="dropbox" id="dropbox"> Dropbox</label>
											<label for="micromax"><input type="checkbox" name="micromax" id="micromax"> Micromax</label>
											<label for="nokia"><input type="checkbox" name="nokia" id="nokia"> Nokia</label>
											<label for="microsoft"><input type="checkbox" name="microsoft" id="microsoft"> Microsoft</label>
											<label for="samsung"><input type="checkbox" name="samsung" id="samsung"> Samsung</label>
											<div class="see-more">
												<button type="button" class="show-more two"><i class="fa fa-plus-square-o" aria-hidden="true"></i>See More</button>
												<div class="more-category two">
													<label for="blackBerry"><input type="checkbox" name="blackBerry" id="blackBerry">BlackBerry</label>
													<label for="motorola"><input type="checkbox" name="motorola" id="motorola">Motorola</label>
													<label for="lenovo"><input type="checkbox" name="lenovo" id="lenovo">Lenovo</label>
												</div>
											</div>											
										</div><!-- panel-body -->
									</div>
								</div> <!-- panel -->

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

					<!-- recommended-ads -->
					<div class="col-sm-8 col-md-9">				
						<div class="section job-list-item">

		<?php if (@$search && $total > 0) { ?>
			<h1>Search results</h1>
			<p><span class="results_count"><?php echo number_format($total); ?></span> offers found <?php if (isset($stext) && $stext != "") { ?>for '<b><?php echo $stext; ?></b>'<hr></p><?php } ?>
		<?php }else{ ?>
			<h1><i class="fa fa-list" style="color: #01a650"></i> <?php echo getCategory($_GET['cat']); ?></h1>
		<?php } ?>
							<!--
							<div class="featured-top">
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
							</div>
							-->

		<p class="category_description"><?php echo getCategory($_GET['cat'], 1); ?></p>

		<?php if ($total > 0) { ?>
		<div class="browse_top">
			<div class="sortby">
				<form action="" id="form1" name="form1" method="get">
					<span>Sort by:</span>
					<select name="column" id="column" onChange="document.form1.submit()">
						<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
						<option value="views" <?php if ($_GET['column'] == "views") echo "selected"; ?>>Popularity</option>
						<option value="price" <?php if ($_GET['column'] == "price") echo "selected"; ?>>Price</option>
					</select>
					<select name="order" id="order" onChange="document.form1.submit()">
						<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
					&nbsp;&nbsp;<span>View:</span>
					<select name="show" id="show" onChange="document.form1.submit()">
						<option value="10" <?php if ($results_per_page == "10") echo "selected"; ?>>10</option>
						<option value="20" <?php if ($results_per_page == "20") echo "selected"; ?>>20</option>
						<option value="50" <?php if ($results_per_page == "50") echo "selected"; ?>>50</option>
						<option value="100" <?php if ($results_per_page == "100") echo "selected"; ?>>100</option>
						<option value="111111" <?php if ($results_per_page == "111111") echo "selected"; ?>>All</option>
					</select>
					<?php if ($cat_id) { ?><input type="hidden" name="cat" value="<?php echo $cat_id; ?>" /><?php } ?>
					<?php if ($search) { ?><input type="hidden" name="action" value="search" /><?php } ?>
					<?php if ($stext) { ?><input type="hidden" name="searchtext" value="<?php echo $stext; ?>" /><?php } ?>
					<?php if ($reward > 0) { ?><input type="hidden" name="reward" value="<?php echo $reward; ?>" /><?php } ?>
					<?php if ($spremium == 1) { ?><input type="hidden" name="spremium" value="1" /><?php } ?>
					<?php if ($sfeatured == 1) { ?><input type="hidden" name="sfeatured" value="1" /><?php } ?>
					<?php if ($page > 0) { ?><input type="hidden" name="page" value="<?php echo $page; ?>" /><?php } ?>
				</form>
			</div>
			<div class="results pull-right">
				<a href="<?php echo SITE_URL; ?>rss.php?show=offers<?php echo (isset($cat_id)) ? "&cat=".$cat_id : ""; ?>"><img src="<?php echo SITE_URL; ?>images/rss_icon.png" align="absmiddle" /></a> &nbsp; &nbsp; Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?> offers
			</div>
		</div>
		<hr>
		<?php } ?>

			<div class="row">
			
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				<div class="col-md-4">

							<div class="job-ad-item <?php if ($row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
								<div class="item-info">
									<div class="item-image-box2">
										<div class="text-center">
											<a href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $row['job_id']; ?>"><img src="<?php echo SITE_URL; ?>images/offers/<?php echo ($row['image'] != "") ? $row['image'] : "no_image.png"; ?>" alt=""  height="150" style="margin: 5px 0;"></a>
										</div><!-- item-image -->
									</div>

									<div class="offer-info">

									  <?php echo GetUserAvatar($row['user_id'], 33); ?>		  
									  by <?php echo GetEmployerInfo($row['user_id'], $username = 1); ?><br><br>
										
										<?php if ($row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
										<a class="job_title" href="<?php echo SITE_URL; ?>offer_details.php?id=<?php echo $row['job_id']; ?>">
											<h4>I will <?php if (strlen($row['title']) > 75) $job_title = substr($row["title"], 0, 70)."..."; else $job_title = $row["title"]; echo $job_title; ?></h4>
										</a>	
									  <br><i class="fa fa-star fa-lg" style="color: #ffc101"></i> <b>5.0</b> (0<?php //echo GetOfferFeedbacksTotal($row['job_id']); ?>)
									  &nbsp;&nbsp;&nbsp;<a href="<?php echo SITE_URL; ?>myoffers.php?id=<?php echo $row['job_id']; ?>&act=save"><i class="fa fa-heart jobfia_tooltip" title="Add to Saved" style="color: #ae3535"></i></a>
									  <hr>
									  <div class="pull-left"><a href="<?php echo SITE_URL; ?>order.php?id=<?php echo $row['job_id']; ?>" class="btn btn-success"><i class="fa fa-shopping-cart"></i> order</a></div>
									  <div class="pull-right" style="padding-top: 5px"><span style="font-size: 11px; color: #555; text-transform: uppercase; text-align: right">starting at</span> <span style="font-size: 19px; color: #4ea204"><?php echo DisplayMoney($row['price']); ?></span></div>
									  <div style="clear: both"></div>
									 	<?php if ($row['country'] != "") { ?>					
											<div class="ad-meta">
												<ul>
													<li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i>San Francisco, CA, US </a></li>
												</ul>
											</div>
										<?php } ?>							
									</div><!-- ad-info -->
								</div><!-- item-info -->
							</div><!-- job-ad-item -->                
                
				</div>
			<?php } ?>
			
			</div>

					<?php
							$params = "";
							if ($cat_id)				$params .= "cat=$cat_id&";
							if ($search == 1)			$params .= "action=search&";
							if ($stext)					$params .= "searchtext=$text&";
							if ($reward > 0)			$params .= "reward=$reward&";
							if ($spremium == 1)			$params .= "spremium=1&";
							if ($sfeatured == 1)		$params .= "sfeatured=1&";

							echo ShowPagination("jobs",$results_per_page,"offers.php?".$params."column=$rrorder&order=$rorder&show=$results_per_page&","WHERE ".$where);
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
						<div class="alert alert-info text-center">There are no offers found with your search criteria.</div>
					<?php }else{ ?>
						<div class="alert alert-info text-center">There are no offers available.</div>
					<?php } ?>

			<?php } ?>
			
							
						</div>
					</div><!-- recommended-ads -->

					
				</div>	
			</div>
		</div><!-- container -->
	</section><!-- main -->


	<?php }else{ ?>
		<h1><i class=" fa fa-list"></i> Offers</h1>
		<div class="alert alert-info text-center"><i class="fa fa fa-eye-slash fa-5x"></i><br> You need <a href="<?php echo SITE_URL; ?>signup.php">signup</a> or <a href="<?php echo SITE_URL; ?>login.php">login</a> to see offers list.</div>
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>