<?php

$map->attach('api.', '/api', function ($map) {

    require_once __DIR__ . "/api/kapitalRoute.php";
    require_once __DIR__ . "/api/ValoraRoute.php";
});
