<?php
/**
 * Client.php
 *
 * Author: kevin
 * Date: 2/18/17
 * Time: 5:24 PM
 *
 *
 *
 */

namespace Topster21\LMAO;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\URL;

class Client
{

    /*
     * Consumer Key
     */
    public $consumerKey;

    /*
     * Consumer Secret
     */
    public $consumerSecret;

    /*
     * The url of the Magento installation
     */
    public $magentoUrl;

    /*
     * The url the Magento OAuth-backend should redirect us to after authentication
     */
    public $callbackUrl;


    private $oauthController;


    /**
     * Client constructor.
     */
    public function __construct()
    {
        if (env("MAGENTO_OAUTH_KEY") == null)
            echo "Please set the 'MAGENTO_OAUTH_KEY' variable in your .env file.";
        if (env("MAGENTO_OAUTH_SECRET") == null)
            echo "Please set the 'MAGENTO_OAUTH_SECRET' variable in your .env file.";
        if (env("MAGENTO_OAUTH_URL") == null)
            echo "Please set the 'MAGENTO_OAUTH_URL' variable in your .env file.";

        $this->consumerKey = env("MAGENTO_OAUTH_KEY", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->consumerSecret = env("MAGENTO_OAUTH_SECRET", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->magentoUrl = env("MAGENTO_OAUTH_URL", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->callbackUrl = URL::to('/') . "/lmao/callback";

        $this->oauthController = new OAuthController($this);
    }

    public function serveLoginPage() {

        if (session('lmao_token') != null && session('lmao_secret') != null && session('lmao_status') == Status::HAS_ACCESSTOKEN) {
            // We are already logged in!
            exit;
        }

        // This will keep track of our progress
        session(['lmao_status' => Status::LOGGED_OUT]);

        $oauth_data = $this->oauthController
            ->getRequestToken();

        if (!key_exists('oauth_token', $oauth_data) || $oauth_data['oauth_token'] == nullOrEmptyString())
            throw new AuthenticationException("We did not recieve an OAuth token...");


        session(['lmao_status' => Status::HAS_REQUESTTOKEN]);

        return $oauth_data;
    }

    public function completeAuthentication($token, $verifier, $secret) {

        if ($token == nullOrEmptyString() || $verifier == nullOrEmptyString() ||
             $secret == nullOrEmptyString() || session('lmao_status') != Status::HAS_REQUESTTOKEN) {

            session(['lmao_status' => Status::LOGGED_OUT]);
            return redirect('/lmao/initialise');
        }

        $oauth_complete = $this->oauthController->getAccessToken($token, $verifier, $secret);

        session(['lmao_status' => Status::HAS_ACCESSTOKEN]);

        session(['lmao_token' => $oauth_complete['oauth_token']]);

        session(['lmao_secret' => $oauth_complete['oauth_token_secret']]);


    }
}