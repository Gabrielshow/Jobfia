<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/


/**
 * Creates pagination
 * @param	$table		table name
 * @param	$limit		result's limit
 * @param	$target		link's target
 * @param	$where		additional WHERE parameter
 * @return	string		returns pagination
*/

function ShowPagination($table, $limit, $target, $where = "")
{
	$main_sql = "SELECT COUNT(*) AS total FROM jobfia_".$table." ".$where;
	$total_pages = mysqli_fetch_array(smart_mysql_query($main_sql));
	$total_pages = $total_pages['total'];
	$adjacents = "3";
	$page = (int)$_GET['page'];
	
	if ($page)
		$start = ($page - 1) * $limit;
	else
		$start = 0;
	 
	if ($page == 0) $page = 1;
	$prev = $page - 1;
	$next = $page + 1;
	$lastpage = ceil($total_pages/$limit);
	$lpm1 = $lastpage - 1;
	
	
if (isset($_SESSION['adm']['id']) && is_numeric($_SESSION['adm']['id']))
{
	/////
	$pagination = "";

	if ($lastpage > 1)
	{  
		$pagination .= "<div class='pagination'>";
		
		if ($page > 1)
			$pagination.= "<a href='".$target."page=$prev' class='text'>&#139; Previous</a>";
		else
			$pagination.= "<span class='disabled'>&#139; Previous</span>";  
	 
		if ($lastpage < 7 + ($adjacents * 2))
		{  
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
				$pagination.= "<span class='curPage'>$counter</span>";
				else
				$pagination.= "<a href='".$target."page=$counter'>$counter</a>";                  
			}
		}
		elseif($lastpage > 5 + ($adjacents * 2))
		{
			if ($page < 1 + ($adjacents * 2))      
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class='curPage'>$counter</span>";
					else
						$pagination.= "<a href='".$target."page=$counter'>$counter</a>";                  
				}
				$pagination.= "...";
				$pagination.= "<a href='".$target."page=$lpm1'>$lpm1</a>";
				$pagination.= "<a href='".$target."page=$lastpage'>$lastpage</a>";      
			}
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination.= "<a href='".$target."page=1'>1</a>";
				$pagination.= "<a href='".$target."page=2'>2</a>";
				$pagination.= "...";
				
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class='curPage'>$counter</span>";
					else
						$pagination.= "<a href='".$target."page=$counter'>$counter</a>";                  
				}
				
				$pagination.= "..";
				$pagination.= "<a href='".$target."page=$lpm1'>$lpm1</a>";
				$pagination.= "<a href='".$target."page=$lastpage'>$lastpage</a>";      
			}
			else
			{
				$pagination.= "<a href='".$target."page=1'>1</a>";
				$pagination.= "<a href='".$target."page=2'>2</a>";
				$pagination.= "..";
				
				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class='curPage'>$counter</span>";
					else
						$pagination.= "<a href='".$target."page=$counter'>$counter</a>";                  
				}
			}
	}
	 
	if ($page < $counter - 1)
		$pagination.= "<a href='".$target."page=$next' class='text'>Next &#155;</a>";
	else
		$pagination.= "<span class='disabled'>Next &#155;</span>";
		$pagination.= "</div>";      
	}

		return $pagination;
	/////	

}	
	

	$pagination = "";

	if($lastpage > 1)
	{  
		$pagination .= "<ul class='pagination'>";
		
		if ($page > 1)
			$pagination.= "<li><a href='".$target."page=$prev'>&#139; Previous</a></li>";
		else
			$pagination.= "<span class='disabled'>&#139; Previous</span>";  
	 
		if ($lastpage < 7 + ($adjacents * 2))
		{  
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
				$pagination.= "<li class='active'><span class='curPage'>$counter</span></li>";
				else
				$pagination.= "<li><a href='".$target."page=$counter'>$counter</a></li>";                  
			}
		}
		elseif($lastpage > 5 + ($adjacents * 2))
		{
			if($page < 1 + ($adjacents * 2))      
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><span class='curPage'>$counter</span></li>";
					else
						$pagination.= "<a href='".$target."page=$counter'>$counter</a>";                  
				}
				$pagination.= "...";
				$pagination.= "<a href='".$target."page=$lpm1'>$lpm1</a></li>";
				$pagination.= "<a href='".$target."page=$lastpage'>$lastpage</a></li>";      
			}
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination.= "<li><a href='".$target."page=1'>1</a></li>";
				$pagination.= "<li><a href='".$target."page=2'>2</a></li>";
				$pagination.= "...";
				
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><span class='curPage'>$counter</span></li>";
					else
						$pagination.= "<li><a href='".$target."page=$counter'>$counter</a></li>";                  
				}
				
				$pagination.= "..";
				$pagination.= "<li><a href='".$target."page=$lpm1'>$lpm1</a></li>";
				$pagination.= "<li><a href='".$target."page=$lastpage'>$lastpage</a></li>";      
			}
			else
			{
				$pagination.= "<li><a href='".$target."page=1'>1</a></li>";
				$pagination.= "<li><a href='".$target."page=2'>2</a></li>";
				$pagination.= "..";
				
				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<li class='active'><span class='curPage'>$counter</span></li>";
					else
						$pagination.= "<li><a href='".$target."page=$counter'>$counter</a></li>";                  
				}
			}
	}
	 
	if ($page < $counter - 1)
		$pagination.= "<li><a href='".$target."page=$next'>Next &#155;</a></li>";
	else
		$pagination.= "<span class='disabled'>Next &#155;</span>";
		$pagination.= "</ul>\n";      
	}

		return $pagination;
}

?>