<?php
Route::post('/api/v1/cardpool',[
    'as'=>'cardpool.api.pool',
    'uses'=>'Vsb\Pne\Controllers\CardPoolApiController@getCardPool'
]);
Route::post('/api/v1/cardpool/projects',[
    'as'=>'cardpool.api.pool',
    'uses'=>'Vsb\Pne\Controllers\CardPoolApiController@getProjects'
]);
Route::post('/api/v1/cardpool/get',[
    'as'=>'cardpool.api.get',
    'uses'=>'Vsb\Pne\Controllers\CardPoolApiController@getCardFromPool'
]);
Route::post('/api/v1/cardpool/get/rollback',[
    'as'=>'cardpool.api.rollback',
    'uses'=>'Vsb\Pne\Controllers\CardPoolApiController@getCardFromPoolRollback'
]);

?>
