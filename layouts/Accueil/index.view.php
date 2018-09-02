<?php

use project\dao\user_dao;

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
    '.($test5 ? 'true' : 'false').'<br />';

if(isset($users)) {
	$tableau_users = '<table>';
	if(!empty($users)) {
		/**
		 * @var user_dao $user0
		 */
		$user0 = $users[0];
		$tableau_users .= '<thead><tr>';
		foreach ($user0->get_fields() as $field) {
			if($field !== 'id') {
				$tableau_users .= '<th>'.$field.'</th>';
			}
		}
		$tableau_users .= '</tr></thead>';
	}
	else {
		$tableau_users .= '<tbody>
		<tr>
			<th>Aucun utilisateur inscrit</th>
		</tr>
	</tbody>';
	}
	$tableau_users .= '<tbody>';
		foreach ($users as $user) {
			/**
			 * @var user_dao $user
			 */
			$tableau_users .= '<tr>';
			foreach ($user->get_fields() as $field) {
				if ($field !== 'id') {
					$tableau_users .= '<td>';
					$tableau_users .= (($field_value = $user->get_field($field)) !== null) ? $field_value : '<center>//</center>';
					$tableau_users .= '</td>';
				}
			}
			$tableau_users .= '</tr>';
		}
	$tableau_users .= '</tbody>';
	$tableau_users .= '</table>';
}
else {
	$tableau_users = '';
}

$retour .= $tableau_users.'<br />';

if(isset($path_array)) {
	$tableau_paths = '<ul>';
	foreach ($path_array as $directory => $file_and_directory_array) {
		$tableau_paths .= '<li><b>'.$directory.'</b>';
		$tableau_paths .= '<ul>';
		foreach ($file_and_directory_array as $file_or_directory => $path_or_array) {
			$tableau_paths .= '<li>';
			if (is_array($path_or_array)) {
				$tableau_paths .= '<ul><b>'.$file_or_directory.'</b>';
				foreach ($path_or_array as $path) {
					$tableau_paths .= '<li><i>'.$path.'</i></li>';
				}
				$tableau_paths .= '</ul>';
			} else {
				$tableau_paths .= '<i>'.$path_or_array.'</i>';
			}
			$tableau_paths .= '</li>';
		}
		$tableau_paths .= '</ul></li>';
	}
	$tableau_paths .= '</ul>';
}
else {
	$tableau_paths = '';
}

$retour .= $tableau_paths;
$retour .= '<br /><br />';
$retour .= '<a href="cssdoc.php">CssDoc</a> | <a href="page1.php">page 1</a>';
$retour .= '</body>
</html>';

return $retour;