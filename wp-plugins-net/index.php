<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>WordPress Plugins Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="DC.title" content="A Plugin Database for WordPress. Can be used from the website or using wp-plugin-mgr which lets you install/remove plugins with one click." />
<link rel="stylesheet" href="wp-plugins.css" />
<script type="text/javascript" src="nicetitle.js"></script>
<link rel="stylesheet" href="nicetitle.css" />
</head>
<body>
 <script type="text/javascript">
 function switch_arrow(my_id)
  { 
   var elem = document.getElementById(my_id);
   var str = elem.className;
   
   if (str.indexOf('closed') > -1)
	{
		elem.className = str.replace('closed', 'opened');
	}
	else
	{
		elem.className = str.replace('opened', 'closed');
   }
  }
</script>


<div align="center"><a href="http://wp-plugins.net" title="Wordpress Plugin Database"><img src="img/bannerDB.png" alt="Wordpress Plugin Database" /></a><h1>Wordpress Plugin Database</h1></div>

<div id="outset">
	 <span class="box"><a href="faq.html" target="_new" title="FAQ for users and developers">FAQ</a></span>
	<span class="box"><a href="rss2.php" target="_new" title="RSS2 feed for new releases and updates">RSS2</a></span>
	<span class="box"><a href="./wp-plugin-mgr.zip" title="Easy to install local DB client, drop it in your WP root folder and it will help you keep tracks of installed plugins and available upgrades.">wp-plugin-mgr</a></span>
	 <span class="box"><a href="faq.html#dev" target="_new">Plugin Dev Info</a></span>
	<span class="box"><a href="dev.php" target="_new">Add your plugin</a></span>
</div>

<div id="main_content">

<div id="left_col">
<h3>Plugins Available for Wordpress:</h3>
<?php

$plugin_server_url = "http://unknowngenius.com/wp-plugins/get_plugin_data.php?wppm_version=666";

if (isset($_REQUEST['id']) && ($_REQUEST['id'] > 0))
	$plugin_server_url .= "&id=" . $_REQUEST['id'];
elseif (isset($_REQUEST['author_id']) && ($_REQUEST['author_id'] > 0))
	$plugin_server_url .= "&author_id=" . ($_REQUEST['author_id'] + 0);
elseif (!empty($_REQUEST['filter']))
{
	echo "<p>Filtered by <b><i>" . $_REQUEST['filter'] ."</i></b>:</p>";
	$plugin_server_url .= "&filter=" . urlencode($_REQUEST['filter']);
}

$plugins = get_plugin_data ($plugin_server_url);	

if (! is_array($plugins) )
{
	if ($plugins)
		error_msg($plugins, true);
	else
		echo error_msg("Can't access remote plugin database. Unable to refresh plugin list.");
}
elseif (! count($plugins))
	echo error_msg("No matching plugin.", true);

//echo "<pre>";
//print_r($plugins);
//echo "</pre>";


foreach($plugins as $id => $plugin)
{
	if (empty ($plugin['parent_name']))
		$parent = "[empty]";
	else
		$parent = $plugin['parent_name'];
	
	if (empty ($plugin['cat_name']))
		$cat = "[empty]";
	else
		$cat = $plugin['cat_name'];
	$plugins_s[$parent][$cat][$id] = $plugin;
}

$total = count($plugins_s);
$section_counter = 0;

if (isset($_REQUEST['filter']) || isset($_REQUEST['id']) || isset($_REQUEST['author_id']) || ($total <= 5))
	$default_state = "opened";
else
	$default_state = "closed";

