<?php
$files = array(); 
$folder = './';

$handle = opendir($folder);
$exts = explode(' ','jpg jpeg png gif');
while (false !== ($file = readdir($handle))) {
	foreach($exts as $ext) { // for each extension check the extension
		if (preg_match('/\.'.$ext.'$/i', $file, $test)) { // faster than ereg, case insensitive
			$files[] = $file; // it's good
		}
	}
}
closedir($handle); // We're not using it anymore
$rand = array_rand($files);
CSRF::header('Location: '.$folder.$files[$rand]); // Voila!
?>
