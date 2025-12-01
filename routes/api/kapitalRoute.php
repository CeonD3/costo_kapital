<?php

$map->attach('kapital.', '/kapital', function ($map) {

    $map->post('form', '/form', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'form'
    ]);

    $map->get('getFormData', '/users/{userId}/templates/{uid}/form-data', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'getFormData'
    ]);

    $map->post('store', '/store', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'store'
    ]);

    $map->post('update', '/users/{userId}/templates/{uid}/update', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'update'
    ]);

    $map->post('result', '/users/{userId}/templates/{uid}/result', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'result'
    ]);

    $map->post('detailResult', '/users/{userId}/templates/{uid}/result/detail', [
        'Controller' => 'App\Controllers\KapitalController',
        'Action' => 'detailResult'
    ]);
});
