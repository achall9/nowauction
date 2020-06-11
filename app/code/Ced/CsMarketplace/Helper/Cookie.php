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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
class Cookie extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /* Vendor cookie name*/
    CONST VENDOR_COOKIENAME = 'remember';

    /*Vendor cookie life time*/
    CONST VENDOR_COOKIELIFE = 2592000;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * Cookie constructor.
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ){
        $this->_scopeConfigInterface = $scopeConfigInterface;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
    }
    
    /**
     * Get cookie data
     *
     * @return value
     */
    public function get($cookie_name)
    {
        return $this->_cookieManager->getCookie($cookie_name);
    }
    
    /**
     * Set data in cookie
     *
     */
    public function set($cookie_name, $cookie_value, $cookie_time = 2592000)
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($cookie_time)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());
        $this->_cookieManager->setPublicCookie($cookie_name, $cookie_value, $metadata);
    }
    
    /**
     * delete cookie remote address
     *
     */
    public function delete($cookie_name)
    {
        $this->_cookieManager->deleteCookie(
            $cookie_name,
            $this->_cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain())
        );
    }
    
    /**
     * Cookie user id
     */
    public function getCookieUserId()
    {
        $cookieUser = json_decode($this->get(self::VENDOR_COOKIENAME));
        if($cookieUser)
        return $cookieUser->userId ? $cookieUser->userId : '';
    }

    /**
     * Cookie user email
     */
    public function getCookieEmail()
    {
        $cookieUser = json_decode($this->get(self::VENDOR_COOKIENAME));
        if($cookieUser)
        return $cookieUser->userEmail ? $cookieUser->userEmail : '';
    }

    /**
     * Cookie user password
     */
    public function getCookieUserPassword()
    {
        $cookieUser = json_decode($this->get(self::VENDOR_COOKIENAME));
        if($cookieUser)
        return $cookieUser->userPass ? base64_decode($cookieUser->userPass) : '';
    }
    
    
    /**
     * Cookie check remember me
     */
    public function getCookieLoginCheck()
    {
        $cookieUser = json_decode($this->get(self::VENDOR_COOKIENAME));
        if($cookieUser)
        return $cookieUser->rememberMeCheckbox ? 1 : '';
    }
    
    /**
     * @return cookie life time in seconds
     */
    public function getCookieLifeTime()
    {
        return self::VENDOR_COOKIELIFE;
    }
}