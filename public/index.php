<?php
    declare(strict_types=1);
    require_once(__DIR__."/../modules/load_modules.php");
    $parser = new url_parser();
    $parser->parse();
