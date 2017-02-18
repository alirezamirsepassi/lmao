<?php
/**
 * routes.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 2/18/17
 * Time: 4:46 PM
 *
 *
 *
 */

use Illuminate\Support\Facades\Input;
use Topster21\LMAO\Client;

Route::get('/lmao', function() {
    return "AYYmd";
});

Route::get('/lmao/initiate', function() {
    $client = new Client();

    $oauth_data = $client->serveLoginPage();

    session(['lmao_secret' => $oauth_data['oauth_token_secret']]);

    return redirect($client->magentoUrl . '/beheer/oauth_authorize?oauth_token=' . $oauth_data['oauth_token']);
})->middleware('web');


Route::get('/lmao/callback', function() {
    $client = new Client();

    $oauth_token = Input::get('oauth_token');
    $oauth_verifier = Input::get('oauth_verifier');
    $oauth_secret = session('lmao_secret');

    $client->completeAuthentication($oauth_token, $oauth_verifier, $oauth_secret);

    return redirect()->intended('defaultpage');

})->middleware('web');