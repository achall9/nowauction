<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     VendorsocialLogin
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * VendorsocialLogin    Google/Oauth2/Client Model
 *
 * @category    Ced
 * @package        Ced_VendorsocialLogin
 * @author        CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Model\Google\Oauth2;


class Client extends \Magento\Framework\DataObject

{


    const REDIRECT_URI_ROUTE = 'cedvendorsociallogin/google/connect';


    const XML_PATH_ENABLED = 'ven_social/ced_sociallogin_google/enabled';


    const XML_PATH_CLIENT_ID = 'ven_social/ced_sociallogin_google/client_id';


    const XML_PATH_CLIENT_SECRET = 'ven_social/ced_sociallogin_google/client_secret';


    const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';


    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';


    const OAUTH2_AUTH_URI = 'https://accounts.google.com/o/oauth2/auth';


    const OAUTH2_SERVICE_URI = 'https://www.googleapis.com/oauth2/v2';


    /**
     * @var \Magento\Backend\App\ConfigInterface
     */

    protected $_config;

    /**
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */

    protected $_httpClientFactory;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */

    protected $_url;

    /**
     *
     * @var \Ced\VendorsocialLogin\Helper\Data
     */

    protected $_helperData;

    protected $isEnabled = null;


    protected $clientId = null;


    protected $clientSecret = null;


    protected $redirectUri = null;


    protected $state = '';


    protected $scope = array(


        'https://www.googleapis.com/auth/userinfo.profile',


        'https://www.googleapis.com/auth/userinfo.email',


    );


    protected $access = 'offline';


    protected $prompt = 'auto';


    protected $token = null;

    /**
     *
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\UrlInterface $url
     * @param \Ced\VendorsocialLogin\Helper\Data $helperData
     * @param array $data
     */

    public function __construct(

        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,

        \Magento\Backend\App\ConfigInterface $config,

        \Magento\Framework\UrlInterface $url,

        \Ced\VendorsocialLogin\Helper\Data $helperData,
        
        \Magento\Framework\App\RequestInterface $request,

        // Parent

        array $data = array())

    {

        $this->_httpClientFactory = $httpClientFactory;

        $this->_config = $config;

        $this->_url = $url;
        
        $this->_request = $request;

        $this->redirectUri = $this->_url->sessionUrlVar(

            $this->_url->getUrl(self::REDIRECT_URI_ROUTE)

        );

        $this->_helperData = $helperData;

        $this->clientId = $this->_getClientId();


        $this->clientSecret = $this->_getClientSecret();

        $this->_config = $config;

        parent::__construct($data);

    }

    /**
     * @return bool
     */

    public function isEnabled()

    {

        return (bool)$this->_isEnabled();

    }


    /**
     * @return mixed|string
     */

    public function getClientId()

    {

        return $this->clientId;

    }

    /**
     * @return mixed|string
     */

    public function getClientSecret()

    {

        return $this->clientSecret;

    }

    /**
     * @return string
     */

    public function getRedirectUri()

    {

        return $this->redirectUri;

    }

    /**
     * @return array
     */

    public function getScope()

    {

        return $this->scope;

    }

    /**
     * @return string
     */

    public function getState()

    {

        return $this->state;

    }

    /**
     * @param $state
     */

    public function setState($state)

    {

        $this->state = $state;

    }

    /**
     * @param \StdClass $token
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */

    public function setAccessToken(\StdClass $token)

