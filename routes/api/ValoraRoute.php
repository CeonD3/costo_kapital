<?php

$map->attach('valora.', '/valora', function ($map) {

    $map->post('form', '/form',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'form'
    ]);

    $map->post('store', '/store',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'store'
    ]);

    $map->post('update', '/users/{userId}/templates/{uid}/update',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'update'
    ]);

    $map->post('balance', '/users/{userId}/templates/{uid}/balance',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'balance'
    ]);
    
    $map->post('result', '/users/{userId}/templates/{uid}/result',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'result'
    ]);

    $map->post('detailResult', '/users/{userId}/templates/{uid}/result/detail',[
        'Controller' => 'App\Controllers\ValoraController',
        'Action' => 'detailResult'
    ]);

});

?>