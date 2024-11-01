<?php
// Author: dr Dave
// URL: http://unknowngenius.com/blog
// License: GPL blahblahblah...
// Version: 1.6.4b
//
// *** TO USE *** Just copy this file in the root folder of your WP install (same as the one where 
// 'wp-config.php' and 'index.php' reside, among other things) and browse to it.
//
// For example, if your blog is http://www.yourdomain.com/blog/, point your browser to 
// http://www.yourdomain.com/blog/wp-plugin-mgr.php.
//
//  You will be guided through the rest.
//
// FAQ: http://unknowngenius.com/wp-plugins/faq.html#dev

static $ran_plugin;
if (! isset($ran_plugin))
{
	$ran_plugin = true;

	global $file_path, $user_level;
 
	require_once(dirname(__FILE__).'/' .'wp-config.php');
	get_currentuserinfo();
	if ($user_level < 8)
		die ("Sorry, you must be logged in and at least a level 8 user to access the plugin manager.");
}


define ("kData_folder", "wp-content/plugin_manager/");
define("kPrefSuffix", "_prefs.php");
define("kInstalledFile", "installed");
define("kPluginDataFile", "plugin_data");
define("kParamsFile", "params");
define("kWppmMajorVersion", 1);
define("kWppmMinorVersion", 4);
define("kWppmVersion", (kWppmMajorVersion * 10 + kWppmMinorVersion));

define("kBackupScriptId", 50);
define("kBackupFunctionFile", "wp-admin/backupRestoreFunctions.php");

define("kWppmScriptId", 37);
define ("kWpContentFolder", "wp-content/");

// should move these to constants too...
$plugins_folder = "wp-content/plugins/";
$pict_folder = kData_folder;

define("kRapeMePerms", "0777");
define("kTmpDir", "tmp");

define("kPluginDataUrl", "http://unknowngenius.com/wp-plugins/get_plugin_data.php?wppm_version=" . kWppmVersion);
define("kUpdateDataUrl", "http://unknowngenius.com/wp-plugins/get_update_data.php?wppm_version=" . kWppmVersion);

define ("kPluginPicsUrl", "http://unknowngenius.com/wp-plugins/img/");
define ("kPluginFilesUrl", "http://unknowngenius.com/wp-plugins/files/");

// temporary permissions:
$perms = array(kWpContentFolder => "0777", $plugins_folder => "0777");

// permanent permissions:
$perm_perms = array(kData_folder => "0755", kData_folder . kInstalledFile => "0755", kData_folder . kPluginDataFile => "0755");
$install_finished = false;

// session data
ini_set('session.save_handler', 'files');
if (!@session_start())
	$session_error = true;
