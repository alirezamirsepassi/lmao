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
use Topster21\LMAO\Status;

Route::get('/lmao/initiate', function () {
    $client = new Client();

    $oauth_data = $client->serveLoginPage();

    session(['lmao_secret' => $oauth_data['oauth_token_secret']]);

    return redirect($client->magentoUrl . '/' . $client->adminSlug . '/oauth_authorize?oauth_token=' . $oauth_data['oauth_token']);
})->middleware('web');


Route::get('/lmao/callback', function () {
    $client = new Client();

    $oauth_token = Input::get('oauth_token');
    $oauth_verifier = Input::get('oauth_verifier');
    $oauth_secret = session('lmao_secret');

    $client->completeAuthentication($oauth_token, $oauth_verifier, $oauth_secret);

    session(['lmao_status' => Status::LOGGED_IN]);

    return redirect()->intended('defaultpage');

})->middleware('web');

Route::get('/lmao/refused', function() {
    session()->put('lmao_status', Status::LOGGED_OUT);
    session()->forget('lmao_token');
    session()->forget('lmao_secret');

    echo "<h1>The server has refused our request...</h1>";
    echo "<p>Please make sure your account has access</p>";

    echo "<a href='/lmao/initiate'><button>Try again</button></a>";
    echo "&nbsp;&nbsp;";
    echo "<a href='/'><button>Return to home</button></a>";

});

Route::get('/lmao/logout', function () {

    echo "Logging you out...";

    session()->put('lmao_status', Status::LOGGED_OUT);
    session()->forget('lmao_token');
    session()->forget('lmao_secret');

    header("Refresh: 4; url=/");
})->middleware('web');