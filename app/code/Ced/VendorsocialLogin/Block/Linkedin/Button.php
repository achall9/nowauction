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
 * VendorsocialLogin 	Linkedin\Button block
 *
 * @category   	Ced
 * @package    	Ced_VendorsocialLogin
 * @author		CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Block\Linkedin;

class Button extends \Magento\Framework\View\Element\Template

{

    /**

     * @var \Magento\Framework\Registry

     */

    protected $_registry;

    /**

     * Facebook client model

     *

     * @var \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client

     */

    protected $_clientLinkedin;

    /**

     *

     * @var \Magento\Customer\Model\Session

     */

    protected $_customerSession;

    protected $userInfo =null;

    /**
     * Button constructor.
     * @param \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $clientLinkedin
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */

    public function __construct(

        \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $clientLinkedin,

        \Magento\Framework\Registry $registry,

        \Magento\Customer\Model\Session $customerSession,

        \Magento\Framework\View\Element\Template\Context $context,

        array $data = [])

    {

        $this->_clientLinkedin = $clientLinkedin;

        $this->_registry = $registry;

        $this->_customerSession = $customerSession;

        $this->userInfo = $this->_registry->registry('ced_sociallogin_linkedin_userdetails');

       parent::__construct($context, $data);

    }

    protected function _construct()

    {

        parent::_construct();

        $this->_customerSession->setLinkedinCsrf($csrf = md5(uniqid(rand(), true)));

        $this->_clientLinkedin->setState($csrf);

    }

  


	/**

     * @return string

     */
    public function getButtonUrl()
    {
        if(empty($this->userInfo)) {

            return $this->_clientLinkedin->createAuthUrl();

        } else {

            return $this->getUrl('cedsociallogin/linkedin/disconnect');

        }

    }





	/**

     * @return string

     */

    public function getButtonText()

    {
        if(empty($this->userInfo)) {
            if(!($text = $this->_registry->registry('ced_sociallogin_button_text'))){
                $text = $this->__('Connect');
            }
       } else {
           $text = $this->__('Disconnect');
        }
        return $text;
    }




	/**

     * @return string

     */

    public function getButtonClass()
    {
        if(empty($this->userInfo)) {
                $text = "ced_linkedin_connect";
       } else {
                $text = "ced_linkedin_disconnect";
        }
        return $text;
    }
}