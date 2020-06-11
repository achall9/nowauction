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
 * VendorsocialLogin    Twitter/Oauth2/Client Model
 *
 * @category    Ced
 * @package        Ced_VendorsocialLogin
 * @author        CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Model\Twitter\Oauth2;

class Client extends \Magento\Framework\DataObject

{


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
    protected $_customerSession;
    const REDIRECT_URI_ROUTE = 'cedvendorsociallogin/twitter/connect';

    const REQUEST_TOKEN_URI_ROUTE = 'cedvendorsociallogin/twitter/request';

    const OAUTH_URI = 'https://api.twitter.com/oauth';
    const OAUTH2_SERVICE_URI = 'https://api.twitter.com/1.1';

    const XML_PATH_ENABLED = 'ven_social/ced_sociallogin_twitter/enabled';

    const XML_PATH_CLIENT_ID = 'ven_social/ced_sociallogin_twitter/client_id';

    const XML_PATH_CLIENT_SECRET = 'ven_social/ced_sociallogin_twitter/client_secret';

    protected $clientId = null;

    protected $clientSecret = null;

    protected $redirectUri = null;

    protected $client = null;

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

        \Magento\Customer\Model\Session $customerSession,

        array $data = [])
    {

        $this->_httpClientFactory = $httpClientFactory;

        $this->_config = $config;

        $this->_url = $url;

        $this->redirectUri = $this->_url->sessionUrlVar(

            $this->_url->getUrl(self::REDIRECT_URI_ROUTE)

        );

        $this->_helperData = $helperData;

        $this->clientId = $this->_getClientId();

        $this->clientSecret = $this->_getClientSecret();

        $this->_config = $config;
        $this->_customerSession = $customerSession;

        $this->client = new \Zend_Oauth_Consumer(
            [
                'callbackUrl' => $this->redirectUri,
                'siteUrl' => self::OAUTH_URI,
                'authorizeUrl' => self::OAUTH_URI . '/authenticate',
                'consumerKey' => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );

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
     * @throws \Magento\Framework\Exception
     */

    public function setAccessToken($token)
    {
        $this->token = unserialize($token);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Oauth_Exception
     */

    public function getAccessToken()
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        }
        return serialize($this->token);
    }

    /**
     * @return string
     */

    public function createAuthUrl()
    {
        return $this->_url->getUrl(self::REQUEST_TOKEN_URI_ROUTE);
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

    public function api($endpoint, $method = 'GET', $params = [])

    {

        if (empty($this->token)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to proceed without an access token.')
            );
        }

        $url = self::OAUTH2_SERVICE_URI . $endpoint;
        $response = $this->_httpRequest($url, strtoupper($method), $params);

        return $response;
    }

    /**
     * @param null $code
     * @return \Zend_Oauth_Token_Access
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Oauth_Exception
     */

    protected function fetchAccessToken($code = null)

    {

        //if (!($params = $_REQUEST)
        if (!($params = $this->getRequest()->getParams())
            ||
            !($requestToken = $this->_customerSession
                ->getTwitterRequestToken())
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access code.')
            );
        }

        if (!($token = $this->client->getAccessToken(
            $params,
            unserialize($requestToken)
        )
        )
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access token.')
            );
        }

        $this->_customerSession->unsTwitterRequestToken();

        return $this->token = $token;
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
        $client = $this->token->getHttpClient(
            [
                'callbackUrl' => $this->redirectUri,
                'siteUrl' => self::OAUTH_URI,
                'consumerKey' => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );

        $client->setUri($url);

        switch ($method) {
            case 'GET':
                $client->setMethod(\Zend_Http_Client::GET);
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setMethod(\Zend_Http_Client::POST);
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setMethod(\Zend_Http_Client::DELETE);
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                __('Required HTTP method is not supported.')
            );
        }

        $response = $client->request();

        $decoded_response = json_decode($response->getBody());
        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401 || $status == 429)) {
                if (isset($decoded_response->error->message)) {
                    $message = $decoded_response->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }

                //throw new Ced_VendorsocialLogin_TwitterOAuthException($message);
                throw new \Magento\Framework\Exception\LocalizedException($message);
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
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


    public function fetchRequestToken()
    {
        if (!($requestToken = $this->client->getRequestToken())) {

            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve request token.')

            );
        }


        $this->_customerSession
            ->setTwitterRequestToken(serialize($requestToken));


        $this->client->redirect();

    }


}

