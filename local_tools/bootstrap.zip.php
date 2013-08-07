<?php
	// initialisation de la session
	$ch = curl_init();
	
	// configuration des options
	curl_setopt($ch, CURLOPT_URL, "http://getbootstrap.com/");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	// exécution de la session
	$html = curl_exec($ch);
	
	// fermeture des ressources
	curl_close($ch);
	
	// recup URL du fichier zip
	$matches = array();
	preg_match('#href="(http://.+\.zip)"#', $html, $matches);
	$url = $matches[1];
	
	// affichage du fichier
	header("Content-Type: application/zip");
	readfile($url);
?>