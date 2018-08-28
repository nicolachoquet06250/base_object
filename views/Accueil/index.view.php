<?php

$retour = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>'.$page_name.'</title>
</head>
<body>
	'.$template_name.'<br />
    '.$prenom.'<br />
    <a href="'.$src.'">'.$src.'</a><br />
    '.$nom.'<br />
    '.$date_naissence.'<br />
    '.$test1.'<br />
    '.$test2.'<br />
    '.$test3.'<br />
    '.$test4.'<br />
    '.$test5.'<br />
    '.$tableau_users;
$retour .= '<br />';
//foreach ($path_array as $directory => $file_array) {
//	foreach ($file_array as $file) {
//		$retour .= $directory.$file.'<br>';
//	}
//}
$retour .= '</body>
</html>';

return $retour;