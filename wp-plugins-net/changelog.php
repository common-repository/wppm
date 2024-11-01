<html>
<head>
	<title>Plugin Changelog</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css"><!--
	
		body  { color: #474747; font-size: 11px; font-family: Georgia, "Times New Roman", Times, serif; background-color: #F2F2FF; }

		
		.fatal_error_msg {color:white; font-weight:bold; background-color:red; border: 1px black solid; padding: 2px; margin: 5px;}
		.error_msg {color:white; font-weight:bold; background-color:orange; border: 1px black solid;  padding: 2px; margin: 5px;}
		.success_msg {color:white; font-weight:bold; background-color:green; border: 1px black solid;  padding: 2px; margin: 5px;}
		.status_msg {color:white; font-weight:bold; background-color:grey; border: 1px black solid;  padding: 2px; margin: 3px;}
		.item {border:black 1px solid; padding:5px; margin:2px 30px 5px 30px;}
		
   </style>
</head>
<body>
<?php
if (empty($_REQUEST['plugin_id']))
	die("Need a plugin ID");
$id = $_REQUEST['plugin_id'];

include_once("db_functions.php");
include_once("form_functions.php");
connectDB();

if ($res = mysql_query("SELECT DISTINCT `plugins`.`plugin_name`, `updates`.`changelog`, `updates`.`version_major` AS `version_major`, `updates`.`version_minor` AS `version_minor`  FROM `plugins`, `updates` WHERE `plugins`.`plugin_id` = $id AND `updates`.`plugin_id` = $id ORDER BY `version_major`, `version_minor` DESC"))
	{
		while($row = mysql_fetch_assoc($res))
		{
			array_walk($row, "clean_string");
			$logs[] = $row;
		}
	}
	else
	{
		die (mysql_error());
	}
	if (count($logs))
	{
		echo "<h3>Changelog for plugin: <i>". $logs[0]['plugin_name'] . "</i></h3><br/>";
		foreach($logs as $log)
		{
			echo "<div class=\"item\"><b>Version ". $log['version_major'] . "." .  $log['version_minor'] . "</b><br/><br/>" . nl2br($log['changelog']) . "</div>";
		}
	}
	else
		echo "<i>No changelogs for this plugin</i>";

?>
<br/><p>[<a href="http://unknowngenius.com/wp-plugins/index.php?id=<?=$id ?>">Back to Wordpress Plugin Database</a>]</p>
<p>Faq, help and <a href="http://unknowngenius.com/wp-plugins/faq.html#dev">dev info</a> available <a href="http://unknowngenius.com/wp-plugins/faq.html">here</a></p>
<p>©2004 - dr Dave - <a href="http://unknowngenius.com/blog">http://unknowngenius.com/blog</a></p>
</body>
</html>