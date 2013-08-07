<?php
	$config = file_get_contents('config.json');
	$json = json_decode($config, true);
	
	$folders = $json['folders'];
	$tools = $json['tools'];

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Web Template Generator</title>
		<meta charset="utf-8" />
		
		<!-- CSS -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/bootstrap-glyphicons.css">
		<link rel="stylesheet" href="css/main_style.css">
		
		<!-- JS -->
		<script src="js/jquery-2.0.3.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/main_script.js"></script>
	</head>
	<body>
		<header class="col-10 col-offset-1">
			<h1>Web Template Generator</h1>
		</header>
		
		<!-- div 1 : Project -->
		<div class="col-4 col-offset-1" id="section_project">
			<h3>Properties</h3>
			<div class="form-group">
				<label for="input_prj_name" class="control-label">Project Name*</label>
				<input type="text" class="form-control input-small" id="input_prj_name" placeholder="The name of the project folder">
			</div>
			
			<div class="form-group">
				<label for="input_page_title" class="control-label">Page Title</label>
				<input type="text" class="form-control input-small" id="input_page_title"  placeholder="The title of the index page">
			</div>
			
			<div class="form-group">
				<label for="sel_mainFile" class="control-label">Main file type</label>
				<select class="form-control input-small" id="sel_mainFile">
					<option value="php">PHP</option>
					<option value="html">HTML</option>
				</select>
			</div>
			
			
		</div>
		
		<!-- div 2 : Folders -->
		<div class="col-3" id="section_folders">			
			<h3>Media folders</h3>
			<?php
				foreach ($folders as $name => $datas)
				{
			?>
			<div class="checkbox">
				<label>
					<?php 
						$name = $datas['name'];
						$id = 'chk_folder_'.$name;
						$checked = ($datas['checked']) ? 'checked="checked"' : '';
						echo '<input type="checkbox" value="" id="'.$id.'" data-name="'.$name.'" '.$checked.' />';
						echo $datas['disp'];
					?>
				</label>
			</div>
			<?		
				}
			?>
			<div class="form-group">
				<label for="input_folders_others" class="control-label">Others (comma-separated)</label>
				<input type="text" class="form-control input-small" id="input_folders_others">
			</div>
		</div>
	
		<!-- div 3 : Tools -->
		<div class="col-3" id="section_tools">
			<h3>Tools & frameworks</h3>
			<?php
				foreach ($tools as $name => $datas)
				{
					$types = "[";
					foreach($datas['types'] as $type)
					{
						$types .= '"'.$type.'",';
					}
					$types = substr($types,0,-1);
					$types .= "]";
			?>
			<div class="checkbox">
				<label>
					<?php 
						$id = 'chk_tool_'.$name;
						$checked = ($datas['checked']) ? 'checked="checked"' : '';
						echo '<input type="checkbox" value="" id="'.$id.'" data-name="'.$name.'" data-types="'.$types.'" '.$checked.' />';
						echo $datas['disp'];
					?>
				</label>
			</div>
			<?php
				}
			?>
		</div>

		<!-- div 4 : Validation -->
		<div class="col-10 col-offset-1">
			<button id="btn_valid" type="button" class="btn btn-primary btn-large btn-block"><span class="glyphicon glyphicon-arrow-down"></span> Generate</button>
		</div>
		
		
		<footer class="col-10 col-offset-1">
			<p>
				Sylvain Mariel © 2013<br />
				Sur une idée originale de Damien Calmels
			</p>
		</footer>
		
		<iframe id="download_frame" src="" width="0"></iframe>
	</body>
</html>