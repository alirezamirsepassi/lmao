<?php
/**
 * Client.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 2/18/17
 * Time: 5:24 PM
 *
 *
 *
 */

namespace Topster21\LMAO;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Routing\Exception\InvalidParameterException;

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

    /*
     * The admin slug. Is not the same for every language, you can change that in the .env file with LMAO_ADMIN_SLUG
     * For instance: if your url for the admin page is : yourmagentoshop.nl/beheer/ you change that to beheer.
     * In the .env file: LMAO_ADMIN_SLUG=beheer
     */
    public $adminSlug;


    private $oauthController;


    /**
     * Client constructor.
     */
    public function __construct()
    {
        /*
         * Some error message stuff.
         * The user should know when something is not configured correctly eh?
         */

        $errormsg = "Please make sure these keys and values are present in your .env file: \n
    \nLMAO_KEY=\nLMAO_SECRET=\nLMAO_URL=";

        if (env("LMAO_KEY") == null || env("LMAO_KEY") == "")
            throw new InvalidParameterException($errormsg . "\n\nPlease set the 'LMAO_KEY' variable in your .env file.");
        if (env("LMAO_SECRET") == null || env("LMAO_SECRET") == "")
            throw new InvalidParameterException($errormsg . "\n\nPlease set the 'LMAO_SECRET' variable in your .env file.");
        if (env("LMAO_URL") == null || env("LMAO_URL") == "")
            throw new InvalidParameterException($errormsg . "\n\nPlease set the 'LMAO_URL' variable in your .env file.");


        $this->consumerKey = env("LMAO_KEY", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->consumerSecret = env("LMAO_SECRET", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->magentoUrl = env("LMAO_URL", "SET THIS VALUE IN YOUR .ENV FILE");
        $this->adminSlug = env("LMAO_ADMIN_SLUG", 'admin');

        $this->callbackUrl = URL::to('/') . "/lmao/callback";

        $this->oauthController = new APIController($this);
    }

    public function serveLoginPage()
    {

        if (session('lmao_token') != null && session('lmao_secret') != null && session('lmao_status') == Status::LOGGED_IN) {
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

    public function completeAuthentication($token, $verifier, $secret)
    {

        if ($token == nullOrEmptyString() || $verifier == nullOrEmptyString() ||
            $secret == nullOrEmptyString() || session('lmao_status') != Status::HAS_REQUESTTOKEN
        ) {

            session(['lmao_status' => Status::LOGGED_OUT]);
            return redirect('/lmao/initialise');
        }

        $oauth_complete = $this->oauthController->getAccessToken($token, $verifier, $secret);

        session(['lmao_status' => Status::HAS_ACCESSTOKEN]);

        session(['lmao_token' => $oauth_complete['oauth_token']]);

        session(['lmao_secret' => $oauth_complete['oauth_token_secret']]);
    }
}
