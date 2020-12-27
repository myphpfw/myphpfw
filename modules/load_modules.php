<?php
    declare(strict_types=1);
    require_once(__DIR__."/../modules.php");
    $loaded_modules = [];
    foreach($load_modules as $module => $required) {
        $required ? require_once(__DIR__."/".$module."/Object.php") : include_once(__DIR__."/".$module."/Object.php");
        array_push($loaded_modules, $module);
    }
    unset($load_modules);
