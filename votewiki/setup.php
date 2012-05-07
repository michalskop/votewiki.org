<?php

function api_votewiki_autoload($class_name)
{
    if (file_exists(API_ROOT . "/projects/votewiki/classes/$class_name.php"))
		require_once API_ROOT . "/projects/votewiki/classes/$class_name.php";
	else if (file_exists(API_ROOT . "/projects/data/classes/$class_name.php"))
		require_once API_ROOT . "/projects/data/classes/$class_name.php";
}
spl_autoload_register('api_votewiki_autoload');

?>