$total_count = 0;
if ($total)
{
echo "<ul id=\"plugin_list\">";
foreach($plugins_s as $parent => $cats)
{
	$sub_total = 0;
	foreach ($cats as $plugs)
		$sub_total += count($plugs);
	$total_count += $sub_total;
		
	echo "<li class=\"parent_category $default_state\" id=\"section_$section_counter\">";
	echo "<span class=\"parent_category_label\" onclick=\"javascript:switch_arrow('section_$section_counter');\">$parent ($sub_total)</span>";
	echo "<ul>";
	
	$section_counter++;
	
	foreach($cats as $cat => $plugs)
	{
		if($cat != "[empty]")
		{
			echo "<li class=\"category opened\" id=\"section_$section_counter\"><span class=\"category_label\" onclick=\"javascript:switch_arrow('section_$section_counter');\">$cat (". count($plugs) . ")</span><ul>";
			$section_counter++;
		}
		
		foreach ($plugs as $id => $plugin)
		{
			$notice = "";
				
			echo "<li class=\"plugin " . (($total < 6) ? "opened" : "closed") . ((! empty($plugin['oneclick_url'])) ? " oneclick" : " manually") . "\" id=\"plugin_$id\">";
			
			//echo "<div class=\"arrow\" id=\"img_$id\"></div> ";
			echo "$notice<span class=\"name\" onclick=\"javascript:switch_arrow('plugin_$id');\">" . $plugin['plugin_name'] . "</span> <span class=\"description\" onclick=\"javascript:switch_arrow('plugin_$id');\">". $plugin['description'] . "</span>";
			
			echo "<ul class=\"details\">";
			echo detail_line("Name", $plugin['plugin_name'] . " (<a href=\"http://wp-plugins.net/index.php?id=$id\" title=\"Permaling for ". $plugin['plugin_name']."\">permalink</a>)");
			echo detail_line("Version", $plugin['version_major']. "." . $plugin['version_minor']);		
			echo detail_line("Description", $plugin['long_description'] . "<br/><br/>");
			echo detail_line("Author", $plugin['author']);
			echo detail_line("Plugin Site", "<a href=\"". $plugin['plugin_url'] . "\">" . $plugin['plugin_url'] . "</a>");
			echo detail_line("Author Site", "<a href=\"". $plugin['author_url'] . "\">" . $plugin['author_url'] . "</a>");
			$compat ="";
			if ($plugin['wp_12'] == 'yes')
				$compat .= "WP 1.2";
			if ($plugin['wp_13'] == 'yes')
			{
				if (!empty($compat))
					$compat .= " &amp; ";		
				$compat .= "WP 1.3";
			}
			echo detail_line("Compatibility", $compat);
			echo detail_line("License", $plugin['license']);
			
			if ($plugin['changelogs'] > 0)
				echo detail_line("Change Log", "<a href=\"changelog.php?plugin_id=$id\" target=\"_new\">" . $plugin['changelogs'] . " Entr". (($plugin['changelogs'] > 1) ? "ies" : "y") . "</a>" );

			if (!empty($plugin['download_url']))
				echo detail_line("Download URL", "<a href=\"". $plugin['download_url'] . "\">" . $plugin['download_url'] . "</a>");

			echo "<li class=\"download_section\">";
			if (!empty($plugin['download_url']))
				echo "<button onclick=\"window.location='". $plugin['download_url'] . "'\">Download</button> ";
			else
				echo "No download link.";
			
			echo "</li>";

			
			echo "</ul>";
			echo "</li>";
		}

		if($cat != "[empty]")
			echo "</ul></li>\n";
	}
	echo "</ul></li>\n\n";
}
echo "</ul>";

echo "(total: $total_count)";
}


function error_msg($str, $fatal = false)
{
	if ($fatal)
		echo "<div class=\"fatal_error_msg\">Fatal Error: ";
	else
		echo "<div class=\"error_msg\">";
	
	echo $str;
	if ($fatal)
		die("</div></body></html>");
	else
		echo "</div>";
}

function status_msg($str)
{
		echo "<div class=\"status_msg\">$str</div>";
}


function detail_line($label, $field)
{
	return wpautop("<li class=\"line\"><span class=\"label\">$label:</span> <span class=\"field\">$field</span></li>") . "\n";
}

function get_plugin_data($location)
{
	if ($file = @fopen ($location, "r"))
	{
		$buf = "";
		$i = 0;
		while (! feof($file) && $i++ < 10000)
			$buf .= fgets($file, 2048);
		fclose($file);
		
		return unserialize($buf);
	}
	else
	{
		return false;
	}
}

function write_to_file($location, $data)
{
	if ($file = @fopen ($location, "w"))
	{
		fwrite($file, serialize($data));
		fclose($file);
		return true;
	}
	else
	{
		return false;
	}
}

function ftp_set_perms ($ftp_connection, $ftp_file)
{
	if (function_exists("ftp_chmod"))
	{
		$res = ftp_chmod($ftp_connection, 0777, $ftp_file);
	}
	else
	{
		$chmod_cmd="CHMOD 0777 ". $ftp_file;
		$res = ftp_site($ftp_connection, $chmod_cmd);
	}

	if ($res === true)
		status_msg("Changed permissions for directory '$ftp_file' to writeable by server");
	else
		error_msg("Could not make directory '$ftp_file' writable. Please change manually or check the ftp settings and try again.", true);
}

