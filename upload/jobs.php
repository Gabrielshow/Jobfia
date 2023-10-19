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
	
	//Bold USD0.11	  Home Page Post USD0.20	  Highlighted USD0.10 //dev


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
			if ($show_description != 1) return "All Jobs";
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
	$where = " job_type='job' AND ";


	// show hidden jobs
	if (isset($_GET['clear']))
	{
		unset($_SESSION['not_interested_jobs']);
		//header ("Location: jobs.php");
		//exit();
	}

	///// Hide 'not interested' jobs /////
	if (isset($_GET['act']) && $_GET['act'] == "hide" && isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$_SESSION['not_interested_jobs'][] = (int)$_GET['id'];
	}

	if (count($_SESSION['not_interested_jobs']) > 0)
	{
		unset($not_interested);
		$not_interested = $_SESSION['not_interested_jobs'];
		$not_interested[] = "11111111111111111";
		$not_interested = array_map('intval', $not_interested);
		$not_interested = array_unique($not_interested);
		$where .= "job_id NOT IN (".implode(",",$not_interested).") AND ";
	}
	///////////////////////////////////////////////////////


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

		// hide submitted jobs from worker
		if (WORKER_HIDE_JOBS == 1)
		{
			$completed_jobs_query = "SELECT job_id FROM jobfia_job_tasks WHERE worker_id='$userid'";
			$completed_jobs_result = smart_mysql_query($completed_jobs_query);
			$completed_jobs_total = mysqli_num_rows($completed_jobs_result);

			if ($completed_jobs_total > 0)
			{
				unset($submitted_jobs);
				$submitted_jobs = array();
				$submitted_jobs[] = "111111111111";

				while ($completed_jobs_row = mysqli_fetch_array($completed_jobs_result))
				{
					$submitted_jobs[] = $completed_jobs_row['job_id'];
				}
				$where .= "job_id NOT IN (".implode(",",$submitted_jobs).") AND few_times='0' AND ";
			}
		}

		// show jobs for user's country only
		if (HIDE_NOT_COUNTRY_JOBS == 1)
		{
			unset($jobs_per_country);
			$jobs_per_country = array();
			$jobs_per_country[] = "111111111111";

			$sql_jobs_per_country = smart_mysql_query("SELECT job_id FROM jobfia_job_to_country WHERE country_id='".(int)$_SESSION['country']."' OR country_id='111111'");
			if (mysqli_num_rows($sql_jobs_per_country) > 0)
			{
				while ($row_jobs_per_country = mysqli_fetch_array($sql_jobs_per_country))
				{
					$jobs_per_country[] = $row_jobs_per_country['job_id'];
				}
				$jobs_per_country = array_map('intval', $jobs_per_country);
				$jobs_per_country = array_unique($jobs_per_country);
			}
			$where .= "(job_id IN (".implode(",",$jobs_per_country).") OR worldwide='1') AND ";
		}
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
			<div class="breadcrumb-section">
				<!-- breadcrumb -->
				<ol class="breadcrumb">
					<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
					<li>Jobs</li>
				</ol><!-- breadcrumb -->						
				<h2 class="title">Jobs</h2>
			</div>

			<div class="banner-form banner-form-full job-list-form">
				<form action="#">
					<!-- category-change -->
					<div class="dropdown category-dropdown">						
						<a data-toggle="dropdown" href="#"><span class="change-text">Job Category</span> <i class="fa fa-angle-down"></i></a>
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
						<a data-toggle="dropdown" href="#"><span class="change-text">Job Location</span> <i class="fa fa-angle-down"></i></a>
						<ul class="dropdown-menu category-change language-change">
							<!--
							<li><a href="#">Location 1</a></li>
							<li><a href="#">Location 2</a></li>
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
				
					<input type="text" name="searchtext" class="form-control" placeholder="Type your keyword" value="<?php if (isset($stext)) echo $stext; ?>">
					<button type="submit" class="btn btn-primary" value="Search">Search</button>
					<input type="hidden" name="action" value="search">
				</form>
			</div><!-- banner-form -->
	
			<div class="category-info">	
				<div class="row">
					<div class="col-md-3 col-sm-4">
						
						<center><a href="<?php SITE_URL; ?>job_create.php" class="btn btn-success btn-lg btn-block"><i class="fa fa-plus"></i> Post a Job</a></center>


		<div style="background: #FFF; border: 1px solid #ddd; margin: 8px 0; border-radius: 5px; padding: 5px 10px;">
			<h3><i class="fa fa-search"></i> Search for jobs</h3>
			<?php if (@$search) { ?><div align="right"><a class="remove" href="<?php echo SITE_URL; ?>jobs.php"><span class="badge">x delete filter</span></a></div><?php } ?>
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
		            Pays at least: 
			            <div class="input-group"> 
							<span class="input-group-addon"><?php echo SITE_CURRENCY; ?></span>	
							<input type="text" name="reward" id="reward" class="form-control" value="<?php if (isset($reward)) echo $reward; ?>" placeholder="0.00" />
			            </div>
		            </td>
			</tr>
			<tr>
				<td align="left" valign="middle">
					<div class="checkbox">
						<?php if (PREMIUM_ACCOUNTS == 1) { ?><label class="<?php if (@$spremium == 1) echo "checked"; ?>"><input type="checkbox" name="spremium" id="spremium" value="1" <?php if (@$spremium == 1) echo "checked='checked'"; ?> /> for <i class="fa fa-user" style="color: #ffa310"></i>  premium workers</label><br/><?php } ?>
						<label class="<?php if (@$sfeatured == 1) echo "checked"; ?>"><input type="checkbox" name="sfeatured" id="sfeatured" value="1" <?php if (@$sfeatured == 1) echo "checked='checked'"; ?> /> <i class="fa fa-star" style="color: #40b922"></i> only featured jobs</label>
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


		<?php if (SHOW_LATEST_COMPLETED == 1) { ?>
		<?php 
			$ctasks_query = "SELECT * FROM jobfia_job_tasks WHERE status='confirmed' ORDER BY added DESC LIMIT 10";
			$ctasks_result = smart_mysql_query($ctasks_query);
			$ctasks_total = mysqli_num_rows($ctasks_result);

			if ($ctasks_total > 0) {
		?>
		
		<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				//$("#loader").show();
				$("#show").hide().delay(3000).fadeIn();
				function update_stats() {
					$('#show').each(function(){
						$("#loader").show().delay(3000).fadeOut();
						$(this).hide().load('<?php SITE_URL; ?>getdata.php').fadeIn('slow');
						//$("#loader").hide();
					});
				}
				update_stats();
				setInterval(update_stats, 30000);
			});
		</script>


		<div style="background: #FFF; border: 1px solid #ddd; margin: 8px 0; border-radius: 5px; padding: 5px 10px;">
			<h4 class="brd"><i class="fa fa-check-circle" style="color: #01a650"></i> Latest Completed Jobs</h4>
		
			<div class="carousel slide vertical" data-ride="carousel" id="quote-carousel">
	        <!-- Bottom Carousel Indicators -->
	        <!--<ol class="carousel-indicators">
	          <li data-target="#quote-carousel" data-slide-to="0" class="active"></li>
	          <li data-target="#quote-carousel" data-slide-to="1"></li>
	          <li data-target="#quote-carousel" data-slide-to="2"></li>
	        </ol>-->
	        <div class="carousel-inner">
	 		<?php
		 		$cc = 0;
		 		$cresult = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE status='confirmed' ORDER BY added DESC LIMIT 3"); //date = today ORDER BY RAND()
		 		if (mysqli_num_rows($cresult) > 0)
		 		{
			?>
			<?php while ($crow = mysqli_fetch_array($cresult)) { $cc++; ?>
				<div class="item <?php if ($cc == 1) echo "active"; ?>">
	            <blockquote>
	              <div class="row">
	                <div class="col-sm-3 col-xs-3 text-center">
	                  <?php echo GetUserAvatar($crow['worker_id'], $width=50); ?>
	                </div>
	                <div class="col-sm-9 col-xs-9">
	                  <p><i class="fa fa-user-o"></i> <?php echo GetWorkerInfo($crow['worker_id'], $show_type = 2); ?> just completed the job "<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $crow['job_id']; ?>"><?php echo $crow['job_title']; ?></a>" and earned <b><?php echo DisplayMoney($crow['job_price']); ?></b></p>
	                  <small><?php echo SITE_TITLE; ?></small>
	                </div>
	              </div>
	            </blockquote>
				</div>
			<?php	}} ?>
	
	        </div>
			</div>
		</div>

		<!--
			<div style="background: #FFF; border: 1px solid #ddd; margin: 8px 0; border-radius: 5px; padding: 5px 10px;">
			<h4><i class="fa fa-check-circle-o"></i> Latest Completed Jobs</h4>

				<div id="loader"><p align="center"><img src="<?php echo SITE_URL; ?>images/loading_line.gif" /></p></div>
				<div id="show"></div>

                	 <div class="carousel slide vertical" data-ride="carousel" id="quote-carousel">
				        <ol class="carousel-indicators">
				          <li data-target="#quote-carousel" data-slide-to="0" class="active"></li>
				          <li data-target="#quote-carousel" data-slide-to="1"></li>
				          <li data-target="#quote-carousel" data-slide-to="2"></li>
				        </ol>	                	 
			         <div class="carousel-inner">
			         
                    </div>                                
                 </div>
                 
			</div>
			-->
		<?php } ?>
		<?php } ?>

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
											<h5><a href="<?php echo SITE_URL; ?>jobs.php"><i class="fa fa-caret-down"></i> All Jobs</a></h5>
														
											<!--<a <?php echo (!$cat_id && !$search) ? "class='cat_link_active'" : "class='cat_link'"; ?> href="<?php echo SITE_URL; ?>jobs.php"><b>All Jobs</b></a>-->
											<ul>
												<?php
													$cresult = smart_mysql_query("SELECT * FROM jobfia_categories WHERE parent_id='0'  ORDER BY name");
													$ctotal = mysqli_num_rows($cresult);
											
													if ($ctotal > 0)
													{
														while ($row_cats = mysqli_fetch_array($cresult))
														{
															if ($cat_id == $row_cats['category_id'])
																echo "<li><a class='cat_link_active' href='".SITE_URL."jobs.php?cat=".$row_cats['category_id']."'>".$row_cats['name']." (".CategoryTotalJobs($row_cats['category_id']).")</a></li>";
															else
																echo "<li><a class='cat_link' href='".SITE_URL."jobs.php?cat=".$row_cats['category_id']."'>".$row_cats['name']."  (".CategoryTotalJobs($row_cats['category_id']).")</a></li>";
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

							 </div><!-- panel-group -->
						</div>
					</div><!-- accordion-->

					<!-- recommended-ads -->
					<div class="col-sm-8 col-md-9">				
						<div class="section job-list-item">

		<?php if (@$search && $total > 0) { ?>
			<h1>Search results</h1>
			<p><span class="results_count"><?php echo number_format($total); ?></span> jobs found <?php if (isset($stext) && $stext != "") { ?>for '<b><?php echo $stext; ?></b>'<hr></p><?php } ?>
		<?php }else{ ?>
			<h1><i class="fa fa-briefcase" style="color: #01a650"></i> <?php echo getCategory($_GET['cat']); ?></h1>
		<?php } ?>
		
		
						<?php if ($total > 0) { ?>
							<div class="featured-top">
								<h4><a href="<?php echo SITE_URL; ?>rss.php<?php echo (isset($cat_id)) ? "?cat=".$cat_id : ""; ?>"><img src="<?php echo SITE_URL; ?>images/rss_icon.png" align="absmiddle" /></a> &nbsp; Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?> jobs</h4>
								<div class="dropdown pull-right">
									<!--
									<div class="dropdown category-dropdown">
										<h5>Sort by:</h5>						
										<a data-toggle="dropdown" href="#"><span class="change-text">Most Relevant</span><i class="fa fa-caret-square-o-down"></i></a>
										<ul class="dropdown-menu category-change">
											<li><a href="#">Most Relevant</a></li>
											<li><a href="#">Most Popular</a></li>
										</ul>								
									</div>
									-->	
								</div>							
							</div>
						<?php } ?>
					
		<hr>				
		<p class="category_description"><?php echo getCategory($_GET['cat'], 1); ?></p>

		<?php if ($total > 0) { ?>
		<div class="row browse_top">
			<form action="" id="form1" name="form1" method="get">
			<div class="col-md-4">
				
					<span>Sort by:</span>
					<select name="column" id="column" class="form-control" onChange="document.form1.submit()">
						<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
						<option value="duration" <?php if ($_GET['column'] == "duration") echo "selected"; ?>>Duration</option>
						<option value="positions" <?php if ($_GET['column'] == "positions") echo "selected"; ?>>Positions</option>
						<option value="views" <?php if ($_GET['column'] == "views") echo "selected"; ?>>Popularity</option>
						<option value="price" <?php if ($_GET['column'] == "price") echo "selected"; ?>>Most paying</option>
					</select>
			</div>
			<div class="col-md-4">
				Asc/Desc:
					<select name="order" id="order" class="form-control" onChange="document.form1.submit()">
						<option value="desc"<?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
			</div>
			<div class="col-md-4">
				<span>View:</span>
					<select name="show" id="show" class="form-control" onChange="document.form1.submit()">
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
			
			</div>
			</form>
		</div>
		<?php } ?>


			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>

							<div class="job-ad-item <?php if ($row['featured'] == 1) { echo "row_featured"; }else{ if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; } ?>">
								<div class="item-info">
									<div class="pull-right" style="padding-top: 10px"> <a href="<?php echo SITE_URL; ?>jobs.php?id=<?php echo $row['job_id']; ?>&act=hide"><i class="fa fa-eye-slash fa-lg jobfia_tooltip" title="Hide"></i></a></div>
									<div class="item-image-box">
										<div class="item-image">
											<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>"><img src="images/job/1.png" alt="Image" class="img-responsive"></a>
										</div>
										<a href="#" class="jobfia_tooltip" title="Job Done">
											<div class="progress" style="margin: 5px 0; height: 15px">
												<div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row['job_id'])/$row['positions'])*100); ?>%"></div>
											</div>
										</a>
									</div>
									<div class="ad-info">
										<span>
										<?php if ($row['worldwide'] == 1) { ?><i class="fa fa-globe fa-lg jobfia_tooltip" title="Worldwide" style="color: #55b055"></i><?php }else{ ?><i class="fa fa-globe fa-lg jobfia_tooltip" title="Worldwide" style="color: #db6a13" class="jobfia_tooltip" align="absmiddle" title="Selected countries only" /></i><?php } ?>
										<?php if ($row['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
										<?php if ($row['premium'] == 1) { ?><span class="premium jobfia_tooltip" title="for premium workers only"><img src="<?php echo SITE_URL; ?>images/user_premium.png" align="absmiddle" /></span><?php } ?>										
										<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>" class="title"><?php if (strlen($row['title']) > 75) $job_title = substr($row["title"], 0, 70)."..."; else $job_title = $row["title"]; echo $job_title; ?></a></span>
										
										<?php echo substr(stripslashes($row['description']), 0, 150); ?><br><br>
										
										<div class="ad-meta">
											<ul>
												<li style="width: 120px"><a href="#"><i class="fa fa-money" aria-hidden="true"></i><?php echo DisplayMoney($row['price']); ?></a></li>
												<li style="width: 120px"><a href="#"><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ConvertDuration($row['duration']); ?></a></li>
												<li style="width: 70px"><i class="fa fa-users" aria-hidden="true"></i><a style="color: #6f6f6f" href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>"><?php echo GetJobWorkersTotal($row['job_id']); ?> / <sup><?php echo $row['positions']; ?></sup></a></li>
												<li style="width: 70px"><!--<i class="fa fa-bar-chart" aria-hidden="true"></i>--><a href="#" class="jobfia_tooltip" title="Success Rate"><?php echo GetJobSuccess($row['job_id']); ?></a></li>
												
												<li style="width: 160px"><a href="#"><i class="fa fa-calendar" aria-hidden="true"></i><?php echo relative_date(strtotime($row['added'])); ?></a></li>
											</ul>
										</div>											
									</div>
								</div>
							</div><!-- job-item -->                
                
			<?php } ?>

					<?php
							$params = "";
							if ($cat_id)				$params .= "cat=$cat_id&";
							if ($search == 1)			$params .= "action=search&";
							if ($stext)					$params .= "searchtext=$text&";
							if ($reward > 0)			$params .= "reward=$reward&";
							if ($spremium == 1)			$params .= "spremium=1&";
							if ($sfeatured == 1)		$params .= "sfeatured=1&";

							echo ShowPagination("jobs",$results_per_page,"jobs.php?".$params."column=$rrorder&order=$rorder&show=$results_per_page&","WHERE ".$where);
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
							</div>-->					


			<?php }else{ ?>
					<?php if (@$search) { ?>
						<div class="alert alert-info text-center">
							<i class="fa fa-search fa-lg"></i> There are no jobs found with your search criteria.
							<center><a href="<?php echo SITE_URL; ?>jobs.php" class="btn btn-info">See All Jobs</a></center>
						</div>
					<?php }else{ ?>
						<div class="alert alert-info text-center">There are no jobs available at this time.</div>
					<?php } ?>
			<?php } ?>


			<?php
				// show hidden jobs //
				if (count($_SESSION['not_interested_jobs']) > 0)
				{
					$not_interested = $_SESSION['not_interested_jobs'];
					$not_interested[] = "11111111111111111";
					$not_interested = array_map('intval', $not_interested);
					$not_interested = array_unique($not_interested);

					$query2 = "SELECT * FROM jobfia_jobs WHERE job_id IN (".implode(",",$not_interested).") AND status='active' ORDER BY featured DESC, added DESC LIMIT 20";
					$result2 = smart_mysql_query($query2);
					$total2 = mysqli_num_rows($result2);
					$cc2 = 0;

					if ($total2 > 0) {
			?>
					<br/>
					<a name="hiddenlist"></a>
					<h3><i class="fa fa-eye-slash"></i> Hidden Jobs</h3>
					<div class="well" id="hidden_note">You have <span class="label label-default"><?php echo $total2; ?></span> hidden jobs. <a class="btn btn-default" id="show_hidden" href="#hiddenlist"><i class="fa fa-eye"></i> Show hidden jobs &#155;</a></span></div>

					<div id="hidden_jobs_box" style="display: none;">
						<div class="pull-right"><a href="<?php echo SITE_URL; ?>jobs.php?clear"><span class="badge">x clear list</span></a></div>
						<table class="table table-striped table-hover brd" align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
							<th width="40%">Job Title</th>
							<th width="12%">Reward</th>
							<th width="12%">Job Done</th>
							<th width="15%">Success Rate</th>							
							<th width="15%">Time</th>
						</tr>
						<?php while ($row2 = mysqli_fetch_array($result2)) { $cc2++; ?>
						<tr>
						  <td nowrap="nowrap" valign="middle" align="left">
							<?php if ($row2['worldwide'] == 1) { ?><i class="fa fa-globe fa-lg jobfia_tooltip" title="Worldwide" style="color: #55b055"></i><?php }else{ ?><img src="<?php echo SITE_URL; ?>images/worldwide_not.png" class="jobfia_tooltip" align="absmiddle" title="Selected countries only" /><?php } ?>
							<?php if ($row2['featured'] == 1) { ?><span class="featured jobfia_tooltip" title="Featured"></span><?php } ?>
							<?php if ($row2['premium'] == 1) { ?><span class="premium jobfia_tooltip" title="for premium workers only"><img src="<?php echo SITE_URL; ?>images/user_premium.png" align="absmiddle" /></span><?php } ?>
							<a class="job_title" href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row2['job_id']; ?>">
								<?php if (strlen($row2['title']) > 75) $job_title2 = substr($row2["title"], 0, 70)."..."; else $job_title2 = $row2["title"]; echo $job_title2; ?>
							</a>
						  </td>
						  <td nowrap="nowrap" valign="middle" align="left"><?php echo DisplayMoney($row2['price']); ?></td>
						  <td nowrap="nowrap" valign="middle" align="center">
							  <?php echo GetJobWorkersTotal($row2['job_id']); ?> / <sup><?php echo $row2['positions']; ?></sup>
							  <div class="progress" style="height: 15px">
							  	<div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="<?php echo round((GetJobWorkersTotal($row2['job_id'])/$row2['positions'])*100); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round((GetJobWorkersTotal($row2['job_id'])/$row2['positions'])*100); ?>%"></div>
							</div>
						  </td>
						  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 15px"><?php echo GetJobSuccess($row2['job_id']); ?></td>						  					  
						  <td valign="middle" align="left"><?php echo ConvertDuration($row2['duration']); ?></td>
						</tr>
						<?php } ?>
						</table>
					</div>

			<?php 
					}
				}
			?>						
						

					<!--
					<div class="col-md-2 hidden-xs hidden-sm">
						<div class="advertisement text-center">
							<a href="#"><img src="images/ads/1.jpg" alt="" class="img-responsive"></a>
						</div>
					</div>
					-->
					
					</div>
					</div><!-- recommended-ads -->
					
					
				</div>	
			</div>
		</div><!-- container -->
	</section><!-- main -->
	

	<?php if (!isLoggedIn()) { ?>
	<section id="something-sell" class="clearfix parallax-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2 class="title">Sign up and start to post and do job right now.</h2>
					<h4>Create your account and start to use our jobs site.</h4>
					<a href="<?php echo SITE_URL; ?>signup.php" class="btn btn-primary">Sign up</a>
				</div>
			</div>
		</div>
	</section>
	<?php } ?>



	<?php }else{ ?>
		<h1><i class=" fa fa-briefcase"></i> Jobs</h1>
		<div class="alert alert-info text-center"><i class="fa fa fa-eye-slash fa-5x"></i><br> You need <a href="<?php echo SITE_URL; ?>signup.php">signup</a> or <a href="<?php echo SITE_URL; ?>login.php">login</a> to see jobs list.</div>
	<?php } ?>

					<script src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
					<script>
					$("#show_hidden").click(function () {
					  $("#hidden_jobs_box").toggle("slow");
					  $("#hidden_note").toggle("hide");
					});
					</script>


<?php require_once ("inc/footer.inc.php"); ?>