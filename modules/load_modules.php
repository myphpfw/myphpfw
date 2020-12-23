<?php
    require_once(__DIR__."/../modules.php");
    foreach($load_modules as $module) {
        require_once(__DIR__."/".$module."/Object.php");
    }
