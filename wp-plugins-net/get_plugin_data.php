<?php

include_once("db_functions.php");
include_once("form_functions.php");
mb_http_output("UTF-8");
mb_internal_encoding("UTF-8");
connectDB();

$status = "1";
$wp_version = "1";

if (isset($_REQUEST['id']) && ($_REQUEST['id'] > 0))
{
	$status .= " AND `plugins`.`plugin_id` = ". mysql_escape_string($_REQUEST['id']);
}
else
{

	if (isset($_REQUEST['author_id']) && ($_REQUEST['author_id'] > 0))
	{
		$status .= " AND `accounts`.`account_id` = ". $_REQUEST['author_id'];
	}
	
	if (!empty($_REQUEST['filter']))
	{
		$filter = "'%". $_REQUEST['filter'] . "%'";
		$status .= " AND (`plugins`.`plugin_name` LIKE $filter OR `categories`.`name` LIKE $filter OR `parent`.`name` LIKE $filter OR `plugins`.`description` LIKE $filter OR `plugins`.`long_description` LIKE $filter OR `plugins`.`download_url` LIKE $filter OR `accounts`.`url` LIKE $filter)";
	}
		
	if (isset($_REQUEST['status']))
		switch ($_REQUEST['status'])
		{		
			case "dev":
				$status .= " AND `plugins`.`status` IN ('alpha', 'beta')";
			break;
						
			case "stable":
			default:
				$status .= " AND `plugins`.`status` IS 'stable'";
			break;
		} 
	
	if (isset($_REQUEST['wp_version']))
	{
		if ($_REQUEST['wp_version'] == "13")
			$wp_version = "`plugins`.`wp_13` = 'yes'";
		elseif ($_REQUEST['wp_version'] == "12")
			$wp_version = "`plugins`.`wp_12` = 'yes'";
		elseif ($_REQUEST['wp_version'] == "any")
			$wp_version = "(`plugins`.`wp_13` = 'yes' OR `plugins`.`wp_12` = 'yes')";
		
	}
}

if (isset($_REQUEST['wppm_version']))
	$wppm_version = $_REQUEST['wppm_version'];
else
{
	$wppm_version = 10;
	$status .= " AND `plugins`.`plugin_id` = 37";
}
		
if ($res = mysql_query($query = "SELECT `plugins`.`plugin_id`, `plugins`.`plugin_name`, `categories`.`name` AS `cat_name`, `parent`.`name` AS `parent_name`, `plugins`.`dir_name`, `plugins`.`version_major`, `plugins`.`version_minor`, `plugins`.`status`, `plugins`.`wp_12`, `plugins`.`wp_13`, `plugins`.`description`, `plugins`.`long_description`,  `plugins`.`license`, `plugins`.`plugin_url`, `plugins`.`directions_url`, `plugins`.`download_url`, `plugins`.`oneclick_url`, `plugins`.`date_updated`, `accounts`.`name` AS `author`, `accounts`.`url` AS `author_url`, `plugins`.`config_vals` AS `config_vals`, COUNT(`updates`.`update_id`) AS `changelogs`, IFNULL(`parent`.`name`,`categories`.`name`) AS `top_cat`, IFNULL(`parent`.`rank`, `categories`.`rank`) AS `parent_rank`, `categories`.`rank` AS `cat_rank`, `plugins`.`wppm_version` AS `wppm_version`, `plugins`.`approved` AS `approved`, `plugins`.`sql_command` AS `sql_command` FROM `plugins` LEFT JOIN `accounts` ON `accounts`.`account_id` = `plugins`.`account_id` LEFT JOIN `categories` ON `categories`.`category_id` = `plugins`.`category_id` LEFT JOIN `categories` AS `parent` ON `categories`.`parent_id` = `parent`.`category_id` LEFT JOIN `updates` ON `updates`.`plugin_id` = `plugins`.`plugin_id` WHERE $status AND $wp_version GROUP BY `plugins`.`plugin_id` ORDER BY `parent_rank` DESC, `cat_rank` DESC, `top_cat` ASC"))
{
	while($row = mysql_fetch_assoc($res))
	{
		array_walk( $row, "clean_string");
		if (empty($row['parent_name']))
		{
			$row['parent_name'] = $row['cat_name'];
			$row['cat_name'] = "";
		}
		if (($wppm_version != "last") && ($wppm_version < $row['wppm_version']))
			$row['oneclick_url'] = "";
		
		$plugins[$row['plugin_id']] = $row;
	}
	//echo $query;
	if (isset($plugins))
		echo serialize($plugins);
	else
		echo serialize(array());
}
else
{
	echo serialize(error_msg($query, "mysql"));
}


?>