function listFiles($dir , $type)
{ 
	 if (strlen($type) == 0) 
		 $type = "all"; 
 	$x = 0; 
 	if(! is_dir($dir)) 
	 { 
 		return $result;
 	}
 	
 	$thisdir = dir($dir); 
	while($entry=$thisdir->read()) 
 	{
 		if(($entry!='.')&&($entry!='..')) 
 		{
 			if ($type == "all") 
			{
				$result[$x] = $entry;
				$x++;
				continue;
			}
			
			$isFile = is_file("$dir$entry");
			$isDir = is_dir("$dir$entry"); 
			
 			if (($type == "files") && ($isFile))
 			{
 				$result[$x] = $entry;
 				$x++;
 				continue;
 			}
 			if (($type == "dir") && ($isDir)) 
			{
				$result[$x] = $entry; 
				$x++; 
				continue;
			} 
  			
  			$temp = explode(".", $entry); 
  			
  			if (($type == "noext") && (strlen($temp[count($temp) -1]) == 0))
  			{
 				$result[$x] = $entry;
 				$x++;
 				continue;
 			}
 			
			if (($isFile) && (strtolower($type) == strtolower($temp[count($temp) - 1]))) 
			{
				$result[$x] = $entry;
				$x++;
				continue;
			}
		} 
	}

	 return $result; 
}

function rmdirr($dirname, $delete_dir = true)
{
	if ($delete_dir)
		if (is_file($dirname))
			return unlink($dirname);
		
    $dir = dir($dirname);
    while (false !== $entry = $dir->read())
    {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep delete directories      
        if (is_dir("$dirname/$entry")) 
        {
            rmdirr("$dirname/$entry");
        } 
        else 
        {
            unlink("$dirname/$entry");
        }
    }
    $dir->close();
    
    if($delete_dir)
    	return rmdir($dirname);
    else
    	return true;
    
}

function wpautop($pee, $br = 1) {
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
	// Space things out a little
	$pee = preg_replace('!(<(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "\n$1", $pee); 
	$pee = preg_replace('!(</(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])>)!', "$1\n", $pee);
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines 
	$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
	$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $pee); // make paragraphs, including one at the end 
	$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace 
    $pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
	$pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "$1", $pee);
	$pee = preg_replace('!(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); 
	if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
	$pee = preg_replace('!(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*<br />!', "$1", $pee);
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $pee);
	$pee = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " stripslashes('$1') .  clean_pre('$2')  . '</pre>' ", $pee);
	
	return $pee; 
}




?>
</div>

<div id="right_col">
<fieldset id="search_box" class="options">
<legend>Search:</legend>
	<form action="<?=$_SERVER['PHP_SELF'] ?>" name="filter_plugins" method="get"> 
		<input type="text" size="10" name="filter" /> <input type="submit" name="filter_search" value="Go..." />
	</form>
</fieldset>
<fieldset class="options">
<legend>Legend:</legend>
	<p><span class="box_label oneclick">One-Click Install Available</span></p>
	<p><span class="box_label manually">Only Manual Install</span></p>
</fieldset>
<fieldset class="options" id="news">
<legend>News:</legend>
	<ul><li>Take advantage of One-Click plugin installs by installing <a href="./wp-plugin-mgr.zip">WordPress Plugin Manager</a> (please read carefully the direction file).</li></ul>
</fieldset>
</div>

</div>

<div id="footer">
<a href="http://getfirefox.com" title="This page looks an awful lot better in a standard-compliant browser..."><img src="img/get_firefox.png" alt="This page looks an awful lot better in a standard-compliant browser..."  id="get_firefox"></a><br/>Faq, help and <a href="http://unknowngenius.com/wp-plugins/faq.html#dev">dev info</a> available <a href="http://unknowngenius.com/wp-plugins/faq.html">here</a>
<br />Design contribution by <a href="http://xplite.r0gue.net/">drz3d</a>
<br />© 2005 - dr Dave - <a href="http://unknowngenius.com/blog">http://unknowngenius.com/blog</a>
</div>
</body>
</html>