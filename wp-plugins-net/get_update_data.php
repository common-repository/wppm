<?php

include_once("db_functions.php");
include_once("form_functions.php");
mb_http_output("UTF-8");
mb_internal_encoding("UTF-8");
connectDB();

$cur_major = $_REQUEST['cur_major'];
$cur_minor = $_REQUEST['cur_minor'];

if (empty($_REQUEST['plugin_id']))
{
	serialize(error_msg("Need to provide a plugin ID."));
}
elseif ($res = mysql_query($query = "SELECT * FROM `updates` WHERE `plugin_id` = " . $_REQUEST['plugin_id']. " AND ((`version_major` > $cur_major) OR ((`version_major` = $cur_major) AND (`version_minor` > $cur_minor))) ORDER BY `version_major`, `version_minor` ASC")) // f*ck that no SELECT (*) crap... tired of 40 line long sql queries...
{
	while($row = mysql_fetch_assoc($res))
	{
		array_walk( $row, "clean_string");
		
		$plugins[$row['version_major'] . "." . $row['version_minor']] = $row;
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