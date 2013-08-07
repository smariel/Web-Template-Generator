<?php
	// Verify datas in $_GET
	if (!isset($_GET['generate_datas'])) die("Erreur : aucune donnée reçue");

	global $code_to_include;
	$code_to_include = array('js' => '', 'css' => '');
	
	
	// Get all datas in $_GET
	$datas			= $_GET['generate_datas'];
	$project_name	= $datas['properties']['name'];
	$page_title		= $datas['properties']['title'];
	$index_file		= "index.". $datas['properties']['main_type'];
	$folders		= $datas['folders'];
	$tools			= $datas['tools'];

	// Read the config file
	$config = file_get_contents('config.json');
	$json = json_decode($config, true);
	$list_folders = $json['folders'];
	$list_tools = $json['tools'];
	
	// Creation of unique ID for the new site
	$project_id = uniqid('site_');
	
	
	// Creation of the main site folder
	$project_dir = 'temp/'.$project_id.'/'.$project_name.'/';
	$ret = mkdir($project_dir, 0777, true);
	if(!$ret) die("Impossible de créer le dossier principal");
	
	// Creation of all sub-folders
	foreach($folders as $folder_name)
	{
		$ret = mkdir($project_dir . $folder_name);
		if(!$ret) die("Impossible de créer $folder_name");
	}
	
	// Creation of the temporary zip folders (will be removed)
	$zip_dir = $project_dir.'zip/';
	mkdir($zip_dir);
	
	
	// Get all of the tools ton include
	foreach($tools as $tool)
	{
		$tool_datas = $list_tools[$tool];
		$tool_url = $tool_datas['url'];
		$matches = array();
			
		// If the tool is a unique file (CSS or JS) or a an archive (ZIP)
		// It can be terminated by .php if it has to be executed before (exemple : read the Bootstrap page to get the real zip)
		if(preg_match("/^.+\/(.+\.(js|css|zip))(\.php)?$/i", $tool_url, $matches))
		{	
			// Getting usefull constants
			$tool_type = $matches[2];
			$tool_name = $matches[1];
			$tool_path = $project_dir.$tool_type.'/'.$tool_name;
			$zip_path  = $zip_dir . $tool_name;
			
			// Downloading the file in the correct "temp/xxx/" folder	
			copy_file($tool_url, $tool_path, true);
			$code_to_include[$tool_type] .= "\t\t" . getHTML($tool_name, $tool_type) . "\n";
			
			// if the file is a zip archive
			if($tool_type == 'zip')
			{	
				// zip openning
				$zip = new ZipArchive;
				$ret = $zip->open($zip_path);
				if(!$ret) returnJSON("Impossible d'ouvrir l'archive ZIP",false);
				
				// zip extraction in temp/zip/
				$extracted_path = $zip_dir.$tool.'/';
				$zip->extractTo($extracted_path);			
				
				// searching all js and css in $extracted_path, and copying in $project_dir
				search_for_JSorCSS($extracted_path, $project_dir);			
			}	
		}
	}
	
	// deleting the temporary zip folder
	deleteDirectory($zip_dir);
	
	// reading of the main file (future index.php or .html)
	$page = file_get_contents('local_tools/main_file.html');

	// creation of the HTML header
	$html = "<title>".$page_title."</title>\n\n";
	$html .= "\t\t<!-- CSS -->\n";
	$html .= $code_to_include['css'];
	$html .= "\n\t\t<!-- JS -->\n";
	$html .= $code_to_include['js'];
	
	// include of the header in the HTML content
	$page = preg_replace('/<title><\/title>/', $html, $page);

	// put all of the HTML in the the index.php or .html
	file_put_contents($project_dir . $index_file, $page);

	// Creation of the final/global archive
	$zip_name = 'temp/'.$project_id . '.zip';
	zipDir($project_dir, $zip_name);
	
	// return of the path of the archive
	returnJSON($zip_name, true);
	
	// destroy of the website (not the zip)
	deleteDirectory('temp/'.$project_id);
	
	
	
	
	
	
	
	// copie un fichier vers un dossier (en forçant ou non le passage par HTTP)
	// forçage HTTP utile pour exécution PHP au lieu de copie local
	function copy_file($source, $destination, $force_http = false)
	{
		if($force_http)
		{
			// si local : on repasse en http
			if(!preg_match("/^http/", $source))
			{
				$local_tools_url = preg_replace('/^(.+\/).+$/', "$1", 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
				$source = $local_tools_url.$source;
			}
		}
		
		// copie
		$ret = copy($source, $destination);
		if(!$ret) returnJSON('Impossible de copier "'. $source . '" vers "' .$destination. '" <br />', false);
	}
	
	
	// retourne le HTML d'include des JS et CSS
	function getHTML ($file_name, $type)
	{
		if($type == 'js')
			return '<script src="js/'.$file_name.'"></script>';
		elseif($type == 'css')
			return '<link rel="stylesheet" type="text/css" href="css/'.$file_name.'" />';
		else
			return null;
	}
	function getHTMLforCSS ($file_name)
	{
		
	}
		
	// Search all JS and CSS files in a folder and sub-folders
	function search_for_JSorCSS($source, $destination) 
	{
		if ($dir_handle = opendir($source)) 
		{
			while (false !== ($entry = readdir($dir_handle))) 
			{
				// Skip  ./ et ../
				if ($entry == '.' || $entry == '..')  continue;
				
				// if $entry is a folder -> recursive
				if (is_dir($source . $entry . '/')) 
				{
					search_for_JSorCSS($source . $entry . '/', $destination); // Récursivité
				}
				// else if it's a file -> analyse
				else 
				{
					// check if it's a JS or CSS file
					$matches = array();
					if(preg_match('/(\.min)?\.(js|css)/i',$entry, $matches))
					{	
						$type = $matches[2];
						
						// if the file is NOT a .min
						if($matches[1] == '')
						{
							// chek if exist a .min file in the same folder
							if(is_file($source . substr($entry,0,- strlen($matches[2])) . 'min.'.$type))
							{
								continue;
							}
						}
						
						// Copy of the file in the project folder
						copy_file($source . $entry, $destination . $type . '/' . $entry, false);
						
						// Adding a line to the HTML header
						global $code_to_include;
						$code_to_include[$type] .= "\t\t" . getHTML($entry, $type) . "\n";
					}
				}
			}
			closedir($dir_handle);
		}
	}

	// Delete a non-empty directory
	function deleteDirectory($dir)
	{
		if (!file_exists($dir)) return true;
		if (!is_dir($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) 
		{
			if ($item == '.' || $item == '..') continue;
			if (!deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) return false;
		}
		return rmdir($dir);
	}	

	
	// zip a dir and all contents
	function zipDir($source, $destination)
	{
	    if (!extension_loaded('zip') || !file_exists($source)) {
	        return false;
	    }
	
	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
	        return false;
	    }
	
	    $source = str_replace('\\', '/', realpath($source));
	
	    if (is_dir($source) === true)
	    {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
	
	        foreach ($files as $file)
	        {
	            $file = str_replace('\\', '/', $file);
	
	            // Ignore "." and ".." folders
	            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
	                continue;
	
	            $file = realpath($file);
	
	            if (is_dir($file) === true)
	            {
	                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
	            }
	            else if (is_file($file) === true)
	            {
	                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
	            }
	        }
	    }
	    else if (is_file($source) === true)
	    {
	        $zip->addFromString(basename($source), file_get_contents($source));
	    }
	
	    return $zip->close();
	}
	
	// echo a JSON msg with a status
	function returnJSON($msg, $isok)
	{
		echo '{"msg":"'.$msg.'", "status":"' . (($isok)?"ok":"err") . '"}';
	}

?>