else
{
	$session_error = false;
	if (isset($_REQUEST['second_oneclick_id']))
		$_SESSION['second_oneclick_id'] = $_REQUEST['second_oneclick_id'];

	if (isset($_REQUEST['ftp_password']))
		$_SESSION['ftp_password'] = $_REQUEST['ftp_password'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>WordPress Plugin Manager v. <?=kWppmMajorVersion . "." . kWppmMinorVersion?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	
		body  { color: #474747; font-size: 11px; font-family: Georgia, "Times New Roman", Times, serif; background-color: #F2F2FF; }
		.plugin { padding: 2px 15px 2px 2px; margin: 2px 20px 2px 20px; border:2px #88F ridge; background-color: #BBF; color:white;}
		.oneclick { background-color: #4ADF71;}
		.manually { background-color: #AAA;}
		.summary {cursor:hand;}

		.plugin .description { color: black; font-style: italic; }
		.plugin .name { color: black; font-weight: bold; }
		.plugin .details {padding: 4px; margin-top: 3px;}
		.label { color: grey; font-style: italic;}
		.details .label { color: #444; }
		.manually .label { color: white; }
		.details .field { color: black;}
		.section {font-size:16px; font-weight:bold; text-align:left; padding-top: 15px;}
		.parent_category {font-size:14px; padding-left: 5px; padding-top: 7px;}
		.category {font-size:13px; padding-left: 15px; padding-top: 3px;}
		.notice {color:red; font-weight:bold;}
		.closed .details {display:none;}
		button {margin:8px 5px 2px 5px;}
		button.remove {color: red;}
		
		.fatal_error_msg {color:white; font-weight:bold; background-color:red; border: 1px black solid; padding: 2px; margin: 5px;}
		.error_msg {color:white; font-weight:bold; background-color:orange; border: 1px black solid;  padding: 2px; margin: 5px;}
		.success_msg {color:white; font-weight:bold; background-color:green; border: 1px black solid;  padding: 2px; margin: 5px;}
		.status_msg {color:white; font-weight:bold; background-color:grey; border: 1px black solid;  padding: 2px; margin: 3px;}

		.arrow {background-image:url(<?=$pict_folder?>arrow_closed.png); background-repeat: no-repeat; background-position: center; height:13px; width:13px; margin-right:2px; float:left;}

		.closed .arrow {background-image:url(<?=$pict_folder?>arrow_closed.png);}
		.closed .arrow:hover {background-image:url(<?=$pict_folder?>arrow_closed_hover.png);}
		.opened .arrow {background-image:url(<?=$pict_folder?>arrow_opened.png);}
		.opened .arrow:hover {background-image:url(<?=$pict_folder?>arrow_opened_hover.png);}

  </style>
</head>
<body>
<script language="JavaScript" type="text/javascript">
function oneclick_password (oneclick_id) 
{
	var pwd = prompt("Please enter your ftp password to proceed with installation.","");
	if ((pwd != "") && (pwd != null))
	{
		top.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?oneclick_id=" + oneclick_id + "&ftp_password=" + pwd;
	}
}

function oneclick_rem_password (oneclick_id) 
{
	var pwd = prompt("Please enter your ftp password to proceed with removal.","");
	if ((pwd != "") && (pwd != null))
	{
		top.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?remove_oneclick_id=" + oneclick_id + "&ftp_password=" + pwd;
	}
}

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
<h1 align="center">Wordpress Plugin Manager v.<?=kWppmMajorVersion . "." . kWppmMinorVersion?></h1>
<?php
if ($session_error)
	error_msg("It seems that your browser is not configured to support sessions. This might cause problems when using wp-plugin-mgr. Please make sure you enable cookie support for this page.", false);
// Load preferences
if ((!is_file(kData_folder. kParamsFile)) || (!$params = get_plugin_data(kData_folder. kParamsFile)))
	set_default_params($params);


if (! empty($_REQUEST['filter']))
{
	$filter = $_REQUEST['filter'];
	status_msg("Only showing plugins containing the string <i>$filter</i>.");

}

if (isset($_REQUEST['reset_settings']))
{
	rmdirr (kData_folder, true);
	status_msg("Erased all setting files.");
	die();
}

if (!isset($_REQUEST['set_perms_form']) && !isset($_REQUEST['set_perms']))
{
	if ( (is_writable(kData_folder) || !is_dir(kData_folder))
		 && (is_writable(kData_folder . kInstalledFile) || !is_file( kData_folder . kInstalledFile))
		 && (is_writable(kData_folder . kPluginDataFile) || !is_file(kData_folder . kPluginDataFile))
		 && (is_writable(kData_folder . kParamsFile) || !is_file(kData_folder . kParamsFile)))
		$set_data_perms = false;
	else
		$set_data_perms = true;
}

if (isset($_REQUEST['set_perms_form']) || $set_data_perms)
{
	?>
	<h1>Configuring Server</h1>
	<?php
		if ($set_data_perms)
			error_msg("Some of your preference files are not writable by this script (this might be due to an upgrade or similar reason). You will only have to correct this once", false);

	echo "Please enter your FTP connection settings:";

	wppm_ftp_form ($params, 
					"<input type=\"hidden\" name=\"set_perms\" value=\"true\">Set permissions: <input type=\"submit\" name=\"temp\" value=\"Temporary\"> <input type=\"submit\" name=\"perm\" value=\"Permanent\"><br/><br/><i>Note: choosing 'permanent' above will permanently alter your server settings by chmod'ing 777 your 'wp-content' and 'plugins' directories.<br />If you choose 'temporary', permissions will be restored between each use and you will be prompted for your ftp password each time you want to perform a one-click install.</i>");

	echo "</body></html>";

	return;
}
 
//
//*** Set Server Permissions ***
//
if (isset($_REQUEST['set_perms']))
{
	status_msg("Changing permissions.");
	$old_perms = set_permissions($_REQUEST, $perms);
	if (! is_dir(kData_folder))
	{
		$oldumask = umask(0); 
		mkdir(kData_folder, 0755);
		umask($oldumask);
	}
	else
		set_permissions($_REQUEST, $perm_perms);

	if (! isset($_REQUEST['perm']))
	{
		foreach(array("login", "server", "path") as $param)
			$params['ftp'][$param] = $_REQUEST[$param];
		write_to_file(kData_folder . kParamsFile, $params);
		status_msg("Restoring permissions.");
		set_permissions($_REQUEST, $old_perms);
	}
}

if (! is_dir(kData_folder))
{
	$oldumask = umask(0); 
	status_msg("Creating data folder (". kData_folder . ").");
	
	if (! @mkdir(kData_folder, 0755))
	{
		error_msg("Some permissions need to be adjusted.<br /> Click <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a> if you want this script to guide you through it.", false);
		echo "<br /><p><i>IF you are comfortable fiddling with your server: you can manually log onto your server and either chmod 'wp-content' 0777 or create a folder named 'plugin_manager' chmod'ed to 0777 within 'wp-content' (however, it is recommended to <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">use the automated set-up</a>).</i></p>";
		echo "</body></html>";
		exit(0);
	}
	umask($oldumask); 
	
	if (! is_writable(kData_folder))
	{
		error_msg("Data folder is not writable: " . kData_folder, true);
	}

}

if (! ini_get('allow_url_fopen'))
	error_msg("Remote file opening disabled on this install of PHP: this plugin cannot properly function.", true);

if (! is_file(kData_folder . "arrow_opened_hover.png"))
{
	status_msg("Downloading image files.");
	wppm_download_file(kPluginPicsUrl, kData_folder, "arrow_closed.png");
	wppm_download_file(kPluginPicsUrl, kData_folder, "arrow_opened.png");
	wppm_download_file(kPluginPicsUrl, kData_folder, "arrow_closed_hover.png");
	wppm_download_file(kPluginPicsUrl, kData_folder, "arrow_opened_hover.png");
}

if (! is_file(kData_folder . ".htaccess"))
{
	wppm_download_file(kPluginFilesUrl, kData_folder, "htaccess", ".htaccess");
}

if (is_writable(kWpContentFolder) && is_writable($plugins_folder) )
{
	$oneclick = true;
	$oneclick_pwd = false;
}
elseif(isset($params['ftp']))
{
	$oneclick = true;
	$oneclick_pwd = true;
}
else
{
	$oneclick = false;
	error_msg ("Note: Currently, '". kWpContentFolder . "' and/or '". $plugins_folder ."' are not writable by the server. For One-Click Installs to be available, click <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a> and follow the instructions.");
}

if (! $plugins = get_plugin_data(kData_folder . kPluginDataFile))
{
	error_msg("No local plugin data. Refreshing from remote database...");	
	$plugins = array();
}


$installed = array();

if (! is_file(kData_folder. kInstalledFile))
{
	write_to_file(kData_folder. kInstalledFile, array());
}
elseif (! $installed = get_plugin_data(kData_folder. kInstalledFile))
	$installed = array();


if (!empty($_REQUEST['config']))
{
	$config_id = $_REQUEST['config'];
	
	if (!isset($installed[$config_id]['config_vals']))
	{
		error_msg("Cannot configure plugin ID #$config_id: it hasn't been installed.", false);
	}
	elseif (empty($installed[$config_id]['config_vals']))
	{
		error_msg("This plugin (". $installed[$config_id]['plugin_name']. ") doesn't require any configuration through WPPM.", false);
	}
	else
		wppm_configure_plugin ($installed[$config_id]);
}

if (isset($_REQUEST['refresh']) || (count($plugins) == 0))
{
	$refresh = true; 
	if ($new_plugins = get_plugin_data (kPluginDataUrl))
	{
		$old_plugins = $plugins;
		$plugins = $new_plugins;
		
		if (! write_to_file(kData_folder . kPluginDataFile, $plugins))
			error_msg("Can't save plugin list.");
	}
	else
	{
		error_msg("Can't access remote plugin database. Unable to refresh plugin list.");

		if(! is_array($new_plugins))
			error_msg($new_plugins, false);
	}
	if(! count($plugins))
		error_msg("Plugin list empty.", true);
}
else
	$refresh = false;


//
//*** Install One-click archive ***
//
if (isset($_REQUEST['oneclick_id']))
{

	if (! $oneclick)
		error_msg("Cannot do one-click install", true);
	$id = $_REQUEST['oneclick_id'];
	$dir_name = $plugins[$id]['dir_name'];
	
	$got_sql = false;
	if ($updating = (isset($installed[$id])))
	{
		if($update_data = get_plugin_data (kUpdateDataUrl . "&plugin_id=$id&cur_major=".  $installed[$id]['version_major'] . "&cur_minor=" . $installed[$id]['version_minor'] ))
		{
			if (is_array($update_data) && count($update_data))
			{
				foreach($update_data as $this_update)
				{
					if (! empty($this_update['sql_update']))
					{
						$got_sql = true;	
						break;
					}
				}
			}
		}
		else
			status_msg("No specific upgrade information for this plugin. ", false);
	}
	elseif (!empty ($plugins[$id]['sql_command']))
		$got_sql = true;

	if ($got_sql)
	{
		if (isset($installed[kBackupScriptId]))
		{
			// DB Backup using backupRestore script
			if (is_file(kBackupFunctionFile))
			{
				status_msg("Using backupRestore script to create backup...", false);
				include_once(kBackupFunctionFile);
				$backup_file = "wppm_auto_backup_" . date("dmY-Hi") . ".sql";
				
				backupDatabase( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, kWpContentFolder . $installed[kBackupScriptId]['dir_name'] . "/backup/", $backup_file);
				status_msg("Created DB backup file: '$backup_file'", true);
			}
			else
				error_msg("Could not use the restoreBackup script. DB Backup cancelled.", false);
		}
		elseif (!isset($_REQUEST['skip_sql_warning']))
		{
			error_msg("This plugin needs to modify your SQL database. This could <i>potentially</i> affect your whole blog and erase content.", false);
			
			echo "<p>To proceed safely, WPPM can back-up your DB before doing such modifications, but it needs to install an additional script.<br />Please choose one of the options below:</p>";
		
			echo "<p><button onClick=\"window.location='". $_SERVER['PHP_SELF'] ."?oneclick_id=". kBackupScriptId . "&second_oneclick_id=$id';\">Backup &amp; Install</button> <button onClick=\"window.location='". $_SERVER['PHP_SELF'] ."?oneclick_id=$id&skip_sql_warning=true';\">Install (NO backup)</button> <button onClick=\"window.location='". $_SERVER['PHP_SELF'] ."';\">Cancel Install</button></p></body></html>";
			exit(0);
		}
	}
	
		
	if ($oneclick_pwd)
	{
		if (empty($_SESSION['ftp_password']))
		{
			error_msg("You need to provide a valid ftp password to install this plugin (you can also permanently set the permissions <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a>).", true);
		}
		else
		{
			if (! isset($params['ftp']))
				error_msg("Invalid FTP settings. Click <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a> to enter your ftp settings).", true);
			$ftp_params = $params['ftp'];
			$ftp_params['password'] = $_SESSION['ftp_password'];
			status_msg("Changing permissions temporarily.");
			$old_perms = set_permissions($ftp_params, $perms);
		}
	}
	
	
	// Downloading and unzipping plugin archive
	$tmp_dir = kData_folder . kTmpDir;
	if (is_dir($tmp_dir))
		rmdirr ($tmp_dir, false);
	else
		if (! mkdir($tmp_dir, 0755))
			error_msg("Couldn't write to tmp folder: '". kData_folder . kTmpDir . "' (check permissions).", true);

	if( ini_get('allow_url_fopen')) 
	{
		if(! $file = fopen ($plugins[$id]['oneclick_url'], "rb"))
			error_msg("Couldn't download one-click archive from '". $plugins[$id]['oneclick_url'] ."'", true);
		
		if (! $file_dest = fopen($tmp_dir . "/" . kTmpDir . ".zip", "wb"))
			error_msg("Couldn't write downloaded archive to '". $tmp_dir . "/" . kTmpDir . ".zip". "'", true);
	
		$i = 0;
		while(!feof($file) && ($i++ < 10000))
			fwrite($file_dest, fread($file, 8192));
		
		fclose($file_dest);
		fclose($file);
	}
	else
	{
		// Use wget ...
		$cmd = "wget -O " .realpath($tmp_dir . "/" . kTmpDir . ".zip") . " " . $plugins[$id]['oneclick_url'];
		status_msg(exec($cmd));
	}
		
	$cmd = "unzip " . realpath($tmp_dir . "/" . kTmpDir . ".zip") . " -d " . realpath($tmp_dir) ;
	status_msg(exec($cmd));
	unlink(realpath($tmp_dir . "/". kTmpDir . ".zip"));

	if (is_dir($tmp_dir . "/" . $dir_name))
		$tmp_dir .= "/". $dir_name;
	

	// Copying plugin script to plugin folder
	if (isset($plugins[$id]['script_only']) && ($plugins[$id]['script_only'] || ($plugins[$id]['script_only'] == "yes")))
	{
		$plugins[$id]['script_only'] == true;
		status_msg("Standalone script: no plugin to install.");
	}
	if (! is_file($tmp_dir . "/" . $dir_name . ".php"))
	{
		error_msg("This package does not contain a plugin file (might be a standalone script)", false);
		$plugins[$id]['script_only'] = true;
		//error_msg("Could not find plugin file in zip archive. Please contact developer and tell him to insure that his plugin is correctly named (should be: '$dir_name.php').", true);
	}
	else
	{
		$plugins[$id]['script_only'] = false;
		if (is_file($plugins_folder . $dir_name . ".php"))
		{
			if (! unlink($plugins_folder . $dir_name . ".php"))
				error_msg("Could not replace existing plugin file in the plugins folder (if you have installed a previous version manually, you might need to uninstall it manually too).", true);
			$replacing = true;
		}
		if(rename($tmp_dir . "/" . $dir_name . ".php", $plugins_folder . $dir_name . ".php"))
			status_msg("Installed plugin file ('$dir_name.php').");
		else
			error_msg("Could not move plugin file to plugin folder. Might be a permission issue.", true);
	}
	
	$file_list = wppm_listFiles($tmp_dir, "all");
	
	
	
	// Copying other files to relevant locations
	$outside_dirs = array("put_into_wp-content", "put_into_wp-includes", "put_into_wp-admin",  "put_into_wp-images", "put_into_wp-root");

	// retrieve list of files that must be preserved
	if ($updating)
	{
		$keep_files = end($update_data); // take the last update's keep_file's list

		$keep_files = str_replace(";", ".", trim($keep_files['keep_files'], ";")); // just a little bit of paranoia

		if(! empty($keep_files))
		{
			if (eval ("\$keep_files = " . $keep_files . ";") === FALSE) 
			{
				error_msg("Error reading list of files to keep for this upgrade. Proceeding with install anyway, but you should contact the developer.", false);
				unset($keep_files);
			}
		}
		else
			unset($keep_files);
	}

	if (count($file_list) > 0)
		foreach($file_list as $this_file)
		{
			$ext = strtolower(substr($this_file, -4, 4)); // just a very light check, nothing super fancy
			// NOTE: doesn't check nested folders...
			
			if ($ext{0} == ".")
				$ext = substr($ext, 1, 3);
			else
				$ext = "";
				
			if (($this_file != ".htaccess") && ($this_file{0} == "."))
			{
				error_msg("Skipping invisible file '$this_file'.", false);
			}
			elseif(!  in_array($ext, array("css", "xml", "jpg", "gif", "png", "txt", "php", "inc", "htm", "tml", "ess", "")))
			{
				error_msg("Skipping file '$this_file': extension not allowed.", false);
			}
			else
			{
				if (is_dir($tmp_dir . "/" . $this_file) && in_array($this_file, $outside_dirs))
				{
					$content = wppm_listFiles("$tmp_dir/$this_file", "all");
					foreach($content as $index => $one_file)
						if (isset($keep_files[$this_file]) && in_array($one_file, $keep_files[$this_file]))
						{
							status_msg("Preserving older file: " . $one_file);
							unset($content[$index]);
						}

					if (count($content))
						$further_installs[$this_file] = $content;

				}
				elseif (isset($keep_files[put_into_data_folder]) && in_array($this_file, $keep_files[put_into_data_folder]))
				{
					status_msg("Preserving older file: '$this_file' in data folder.");
				}
				else
				{
					wppm_create_data_folder(kWpContentFolder . $dir_name);
				
					if (file_exists(kWpContentFolder . $dir_name . "/$this_file"))
						if (rmdirr(kWpContentFolder . $dir_name . "/$this_file", true))
							status_msg("Removing old version of file '$this_file' in plugin data folder.");
						else
							error_msg("Could not overwrite '$this_file' in plugin data folder, make sure permissions are set correctly.");
			
					if(rename ($tmp_dir . "/" . $this_file, kWpContentFolder . $dir_name . "/" . $this_file))
						status_msg("Copied '$this_file' to plugin data folder.");
					else
						error_msg("Could not copy '$this_file' to plugin data folder, make sure permissions are set correctly.");
				}
			}
		}
	
	if (!empty($plugins[$id]['config_vals']))
		wppm_create_data_folder(kWpContentFolder . $dir_name);
	
	// execute SQL stuff
	if ($got_sql)
	{
		if ($updating)
		{
			foreach($update_data as $this_update)
			{
				status_msg("Applying SQL update for version: ". $this_update['version_major'] . "." . $this_update['version_minor'] . "...");
				wppm_run_sql($this_update['sql_update']);
			}
		}
		else
		{
			status_msg("Modifying SQL database...");
			wppm_run_sql($plugins[$id]['sql_command']);
		}
	}
	
	// Activate plugin
	if (! $plugins[$id]['script_only'] && ! $updating && ! @$replacing)
	{
		$active_plugins = get_settings('active_plugins');
	
		if (is_array($active_plugins))
		{
			$active_plugins[] = trim($dir_name . ".php");
			sort($active_plugins);
		}
		else
		{
			$active_plugins = "\n" . get_settings('active_plugins') . "\n";
			$active_plugins = preg_replace("|(\n)+\s*|", "\n", $active_plugins);
			$active_plugins = trim($active_plugins) . "\n " . trim($dir_name . ".php");
			$active_plugins = trim($active_plugins);
			$active_plugins = preg_replace("|\n\s*|", "\n", $active_plugins); // I don't know where this is coming from
		}
		update_option('active_plugins', $active_plugins);
		status_msg("Activated plugin: '$dir_name.php'.", true);
	}

	if (isset($further_installs) && count($further_installs))
	{
		// copy remaining files (outside of wp-content)
		$plugins[$id]['further_installs'] = $further_installs;
		
		if ($plugins[$id]['approved'] != "yes")
		{
			error_msg("This plugin contains files that must be installed outside of the 'wp-content' directory, but it has not been approved by the moderator. This can be potentially dangerous for your system.", false);
			error_msg("You can choose to override this warning by providing your FTP access information again below. Otherwise, click cancel and try installing it manually.", false);
			wppm_ftp_form ($params, 
							"<input type=\"hidden\" name=\"finish_install_id\" value=\"$id\"><input type=\"submit\" name=\"finish\" value=\"Finish Install\"> <input type=\"submit\" name=\"remove\" value=\"Cancel and Remove\"><br/><br/>");
		}
		elseif (! empty($ftp_params['password']))
		{
			wppm_copy_files ($ftp_params, $further_installs, $dir_name);
			$install_finished = true;
		}
		else
		{
			// ask for ftp password etc.
			status_msg("This plugin needs to install files outside of 'wp-content'.", true);
			echo "<br />To finish this install you need to provide your FTP settings. Otherwise click on 'remove' to cancel the install and do a manual install:<br />";
			wppm_ftp_form ($params, 
							"<input type=\"hidden\" name=\"finish_install_id\" value=\"$id\"><input type=\"submit\" name=\"finish\" value=\"Finish Install\"> <input type=\"submit\" name=\"remove\" value=\"Cancel and Remove\"><br/><br/>");
		}
	}
	else
		$install_finished = true;

	// restore old perms
	if ($oneclick_pwd)
		set_permissions($ftp_params, $old_perms);

	// update installed plugins datafile
	$installed[$id] = $plugins[$id];
	write_to_file (kData_folder . kInstalledFile, $installed);
}

if (isset($_REQUEST['finish_install_id']))
{
	if (isset($_REQUEST['remove']))
	{
		if (isset($_SESSION['second_oneclick_id']))
			unset($_SESSION['second_oneclick_id']);
		$_REQUEST['remove_oneclick_id'] = $_REQUEST['finish_install_id'];
	}
	else
	{
		$id = $_REQUEST['finish_install_id']; // Note: used later in the code

		wppm_copy_files ($_REQUEST, $installed[$id]['further_installs'], $installed[$id]['dir_name']);
		
		// update FTP settings
		foreach(array("login", "server", "path") as $param)
			$params['ftp'][$param] = $_REQUEST[$param];
		write_to_file(kData_folder . kParamsFile, $params);

		$install_finished = true;
	}
}

// Installation finished: display last instructions
if ($install_finished)
{
	wppm_configure_plugin ($installed[$id]);
	
	if (isset($_SESSION['second_oneclick_id']))
	{
		$second_id = $_SESSION['second_oneclick_id'];
		if (kBackupScriptId == $id)
			error_msg("Installed DB Backup script, click <a href=\"". $_SERVER['PHP_SELF'] . "?oneclick_id=$second_id\">here</a> to proceed with the main plugin install ('". $plugins[$second_id]['plugin_name']. "').", false);
		else
			status_msg("Please click <a href=\"". $_SERVER['PHP_SELF'] . "?oneclick_id=$second_id\">here</a> to proceed with the second install ('". $plugins[$second_id]['plugin_name']. "').", true);
		unset($_SESSION['second_oneclick_id']);
	}
}


//*** REMOVE One-click archive
$uninstall_finished = false;
if (isset($_REQUEST['remove_oneclick_id']))
{
	$remove_id = $_REQUEST['remove_oneclick_id'];

	if(isset($installed[$remove_id]))
		$plugin = $installed[$remove_id];
	elseif (isset($plugins[$remove_id]) && is_file($plugins_folder . $plugins[$remove_id]['dir_name'] . ".php"))
	{
		error_msg("This plugin was installed manually, will attempt to remove anyway.", false);
		$plugin = $plugins[$remove_id];
	}
	else
		error_msg("This plugin doesn't appear to be installed", true);

	
	$dir_name = $plugin['dir_name'];
	
	if ($oneclick_pwd)
	{
		if (empty($_SESSION['ftp_password']))
		{
			error_msg("You need to provide a valid ftp password to install this plugin (you can also permanently set the permissions <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a>).", true);
		}
		else
		{
			if (! isset($params['ftp']))
				error_msg("Invalid FTP settings. Click <a href=\"". $_SERVER['PHP_SELF'] . "?set_perms_form\">here</a> to enter your ftp settings).", true);
			$ftp_params = $params['ftp'];
			$ftp_params['password'] = $_SESSION['ftp_password'];
			status_msg("Changing permissions temporarily.");
			$old_perms = set_permissions($ftp_params, $perms);
		}
	}
	
	
	// remove all files
	if(empty($dir_name))
		error_msg("Couldn't remove plugin. Plugin dir_name empty.", true);

	if (!isset($plugin['script_only']) || !$plugin['script_only'])
		if (is_file($plugins_folder . $dir_name . ".php"))
		{
			$active_plugins = get_settings('active_plugins');
			if (is_array($active_plugins))
			{
				$key = array_search(trim($dir_name . ".php"), $active_plugins);
				if ($key !== false)
					unset($active_plugins[$key]);
			}
			else
			{
				$active_plugins = "\n" . $active_plugins . "\n";
				$active_plugins = str_replace("\n" . trim($dir_name . ".php"), '', $active_plugins);
				$active_plugins = trim(preg_replace("|(\n)+\s*|", "\n", $active_plugins));
			}
			update_option('active_plugins', $active_plugins);
			status_msg("Deactivated plugin: '$dir_name.php'.", true);

			if (! unlink ($plugins_folder . $dir_name . ".php"))
				error_msg("Couldn't remove plugin: " . $dir_name . ".php");

		}
	if (is_dir(kWpContentFolder . $dir_name) && (($dir_name != 'plugins') && ($dir_name != 'plugin_manager')))
	{
		if (isset($_REQUEST['remove_prefs']))
		{
			status_msg("Cleaning up preferences too.");
			$except = false;
		}
		else
		{
			if (is_file(kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix ))
				status_msg("Preserving preference file: " . $dir_name . kPrefSuffix);
			$except = array($dir_name . kPrefSuffix);
		}
		if (! rmdirr(kWpContentFolder . $dir_name, true, $except))
			error_msg("Couldn't remove data for plugin: " . $dir_name);
	}	

	
	// remove files located outside of wp-content
	if (isset($plugin['further_installs']) && !isset($_REQUEST['finish_install_id']))
	{
		if ($oneclick_pwd)
		{
			wppm_copy_files($ftp_params, $plugin['further_installs'], $dir_name, true);
			$uninstall_finished = true;
		}
		else
		{
			error_msg("This plugin had installed files outside of 'wp-content'.", false);
			echo "<br />To finish the removal you need to provide your FTP settings. Otherwise click on 'cancel' to skip this step (you will have to manually remove these files):<br />";
			wppm_ftp_form ($params, 
							"<input type=\"hidden\" name=\"finish_removal_id\" value=\"" . $plugin['plugin_id']. "\"><input type=\"submit\" name=\"remove\" value=\"Finish Removal\"> <input type=\"submit\" name=\"cancel\" value=\"Cancel\"><br/><br/>");
		}
	}
	else
		$uninstall_finished = true;
}

if (isset($_REQUEST['finish_removal_id']))
{
	if (! isset($installed[$_REQUEST['finish_removal_id']]))
		error_msg("This plugin has already been removed", true);
	$plugin = $installed[$_REQUEST['finish_removal_id']];
	
	if (isset($_REQUEST['cancel']))
	{
		error_msg("Skipping removal of files located outside of 'wp-content':");
		echo "<pre>", print_r($plugin['further_installs'], true), "</pre>";
	}
	else
	{
		wppm_copy_files ($_REQUEST, $plugin['further_installs'], $plugin['dir_name'], true);
		
		// update FTP settings
		foreach(array("login", "server", "path") as $param)
			$params['ftp'][$param] = $_REQUEST[$param];
		write_to_file(kData_folder . kParamsFile, $params);
	}
	
	$uninstall_finished = true;
}

// Removal finished: display last instructions
if ($uninstall_finished)
{
	if(isset($installed[$plugin['plugin_id']]))
		unset($installed[$plugin['plugin_id']]);
	
	if(write_to_file (kData_folder . kInstalledFile, $installed))
		status_msg("Updating Installed Plugin list: '". kData_folder . kInstalledFile . "'");
	else
		error_msg("Could not update Installed Plugin list: 
	'". kData_folder . kInstalledFile . "'", false);

	if ($oneclick_pwd)
		set_permissions($ftp_params, $old_perms);

	status_msg("Uninstalled plugin: ". $plugin['plugin_name'], true);
}



echo "<button onClick=\"window.location='" . $_SERVER['PHP_SELF'] . "?refresh=1'\">Check for updates</button>";
?>
<p><form action="<?=$_SERVER['PHP_SELF'] ?>" name="filter_plugins" method="get"> 
	<input type="text" size="10" name="filter"> <input type="submit" name="filter_search" value="Filter"></form>
 </p>
<?php

// DISPLAY PLUGIN LISTS
$plug_dir_list = array_flip(wppm_listFiles($plugins_folder, "php"));

foreach($plugins as $id => $plugin)
{
	if(!empty($filter) && (wppm_array_search_bit($filter, $plugin) === FALSE))
	{
		// skip plugin (not matching filter).
		unset($plugins[$id]);
		unset($installed[$id]);
	}
}

if (count($installed))
	echo "<h2 class=\"section\">One-Click Installed Plugins</h2>";
elseif(!empty($filter) && (count($plugins) == 0))
	error_msg("No plugin matching your search. Try filtering with a different string.", false);
	
foreach($plugins as $id => $plugin)
{
	if(isset($installed[$id]))
	{
		if (isset($plug_dir_list[$plugin['dir_name'] . ".php"]))
			unset($plug_dir_list[$plugin['dir_name']. ".php"]);
		if (($plugin['version_major'] != $installed[$id]['version_major']) || ($plugin['version_minor'] != $installed[$id]['version_minor']))
		{
			$status = "<span class=\"notice\">[upgrade available]</span>";
			$latest_version = $plugin['version_major'] . "." . $plugin['version_minor'];
			echo "<div class=\"plugin opened\" id=\"$id\">";
		}
		else
		{
			$status = "[up to date]";
			echo "<div class=\"plugin closed\" id=\"$id\">";
			//echo "<div class=\"plugin opened\" id=\"$id\">";
		}
		echo "<div class=\"summary\" onclick=\"javascript:switch_arrow('$id');\">";
		echo "<div class=\"arrow\" id=\"img_$id\"></div> ";
		echo "<span class=\"name\">" . $installed[$id]['plugin_name'] . "</span> <span class=\"description\">". $installed[$id]['description'] . "</span> $status";
		echo "</div><div class=\"details\">";
		echo detail_line("Name", $installed[$id]['plugin_name']);
		echo detail_line("Version Installed", $installed[$id]['version_major']. "." . $installed[$id]['version_minor']);
		if (isset($latest_version))
			echo detail_line("Version Available", $latest_version);
		echo detail_line("Category", (empty($installed[$id]['parent_name']) ? "" : $installed[$id]['parent_name'] . "/") . (empty($installed[$id]['cat_name']) ? "" : $installed[$id]['cat_name']));
		echo detail_line("Description", nl2br($installed[$id]['long_description']). "<br/><br/>");
		echo detail_line("Author", $installed[$id]['author']);
	
		if (!empty($installed[$id]['directions_url']))
			$directions = $installed[$id]['directions_url'];
		elseif (! empty($installed[$id]['plugin_url']))
			$directions = $installed[$id]['plugin_url'];
		else
			$directions = "";
		if (! empty($directions))		
			echo detail_line("Directions/Configuration", "<a href=\"$directions\">$directions</a>");
	
		echo detail_line("Author Site", "<a href=\"". $installed[$id]['author_url'] . "\">" . $installed[$id]['author_url'] . "</a>");
		
		$compat = "";
		if ($installed[$id]['wp_12'] == 'yes')
			$compat .= "WP 1.2";
		if ($installed[$id]['wp_13'] == 'yes')
		{
			if (!empty($compat))
				$compat .= " &amp; ";		
			$compat .= "WP 1.3/1.5";
		}
		echo detail_line("Compatibility", $compat);
		echo detail_line("License", $installed[$id]['license']);
		if ($plugin['changelogs'] > 0)
			echo detail_line("Change Log", "<a href=\"http://unknowngenius.com/wp-plugins/changelog.php?plugin_id=$id\" target=\"_new\">" . $plugin['changelogs'] . " Entr". (($plugin['changelogs'] > 1) ? "ies" : "y") . "</a>" );
		
		if (!empty($installed[$id]['config_vals']) && (count($installed[$id]['config_vals']) > 0))
			echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?config=$id';\">Change Settings</button><br/ >";
			
		if (! empty($latest_version))
		{
			if ($oneclick)
			{
				if ($oneclick_pwd)
					echo "<button onClick=\"oneclick_password(". $plugin['plugin_id']. ");\">One-Click Upgrade</button>";
				else
					echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?oneclick_id=". $plugin['plugin_id']. "'\">One-Click Upgrade</button>";
			}
			
			if (!empty($plugin['download_url']))
				echo "<button onClick=\"window.location='".$plugin['download_url']  . "?oneclick_id=". $plugin['plugin_id']. "'\">Manual Download</button> ";
			else
				echo "No manual download link.<br />";
		}
		
		if($id != kWppmScriptId) // can't remove itself...
		{
			if ($oneclick)
			{
				if ($oneclick_pwd)
					echo "<button class=\"remove\" onClick=\"oneclick_rem_password(". $plugin['plugin_id']. ");\">One-Click Remove</button>";
				else
					echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?remove_oneclick_id=". $installed[$id]['plugin_id']. "'\" class=\"remove\">One-Click Remove</button></a>";
			}
			else
			{
				echo "Can't use one-click removal, check your settings.<br />";
			}
		}
		echo "</div></div>";		
	}
	elseif(isset($plug_dir_list[$plugin['dir_name'] . ".php"]))
	{
		unset($plug_dir_list[$plugin['dir_name'] . ".php"]);
		$manually_installed[$id] = $plugin;
	}
	else
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
}

if (count($manually_installed))
{
	echo "<h2 class=\"section\">Manually Installed Plugins</h2>";
	foreach($manually_installed as $id => $plugin)
	{
		$plug_meta = wppm_read_plug_meta($plugins_folder . $plugin['dir_name']. ".php");
		$cur_version = ($plug_meta['version_major'] * 100) + $plug_meta['version_minor'];
		$avail_version = ($plugin['version_major'] * 100) + $plugin['version_minor'];

		if ($cur_version <= 0)
			$status = "[version unknown]";
		elseif($cur_version >= $avail_version)
			$status = "[up to date]";
		else
			$status = "<span class=\"notice\">[upgrade available]</span>";

		echo "<div class=\"plugin closed manually\" id=\"$id\">";
		echo "<div class=\"summary\" onclick=\"javascript:switch_arrow('$id');\">";
		echo "<div class=\"arrow\" id=\"img_" . $id ."\"></div> ";
		echo " <span class=\"name\">" . $plug_meta['plugin_name'] . "</span> <span class=\"description\">". $plugin['description'] . "</span> $status";
	
		echo "</div><div class=\"details\">";

		echo detail_line("Name", $plug_meta['plugin_name']);
		echo detail_line("Version Installed", $plug_meta['version_major']. "." . $plug_meta['version_minor'] . (($plug_meta['status'] != "stable") ? (" " . $plug_meta['status']) : ""));		
		if ($cur_version != $avail_version)
			echo detail_line("Version Available", $plugin['version_major']. "." . $plugin['version_minor'] . (($plugin['status'] != "stable") ? (" " . $plugin['status']) : ""));		
			
		echo detail_line("Description", $plug_meta['long_description'] . "<br/><br/>");
		$compat = "";
		if ($plugin['wp_12'] == 'yes')
			$compat .= "WP 1.2";
		if ($plugin['wp_13'] == 'yes')
		{
			if (!empty($compat))
				$compat .= " &amp; ";		
			$compat .= "WP 1.3/1.5";
		}
		echo detail_line("Author", $plugin['author']);
		echo detail_line("Compatibility", $compat);
		echo detail_line("License", $plugin['license']);
	
		echo detail_line("Plugin Site", "<a href=\"". $plugin['plugin_url'] . "\">" . $plugin['plugin_url'] . "</a>");
		echo detail_line("Author Site", "<a href=\"". $plugin['author_url'] . "\">" . $plugin['author_url'] . "</a>");

		if ($plugin['changelogs'] > 0)
			echo detail_line("Change Log", "<a href=\"http://unknowngenius.com/wp-plugins/changelog.php?plugin_id=$id\" target=\"_new\">" . $plugin['changelogs'] . " Entr". (($plugin['changelogs'] > 1) ? "ies" : "y") . "</a>" );

		if ($cur_version != $avail_version)
		{
			echo "<br />";
			if (!empty($plugin['download_url']))
				echo "<button onClick=\"window.location='". $plugin['download_url'] . "'\">Manual Download</button> ";
			else
				echo "No download link.";
			
			if ($oneclick && !empty($plugin['oneclick_url']))
				if ($oneclick_pwd)
					echo "<button onClick=\"oneclick_password(". $plugin['plugin_id']. ");\">One-Click Upgrade</button>";
				else
					echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?oneclick_id=". $plugin['plugin_id']. "'\">One-Click Install</button>";
		
		}

		if ($oneclick)
		{
			if ($oneclick_pwd)
				echo "<button onClick=\"oneclick_rem_password(". $plugin['plugin_id']. ");\" class=\"remove\">One-Click Remove</button>";
			else
				echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?remove_oneclick_id=". $plugin['plugin_id']. "'\" class=\"remove\">One-Click Remove</button>";
		}

		echo "</div></div>";
	}
}
	
if (empty($filter) && count($plug_dir_list))
{
	echo "<h2 class=\"section\">Unknown Plugins</h2>";
	foreach($plug_dir_list as $plugin_file => $index)
	{
		$plug_meta = wppm_read_plug_meta($plugins_folder . $plugin_file);

		echo "<div class=\"plugin closed manually\" id=\"unkn_$index\">";
		echo "<div class=\"summary\" onclick=\"javascript:switch_arrow('unkn_$index');\">";		
		echo "<div class=\"arrow\" id=\"img_unkn_$index\"></div> ";
		echo " <span class=\"name\">" . $plug_meta['plugin_name'] . "</span> <span class=\"description\">". $plug_meta['description'] . "</span>";
		echo "</div><div class=\"details\">";
		echo detail_line("Name", $plug_meta['plugin_name']);
		echo detail_line("Version Installed", $plug_meta['version_major']. "." . $plug_meta['version_minor'] . (($plug_meta['status'] != "stable") ? (" " . $plug_meta['status']) : ""));
		echo detail_line("Description", $plug_meta['long_description'] . "<br/><br/>");
		echo detail_line("Author", $plug_meta['author']);
	
		echo detail_line("Plugin Site", "<a href=\"". $plug_meta['plugin_url'] . "\">" . $plug_meta['plugin_url'] . "</a>");
		echo detail_line("Author Site", "<a href=\"". $plug_meta['author_url'] . "\">" . $plug_meta['author_url'] . "</a>");

		echo "</div></div>";
	}
}

if (count($plugins_s))
{
	echo "<br /><hr width=\"30%\">";
	echo "<h2 class=\"section\">Available Plugins</h2>";
	foreach($plugins_s as $parent => $cats)
	{
		if(($parent != "[empty]") && !empty($parent))
			echo "<div class=\"parent_category\">$parent</div>";
		foreach($cats as $cat => $plugs)
		{
			if($cat != "[empty]")
				echo "<div class=\"category\">$cat</div>";
	
			foreach ($plugs as $id => $plugin)
			{
				$notice = "";
				if ($refresh)
				{
					if (isset($old_plugins[$id]))
					{
						if (($plugin['version_major'] != $old_plugins[$id]['version_major']) || ($plugin['version_minor'] != $old_plugins[$id]['version_minor']))
							$notice = "<span class=\"notice\">[upgrade available]</span> ";
					}
					else
					{
						$notice = "<span class=\"notice\">[new]</span> ";
					}
				}
					
				echo "<div class=\"plugin closed". ((! empty($plugin['oneclick_url'])) ? " oneclick" : "") . "\" id=\"$id\">";
				
				echo "<div class=\"summary\" onclick=\"javascript:switch_arrow('$id');\">";
				echo "<div class=\"arrow\" id=\"img_$id\"></div> ";
	
				echo "$notice<span class=\"name\">" . $plugin['plugin_name'] . "</span> <span class=\"description\">". $plugin['description'] . "</span>";
				echo "</div><div class=\"details\">";
				echo detail_line("Name", $plugin['plugin_name']);
				echo detail_line("Version", $plugin['version_major']. "." . $plugin['version_minor']);		
				echo detail_line("Description", nl2br($plugin['long_description']). "<br/><br/>");
				echo detail_line("Author", $plugin['author']);
				echo detail_line("Plugin Site", "<a href=\"". $plugin['plugin_url'] . "\">" . $plugin['plugin_url'] . "</a>");
				echo detail_line("Author Site", "<a href=\"". $plugin['author_url'] . "\">" . $plugin['author_url'] . "</a>");
				
				$compat = "";
				if ($plugin['wp_12'] == 'yes')
					$compat .= "WP 1.2";
				if ($plugin['wp_13'] == 'yes')
				{
					if (!empty($compat))
						$compat .= " &amp; ";		
					$compat .= "WP 1.3/1.5";
				}
				echo detail_line("Compatibility", $compat);
				echo detail_line("License", $plugin['license']);
				if ($plugin['changelogs'] > 0)
					echo detail_line("Change Log", "<a href=\"http://unknowngenius.com/wp-plugins/changelog.php?plugin_id=$id\" target=\"_new\">" . $plugin['changelogs'] . " Entr". (($plugin['changelogs'] > 1) ? "ies" : "y") . "</a>" );
				
				if (!empty($plugin['download_url']))
					echo "<button onClick=\"window.location='".  $plugin['download_url'] . "'\">Download</button> ";
				else
					echo "No download link.";
				
				if ($oneclick && !empty($plugin['oneclick_url']))
					if ($oneclick_pwd)
						echo "<button onClick=\"oneclick_password(". $plugin['plugin_id']. ");\">One-Click Install</button>";
					else
						echo "<button onClick=\"window.location='". $_SERVER['PHP_SELF'] . "?oneclick_id=". $plugin['plugin_id']. "'\">One-Click Install</button>";
				echo "</div>";
				echo "</div>";
			}
		}
			
	}
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

function status_msg($str, $success = false)
{
		if ($success)
			echo "<div class=\"success_msg\">$str</div>";
		else
			echo "<div class=\"status_msg\">$str</div>";
}

function detail_line($label, $field)
{
	return "	<div class=\"line\"><span class=\"label\">$label:</span> <span class=\"field\">$field</span></div>";
}

function get_plugin_data($location)
{
	if ($file = @fopen ($location, "r"))
	{
		$buf = "";
		$i = 0;
		while (!feof($file) && $i++ < 10000)
			$buf .= fgets($file, 4096);
		fclose($file);
		
		if (!empty($buf))
		{
			$data = unserialize($buf);
			
			if ($data !== FALSE)
				return $data;
			else
			{
				error_msg("Error while fetching data (location: $location)<br />". $buf, false);
				return false;
			}
		}
		else
			return false;
	}
	else
	{
		error_msg("Cannot fetch data from location: '$location'", false);
		return false;
	}
}

function write_to_file($location, $data)
{
	if ($file = fopen ($location, "w"))
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

function ftp_set_perms ($ftp_connection, $ftp_file, $my_perms = "0777")
{
	if (@chmod($ftp_file, intval($my_perms, 8))) // doesn't cost a thing to try locally first
	{
		$res = true;
	}
	elseif (function_exists("ftp_chmod"))
	{
		$res = ftp_chmod($ftp_connection, $my_perms, $ftp_file);
	}
	else
	{
		$chmod_cmd= "CHMOD $my_perms ". $ftp_file;
		$res = @ftp_site($ftp_connection, $chmod_cmd);
	}

	if ($res === true)
		status_msg("Changed permissions for directory '$ftp_file' to '$my_perms'");
	else
	{
		error_msg("Could not change '$ftp_file' permissions. Please change manually or check the ftp settings and try again. " , false);
	}
}

function get_perms($file)
{
	return substr(sprintf('%o', fileperms($file)), -4);
}

function wppm_listFiles($dir , $type)
{ 
	 if (strlen($type) == 0) 
		 $type = "all"; 
 	$x = 0; 
 	if(! is_dir($dir)) 
	 { 
 		return $result;
 	}
 	
	$result = array();
 	$thisdir = dir($dir); 
	while($entry=$thisdir->read()) 
 	{
 		if($entry{0} != '.') 
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

function rmdirr($dirname, $delete_dir = true, $except = false)
{		
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
            rmdirr("$dirname/$entry", true, $except);
        } 
        else 
        {
           	if ($except && in_array($entry, $except))
				$delete_dir = false;
			else
				unlink("$dirname/$entry");
        }
    }
    $dir->close();
    
    if($delete_dir)
    	return rmdir($dirname);
    else
    	return true;
    
}

function set_default_params(&$params)
{
	$params['last_refresh'] = "Never";
	$params['get_wp_12'] = true;
	$params['get_wp_13'] = true;
	$params['show_prefs'] = false;
}

function set_permissions($ftp_params, $folders, $skip_warnings = false)
{
	
	if (! $ftp_connection)
	{
		if ($ftp_connection = @ftp_connect($ftp_params['server']))
		{
			status_msg ("Connected to FTP server...");
			if (@ftp_login ($ftp_connection, $ftp_params['login'], $ftp_params['password']))
				status_msg ("Logged on...");
			else
				error_msg("Could not login using username: " . $ftp_params['login'] . "<br />Please go back and make sure you entered the login and password to your ftp server correctly.", true);
		}
		else
			error_msg("Could not connect to server: " . $ftp_params['server'] . "<br />Please go back and make sure you enter the address of your ftp server correctly.", true);
	}

	$ftplist = ftp_nlist($ftp_connection, ".");
	$dirs = explode("/", $ftp_params['path']);
	$start = false;
	foreach ($dirs as $dir)
	{
		if (! $start)
			$start = in_array($dir, $ftplist);
		if ($start)
			if (! @ftp_chdir($ftp_connection, $dir))
				error_msg("Could not change to directory: " . $dir, true);
	}
	
	foreach($folders as $this_folder => $this_perm)
	{
		if (file_exists($this_folder))
		{
			$my_perms[$this_folder] = get_perms($this_folder);	
			ftp_set_perms ($ftp_connection, $this_folder, $this_perm);
		}
		elseif(! $skip_warnings)
			status_msg("Skipping permission change for file/folder: '$this_folder' (does not exist).");
	}
	
	ftp_quit($ftp_connection);
	status_msg ("Closed ftp connection...");
	return $my_perms;

}

function wppm_download_file($url, $dest_dir, $file_name, $dest_filename = "")
{
	if (empty($dest_filename))
		$dest_filename = $file_name;
		
	if( ini_get('allow_url_fopen')) {
		if(! $file = fopen ($url . $file_name, "b"))
			error_msg("Couldn't download file ". $url . $file_name, true);
		
		$file_dest = fopen($dest_dir . $dest_filename, "wb");
		$i = 0;
		while(!feof($file) && ($i++ < 10000))
			fwrite($file_dest, fread($file, 8192));
		
		fclose($file_dest);
		fclose($file);
	} else {
		// Use wget ...
		$cmd = "wget -O " .realpath($dest_dir . $dest_filename) . " " . $url . $file_name;
		status_msg(exec($cmd));
	}

	status_msg("Downloaded file: '$dest_filename'.");
}

function wppm_read_plug_meta($file_name)
{
	$plug_meta = array();
	if (! $plugin_data = implode('', file($file_name)))
		error_msg("Can't read meta data from plugin: ". $file_name, false);
	preg_match("|Plugin Name:\s*(.*)|i", $plugin_data, $name);
	$plug_meta['plugin_name'] = $name[1];
	preg_match("|Description:\s*(.*)|i", $plugin_data, $description);
	$plug_meta['long_description'] = wptexturize($description[1]);
	preg_match("|Author:\s*(.*)|i", $plugin_data, $author);
	$plug_meta['author'] = $author[1];
	preg_match("|Plugin URI:\s*(.*)|i", $plugin_data, $plugin_uri);
	$plug_meta['plugin_url'] = $plugin_uri[1];
	preg_match("|Author URI:\s*(.*)|i", $plugin_data, $author_uri);
	$plug_meta['author_url'] = $author_uri[1];

	if ( preg_match("|Version:\s*(\d*)\.(\d*)\s*([a-zA-Z]*)|i", $plugin_data, $version))
	{
		$plug_meta['version_major'] = (int) $version[1];
		$plug_meta['version_minor'] = (int) $version[2];
		switch($version[3])
		{
			case 'a':
			case 'alpha':
				$plug_meta['status'] = "alpha";
			break;
			
			case 'b':
			case 'beta':
				$plug_meta['status'] = "beta";
			break;
			
			default:
				$plug_meta['status'] = "stable";
			break;
		}
	}
	else
	{
		$plug_meta['version_minor'] = $plug_meta['version_major'] = 0;
		$plug_meta['status'] = "unknown";
	}
	
	return $plug_meta;
}

 function wppm_array_search_bit($search, $array_in)
 {
    foreach ($array_in as $key => $value)
    {
   	 if (strpos($value, $search) !== FALSE)
  	  return TRUE;
    }
    
    return FALSE;
 }
  
 function wppm_copy_files($ftp_params, $file_list, $dir_name, $remove = false)
 {
  	status_msg("Copying files outside of wp-content...", false);
 	foreach ($file_list as $this_folder => $content)
 	{
		if ($this_folder == "put_into_wp-root")
			$folder = ".";
		else
			$folder = str_replace("put_into_", "", $this_folder);
		
		$temp_perms[$folder] = kRapeMePerms;
 		
 		foreach ($content as $this_file)
			$temp_perms[$folder . "/" . $this_file] = kRapeMePerms;
 	}
 	
 	status_msg("Setting temporary permissions...", false);
	$old_temp_perms = set_permissions($ftp_params, $temp_perms, true);
 	
 	$tmp_dir = kData_folder . kTmpDir . "/";
 	if (is_dir($tmp_dir . $dir_name))
		$tmp_dir .= $dir_name . "/";

	foreach ($file_list as $this_folder => $content)
 	{
		if ($this_folder == "put_into_wp-root")
		{
			$target_folder = ".";
			if ($remove)
				status_msg("Removing files from WP root:", false);
			else
				status_msg("Copying files into WP root:", false);
		}
		else
		{
			$target_folder = str_replace("put_into_", "", $this_folder);
			if ($remove)
				status_msg("Removing files from '$target_folder':", false);
			else
				status_msg("Copying files into '$target_folder':", false);
 		}
		
 		foreach ($content as $this_file)
		{
			$target = $target_folder . "/" . $this_file;
			if (file_exists($target))
			{
				if (file_exists($target_folder . "/backup_" . $this_file))
				{
					if ($remove)
					{
						status_msg("Restoring file '$target' from backup (removing most recently added file).");
						unlink($target);
						rename($target_folder . "/backup_" . $this_file, $target);
					}	
					else
						error_msg("Backup file for file '$target' already exists. Replacing original without backup. Possible compatibility issues.", false);
				}
				else
				{
					if ($remove)
					{
						status_msg("Removing file '$target'.");
						unlink($target);
					}
					else
					{
						status_msg("Substituting file '$target' (renaming older file to: 'backup_$this_file').", false);
						rename($target, $target_folder . "/backup_" . $this_file);
					}
				}
			}
			else
				status_msg("Installing file: $target", false);
			
			if (! $remove)
				rename("$tmp_dir$this_folder/$this_file", $target);
		}
 	}
	
 	status_msg("Restoring permissions...", false);
	set_permissions($ftp_params, $old_temp_perms);

 }
 
function wppm_ftp_form($params, $submit_buttons = "<input type=\"submit\" name=\"set_ftp_settings\" value=\"Submit\">")
{
	if (isset($params['ftp']))
		$default = $params['ftp'];
	else
		$default = array("server" => str_replace("www.", "", $_SERVER['SERVER_NAME']),
						"path" => dirname(__FILE__),
						"login" => "");

?>
	<form id="ftp_info_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="set_perms_form">
	FTP server: <input type="text" name="server" value="<?= $default['server'] ?>" size="30"><br/>
	Path to Wordpress: <input type="text" name="path" value="<?= $default['path'] ?>" size="50"><br/>
	Login: <input type="text" name="login" value="<?= $default['login'] ?>"><br/>
	Password: <input type="password" name="password"><br/>
	<?=$submit_buttons?>
	<br /><br />
<?php
}

function wppm_create_data_folder($folder_path)
{
	static $already_ran = 0;
	
	if ($already_ran++)
		return;
		
	// Create plugin data folder in wp-content
	if (! is_dir($folder_path))
	{
		if (mkdir($folder_path, 0755))
			status_msg("Created plugin data folder ('$folder_path') in '". kWpContentFolder."'.");
		else
			error_msg("Could not create '$folder_path', make sure permissions are set correctly.", true);
	}
	else
	{
		status_msg("Using existing plugin data folder ('$folder_path').");
	}
}
 
function wppm_configure_plugin($plugin)
{
	$dir_name = $plugin['dir_name'];

	if (isset($_REQUEST['config_vals']))
	{
		// writing preference file

		if (is_array($_REQUEST['config_vals']))
		{
			// backward compat. (will remove in some later version)
			wppm_create_data_folder(kWpContentFolder . $dir_name);

			if (! is_file(kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix))
				status_msg("Creating configuration file: '" . kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix . "'");
			else
				status_msg("Updating configuration file: '" . kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix . "'");

			if ($pref_file = fopen(kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix, "w"))
			{
				fwrite($pref_file, "<?php\n");
				foreach($_REQUEST['config_vals'] as $var => $val)
				{
					if (!is_numeric($val) && ($val != "true") && ($val != "false"))
						$val = "\"". $val . "\"";
					fwrite($pref_file, "\$". $var . " = " . $val . ";\n");
				}
				fwrite($pref_file, "?>"); // <? <- dont mind that: just fixing BBEdit syntax highlighting
				fclose($pref_file);
				
				status_msg("Finished writing new preferences", true);
			}
			else
				error_msg("Cannot open/create pref file: " . kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix . " to save preferences, make sure it has the right permissions", false);
		}
		else
			error_msg("Invalid preference string passed. Cannot write to file. " . print_r($_REQUEST['config_vals'], true));
	}
	elseif(! empty($plugin['config_vals']))
	{
			echo "<h2>Configuring Plugin: ". $plugin['plugin_name'] . "</h2><br />";

		$plugin['config_vals'] = str_replace(";", ".", trim($plugin['config_vals'], ";")); // just a little bit of paranoia

		if (@eval ("\$config_vals = " . $plugin['config_vals'] . ";") === FALSE) 
			error_msg("Cannot proceed with auto-configuration of this plugin. You may need to install this plugin manually. Please contact the plugin developer regarding this issue.", true);

		if (!is_array($config_vals))
			error_msg("Cannot proceed with auto-configuration of this plugin (not an array). You may need to install this plugin manually. Please contact the plugin developer regarding this issue.", true);

	?>
		<form id="set_config_vals" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="set_config_vals">
		<?php
		if (is_file(kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix))
		{
			if ($old_pref_file = implode('', file(kWpContentFolder . $dir_name . "/" . $dir_name . kPrefSuffix)))
			{
				preg_match_all("|\\\$(\w*)\s*=\s*(.*);\n|", $old_pref_file, $matches, PREG_SET_ORDER);
				foreach($matches as $match)
					$old_prefs[$match[1]] = trim(trim($match[2], "\""), "'");				
			}
		}
		
		echo "<ul>";
		foreach ($config_vals as $var => $options)
		{
			if (!is_array($options))
			{
				$options = array("caption" => $options);
				error_msg("Non fatal error in plugin auto-configuration settings (invalid array), please notify the plugin developer", false);
			}
			
			
			if (isset($old_prefs[$var]))
			{
				$options['default'] = $old_prefs[$var];
				if ($installing && isset($options['overwrite']) && !$options['overwrite'])
					$options['type'] = "hidden";
			}
			

			echo "<li><span class=\"label\">";
			if (!empty($options['caption']))
				echo $options['caption'];
			else
				echo $var;
			echo "</span><b> ";
					
			switch($options['type'])
			{
				case 'bool':
				case 'boolean':
				case 'menu':
				{
					echo "<select name=\"config_vals[". $var ."]\">";
					if (($options['type'] == 'menu') && (is_array($options['options'])))
						$menu = $options['options'];
					else
					{
						$menu = array("true" => "Yes", "false" => "No");
						if (isset($options['default']))
							if ($options['default'] == false)
								$options['default'] = "false";
							elseif ($options['default'] == true)
								$options['default'] = "true";
					}
					
					foreach($menu as $key => $val)
						if (isset($options['default']) && ($options['default'] == $key))
							echo "<option value=\"$key\" selected>$val</option>";
						else
							echo "<option value=\"$key\">$val</option>";
					
					echo "</select>";
					break;
				}
				
				case 'pasword':
					echo "<input type=\"password\" name=\"config_vals[". $var ."]\" value=\"". ((!empty($options['default'])) ? $options['default'] : "") ."\" />";
					break;
					
				case 'hidden':
					if (empty($options['default']))
						$options['default'] = "";
					echo "<input type=\"hidden\" name=\"config_vals[". $var ."]\" value=\"". $options['default'] ."\" />". $options['default'];
					break;
									
				case 'number':
					echo "<input type=\"text\" name=\"config_vals[". $var ."]\" value=\"". ((empty($options['default'])) ? "" : $options['default']) ."\" size=\"4\" />";
					break;
					
				default:
				case 'text':
					echo "<input type=\"text\" name=\"config_vals[". $var ."]\" value=\"". ((empty($options['default'])) ? "" : $options['default']) ."\" size=\"". (min(max(20, strlen($options['default'])), 60)) . "\" />";
					break;
			}
	
			echo "</b></li>";
		}
		
		echo "</ul><br/>";
		
		echo "<input type=\"hidden\" name=\"config\" value=\"". $plugin['plugin_id'] . "\"><input type=\"submit\" name=\"one_click_config\" value=\"Configure\">";

		echo "</form></body></html>";
		
		exit(0);
	}
	
	if (!empty($plugin['directions_url']))
		status_msg("Please <a href=\"". $plugin['directions_url'] . "\" target=\"_new\">click here</a> to see the directions for this plugin and proceed with additional install steps if necessary.", true);

}

function wppm_run_sql ($sql_commands)
{
	$sql_commands = trim($sql_commands);
	if (empty($sql_commands))
		return;
		
	global $wpdb;
	foreach(array("posts", "users", "categories", "post2cat", "comments", "links", "linkcategories", "options", "optiontypes", "optionvalues", "optiongroups", "optiongroup_options", "postmeta") as $this_table)
		$sql_commands = str_replace("[$this_table]", $wpdb->$this_table, $sql_commands);
		
	$cmd_array = explode( ";", $sql_commands);

	foreach($cmd_array as $index => $this_cmd)
	{
		$this_cmd = trim($this_cmd);
		if(empty($this_cmd))
		{
			unset ($index);	
		}
		else
		{
			if (! @$wpdb->query($this_cmd))
			{
				error_msg("SQL Error: ". mysql_error(), false);
				$errors[]= $this_cmd;
			}
		}
	}
	
	if (isset($errors))
	{
		error_msg("There were errors running the install SQL script, you might want to try and run the following SQL queries yourself:", false);
		echo "<p><pre>";
		foreach($errors as $error)
			echo $error . ";<br />\n";
		echo "</p></pre>";
	}
	else
		status_msg("Successfully ran " . count($cmd_array) . " SQL queries.", true);
}

?>
<br />Faq, help, dev info and more available <a href="http://unknowngenius.com/wp-plugins/faq.html" target="_new">here</a>
<br />©2004 - <a href="http://unknowngenius.com/blog" target="_new">dr Dave</a> @ <a href="http://unknowngenius.com/blog" target="_new">unknowngenius.com</a>
<br />Mad C0ding Skillz: <a href="http://unknowngenius.com/blog" target="_new">dr Dave</a>
<br />Lead Guinea Pig: <a href="http://steamedpenguin.com/" target="_new">SteamedPenguin</a>
<br /><br /><button onClick="window.location='<?php echo $_SERVER['PHP_SELF'] ?>?set_perms_form'">Change One-Click Install Settings</button> <button onClick="if(confirm('Are you sure you want to RESET the Plugin Manager? All your setting files will be deleted.')) top.location.href='<?php echo $_SERVER['PHP_SELF'] ?>?reset_settings';">Erase Settings</button>
</body>
</html>