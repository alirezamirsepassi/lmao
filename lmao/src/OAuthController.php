<?php
/**
 *
 * MagentoOAuthController.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 17-2-17
 * Time: 18:09
 *
 * Massive props to:
 *      Bogdan Constantinescu <BogCon@yahoo.com>
 * for his example on StackOverflow: http://stackoverflow.com/a/27761671
 * It was really helpful with the signature shenanigans!
 */

namespace Topster21\LMAO;


use GuzzleHttp\Client;

/**
 * Class MagentoOAuthController
 * @package App\Http\Controllers
 *
 *
 * For more information on the Magento-specific parts:
 * http://devdocs.magento.com/guides/m1x/api/rest/authentication/oauth_authentication.html
 */
class OAuthController
{
    /*
     * Consumer Key
     */
    public $client;

    /**
     * MagentoOAuthController constructor.
     *
     * Takes values from the .env file
     * @param \Topster21\LMAO\Client $lmaoClient
     */
    public function __construct(\Topster21\LMAO\Client $lmaoClient)
    {
        $this->client = $lmaoClient;
    }

    /**
     * Gets a request token. Obtaining this token is step one in verifying a Magento user.
     * It is used later on in step two and three.
     *
     * @return array
     */
    public function getRequestToken(): array
    {

        $requestUrl = $this->client->magentoUrl . "/oauth/initiate";

        $requestUrlParams = "?oauth_callback=" . urlencode($this->client->callbackUrl);

        $response = $this->makeAPICall("POST", $requestUrl, ['oauth_callback' => $this->client->callbackUrl], $requestUrlParams);

        return $this->parseResponseToArray($response);
    }


    /**
     * Gets the final access token from the Magento API
     *
     *
     */
    public function getAccessToken($token, $verifier, $secret)
    {
        $requestUrl = $this->client->magentoUrl . '/oauth/token';

        $params = [
            'oauth_token' => $token,
            'oauth_verifier' => $verifier,
            'oauth_token_secret' => $secret
        ];

        $response = $this->makeAPICall("POST", $requestUrl, $params);

        $oauth = $this->parseResponseToArray($response);

        return $oauth;
    }


    /**
     * Makes an API-Call to the Magento backend
     *
     * @param string $method POST|GET|PUT|DELETE etc
     * @param string $requestUrl Where should we send the request?
     * @param array $extraParams
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function makeAPICall(string $method, string $requestUrl, array $extraParams = [], string $requestUrlParams = "")
    {

        $client = new Client();

        $headers = $this->getHeaders($method, $requestUrl, $extraParams);

        $result = $client->request($method, $requestUrl . $requestUrlParams, ['form_params' => $headers]);

        return $result;

    }

    /**
     * Gets the headers needed to perform a request
     *
     * @param string $method POST|GET|PUT|DELETE etc
     *
     * @param string $requestUrl
     * @param array $extraparams
     * @return array
     */
    private function getHeaders(string $method, string $requestUrl, array $extraparams = []): array
    {

        $headers = $this->getParameters();

        if (sizeof($extraparams != 0)) {
            $headers = array_merge($extraparams, $headers);
        }

        // If there is a tokensecret sent with this request, we need to save it and remove it
        // from the array, since the token secret should not be in the POST headers.
        $tokenSecret = "";
        if (key_exists('oauth_token_secret', $headers)) {
            $tokenSecret = $headers['oauth_token_secret'];
            unset($headers['oauth_token_secret']);
        }

        $headers['oauth_signature'] = $this->getSignature($headers, $method, $requestUrl, $tokenSecret);

        return $headers;
    }

    /**
     * Gets the parameters used in a request
     *
     * @return array
     */
    private function getParameters(): array
    {

        return [
            'oauth_consumer_key' => $this->client->consumerKey,
            'oauth_nonce' => uniqid(mt_rand(1, 1000)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
        ];
    }

    /**
     * Parses the response from the API to an array we can use
     *
     *
     * @param $response
     * @return array
     */
    private function parseResponseToArray($response): array
    {
        $responseBodyArray = [];

        if ($response->getStatusCode() == 200) {

            $responseBody = $response->getBody();

            // Splitting the OAuth string into an array
            $parts = explode('&', $responseBody);

            // Binding key => value
            foreach ($parts as $part) {
                list($key, $value) = explode("=", $part);
                $responseBodyArray[$key] = $value;
            }
        }

        return $responseBodyArray;
    }

    /**
     * Gets a signature for a request.
     *
     *
     * return base64_encode(hash_hmac('SHA1', $string, $key, 1));
     *
     * @param array $parameters
     * @param string $method
     * @param string $requestUrl
     * @param string $tokenSecret
     * @return string
     */
    private function getSignature(array $parameters, string $method, string $requestUrl, string $tokenSecret = ""): string
    {

        ksort($parameters);
        $encodedParameters = [];

        foreach ($parameters as $key => $parameter) {
            $encodedParameters[] = rawurlencode($key) . '=' . rawurlencode($parameter);
        }

        // urlencode(PARAMETERS_ORDERED_AND_NORMALIZED_AND_WITHOUT_OAUTHSIGNATURE)
        $finalParameterString = implode('&', $encodedParameters);

        // HTTP_METHOD&urlencode(BASE_URL_OF_RESOURCE)& + PARAMS
        $signatureData = strtoupper($method)
            . '&'
            . rawurlencode($requestUrl)
            . '&'
            . rawurlencode($finalParameterString);

        // urlencode(CONSUMER_SECRET)&urlencode(tokensecret)
        $key = rawurlencode($this->client->consumerSecret)
            . '&' . rawurlencode($tokenSecret);

        return base64_encode(hash_hmac('SHA1', $signatureData, $key, 1));
    }

}