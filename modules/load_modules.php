<?php
    declare(strict_types=1);
    require_once(__DIR__."/../modules.php");
    foreach($load_modules as $module => $required) {
        $required ? require_once(__DIR__."/".$module."/Object.php") : include_once(__DIR__."/".$module."/Object.php");
    }
    unset($load_modules);
