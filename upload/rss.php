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

	$where = "";

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
	
	if (isset($_GET['show']) && $_GET['show'] == "offers")
	{
		$where .= "job_type='offer' AND ";
	}

	// show jobs by category
	if (isset($_GET['cat']) && is_numeric($_GET['cat']) && $_GET['cat'] > 0)
	{
		$cat_id = (int)$_GET['cat'];
		
		unset($jobs_per_category);
		$jobs_per_category = array();
		$jobs_per_category[] = "111111111111111111111";

		$sub_categories = array();
		$sub_categories = GetSubCategories($cat_id);
		$sub_categories[] = $cat_id;

		$sql_jobs_per_category = smart_mysql_query("SELECT job_id FROM jobfia_job_to_category WHERE category_id IN (".implode(",",$sub_categories).")");
		while ($row_jobs_per_category = mysqli_fetch_array($sql_jobs_per_category))
		{
			$jobs_per_category[] = $row_jobs_per_category['job_id'];
		}
		$jobs_per_category = array_map('intval', $jobs_per_category);
		$where .= "job_id IN (".implode(",",$jobs_per_category).") AND ";

		$rss_title = GetCategory($cat_id)." Jobs";
	}

	$rss_query = "SELECT *, DATE_FORMAT(added, '%a, %d %b %Y %T') as pub_date FROM jobfia_jobs WHERE $where status='active' ORDER BY added DESC LIMIT 500";
	$rss_result = smart_mysql_query($rss_query);
	$rss_total = mysqli_num_rows($rss_result);

	if ($rss_total > 0)
	{
		header("Content-Type: application/xml; charset=UTF-8");

		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo '<rss version="2.0">';
		echo '<channel>';
		echo '<title>'.$rss_title.' - '.SITE_TITLE.'</title>';
		echo '<link>'.SITE_URL.'</link>';
		echo '<description>'.SITE_HOME_TITLE.'</description>';
		echo '<image>';
			echo '<url>'.SITE_URL.'images/logo.png</url>';
			echo '<title>'.SITE_TITLE.'</title>';
			echo '<link>'.SITE_URL.'</link>';
		echo '</image>';

		while($rss_row = mysqli_fetch_array($rss_result)) 
		{
			$job_title		= well_formed($rss_row['title'])." - ".well_formed(DisplayMoney($rss_row['price']));
			$job_link		= SITE_URL."job_details.php?id=".$rss_row['job_id'];
			$job_pubdate	= $rss_row['pub_date']." PDT";

			if (strlen($rss_row['description']) > 800)
				$job_description = substr(well_formed(stripslashes($rss_row['description'])),0,800).'...';
			else
				$job_description = well_formed(stripslashes($rss_row['description']));

			$job_description = str_replace("{WORKER_ID}", $userid, $job_description);

			echo '
				<item>
					<title><![CDATA['.$job_title.']]></title>
					<link>'.$job_link.'</link>
					<guid isPermaLink="true">'.$job_link.'</guid>
					<pubDate>'.$job_pubdate.'</pubDate>
					<description><![CDATA[ <p>'.$job_description.'</p> ]]></description>
				</item>
				';
		} 
		
		echo '</channel>';
		echo '</rss>';
	}

?>