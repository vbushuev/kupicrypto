<?php
Route::post('/coinbase/notification',[
    'as'=>'cardpool.api.pool',
    'uses'=>'Vsb\Crypto\Controllers\CryptoController@CoinbaseNotification'
]);

?>