    {

        $this->token = $token;

        $this->extendAccessToken();


    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getAccessToken()

    {

        // return $this->token;


        if (empty($this->token)) {


            $this->fetchAccessToken();


        } else if ($this->isAccessTokenExpired()) {


            $this->refreshAccessToken();


        }


        return json_encode($this->token);


    }

    /**
     * @return string
     */

    public function createAuthUrl()

    {


        $url =


            self::OAUTH2_AUTH_URI . '?' .


            http_build_query(


                array(


                    'response_type' => 'code',


                    'redirect_uri' => $this->redirectUri,


                    'client_id' => $this->clientId,


                    'scope' => implode(' ', $this->scope),


                    'state' => $this->state,


                    'access_type' => $this->access,


                    'approval_prompt' => $this->prompt


                )


            );


        return $url;

    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */

    public function api($endpoint, $method = 'GET', $params = array())

    {

        try{
            
        if (empty($this->token)) {

            $this->fetchAccessToken();


        } else if ($this->isAccessTokenExpired()) {

            $this->refreshAccessToken();


        }


        $url = self::OAUTH2_SERVICE_URI . $endpoint;


        $method = strtoupper($method);
        
        $token = json_decode(json_encode($this->token), True);
        
        if(isset($token['error'])){
            throw new \Exception(__($token['error_description']));
            
        }

        $params = array_merge(array(


            'access_token' => $this->token->access_token


        ), $params);


        $response = $this->_httpRequest($url, $method, $params);


        return $response;
        
        }
        
        catch (\Exception $e) {
            throw new \Exception(

                __($e->getMessage())

            );
        }

    }

    /**
     * @param null $code
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function fetchAccessToken($code = null)

    {


        //if (empty($_REQUEST['code'])) {
        if (empty($this->_request->getParam('code'))) {


            throw new \Magento\Framework\Exception(__('Unable to retrieve access code.'));
        }


        $response = $this->_httpRequest(


            self::OAUTH2_TOKEN_URI,


            'POST',


            array(


                //'code' => $_REQUEST['code'],
                'code' => $this->_request->getParam('code'),


                'redirect_uri' => $this->redirectUri,


                'client_id' => $this->clientId,


                'client_secret' => $this->clientSecret,


                'grant_type' => 'authorization_code'


            )


        );


        $response->created = time();


        $this->token = $response;


    }

    /**
     * @return string
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */

    public function extendAccessToken()

    {

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {

            /*throw new \Magento\Framework\Exception(

                __('Unable to retrieve access token.')

            );*/
            throw new \Magento\Framework\Exception(__('Unable to retrieve access token.'));

        }

        // Expires over two hours means long lived token

        if ($accessToken->expires > 7200) {

            // Long lived token, no need to extend

            return $this->getAccessToken();

        }

        $response = $this->_httpRequest(

            self::OAUTH2_TOKEN_URI,

            'GET',

            array(

                'client_id' => $this->_getClientId(),

                'client_secret' => $this->getClientSecret(),

                'fb_exchange_token' => $this->getAccessToken()->access_token,

                'grant_type' => 'fb_exchange_token'

            )

        );

        $this->setAccessToken($response);

        return $this->getAccessToken();

    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function _httpRequest($url, $method = 'GET', $params = array())

    {


        $client = $this->_httpClientFactory->create();

        $client->setUri($url);

        switch ($method) {


            case 'GET':


                $client->setParameterGet($params);


                break;


            case 'POST':


                $client->setParameterPost($params);


                break;


            case 'DELETE':


                break;


            default:
                throw new \Magento\Framework\Exception(__('Required HTTP method is not supported.'));

        }


        $response = $client->request($method);


        $decoded_response = json_decode($response->getBody());


        if ($response->isError()) {


            $status = $response->getStatus();


            if (($status == 400 || $status == 401)) {


                if (isset($decoded_response->error->message)) {


                    $message = $decoded_response->error->message;


                } else {


                    $message = 'Unspecified OAuth error occurred.';


                }


            } else {


                $message = sprintf(


                    'HTTP error %d occurred while issuing request.',


                    $status


                );


                throw new \Magento\Framework\Exception\LocalizedException($message);


            }


        }


        return $decoded_response;


    }

    /**
     * @return mixed
     */

    protected function _isEnabled()

    {

        return $this->_getStoreConfig(self::XML_PATH_ENABLED);

    }

    /**
     * @return mixed
     */

    protected function _getClientId()

    {

        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);

    }

    /**
     * @return mixed
     */

    protected function _getClientSecret()

    {

        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);

    }

    /**
     * @param $xmlPath
     * @return mixed
     */

    protected function _getStoreConfig($xmlPath)

    {

        return $this->_config->getValue($xmlPath);

    }


    public function revokeToken()


    {


        if (empty($this->token)) {


            throw new \Magento\Framework\Exception(__('No access token available.'));


        }


        if (empty($this->token->refresh_token)) {
            throw new \Magento\Framework\Exception(__('No refresh token, nothing to revoke.'));
        }


        $this->_httpRequest(


            self::OAUTH2_REVOKE_URI,


            'POST',


            array(


                'token' => $this->token->refresh_token


            )


        );


    }


    protected function refreshAccessToken()


    {


        if (empty($this->token->refresh_token)) {
            throw new \Magento\Framework\Exception(__('No refresh token, unable to refresh access token.'));
        }


        $response = $this->_httpRequest(


            self::OAUTH2_TOKEN_URI,


            'POST',


            array(


                'client_id' => $this->clientId,


                'client_secret' => $this->clientSecret,


                'refresh_token' => $this->token->refresh_token,


                'grant_type' => 'refresh_token'


            )


        );


        $this->token->access_token = $response->access_token;


        $this->token->expires_in = $response->expires_in;


        $this->token->created = time();


    }


    protected function isAccessTokenExpired()
    {


        // If the token is set to expire in the next 30 seconds.


        $expired = ($this->token->created + ($this->token->expires_in - 30)) < time();


        return $expired;


    }


}

