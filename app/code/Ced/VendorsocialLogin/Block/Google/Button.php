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
 * VendorsocialLogin 	Google\Button block
 *
 * @category   	Ced
 * @package    	Ced_VendorsocialLogin
 * @author		CedCommerce Magento Core Team <connect@cedcommerce.com>
 */
namespace Ced\VendorsocialLogin\Block\Google;

class Button extends \Magento\Framework\View\Element\Template

{

    /**

     * @var \Magento\Framework\Registry

     */

    protected $_registry;

    /**

     * Facebook client model

     *

     * @var \Ced\VendorsocialLogin\Model\Google\Oauth2\Client

     */

    protected $_clientGoogle;

    protected $userInfo = null;

    /**

     *

     * @var \Magento\Customer\Model\Session

     */

    protected $_customerSession;

    /**

     * @param \Ced\VendorsocialLogin\Model\google\Oauth2\Client $clientGoogle
	 
     * @param \Magento\Framework\Registry $registry

     * @param \Magento\Customer\Model\Session $customerSession

     * @param \Magento\Framework\View\Element\Template\Context $context

     * @param array $data

     */

    public function __construct(

        \Ced\VendorsocialLogin\Model\google\Oauth2\Client $clientGoogle,

        \Magento\Framework\Registry $registry,

        \Magento\Customer\Model\Session $customerSession,

        \Magento\Framework\View\Element\Template\Context $context,

        array $data = array())

    {

        $this->_clientGoogle = $clientGoogle;

        $this->_registry = $registry;

        $this->_customerSession = $customerSession;

        $this->userInfo = $this->_registry->registry('ced_sociallogin_google_userdetails');



        parent::__construct($context, $data);

    }

    protected function _construct()

    {

        parent::_construct();

        // CSRF protection

        $this->_customerSession->setGoogleCsrf($csrf = md5(uniqid(rand(), true)));

        $this->_clientGoogle->setState($csrf);

    }

   


	/**

     * @return string

     */
    public function getButtonUrl()
    {

       if(empty($this->userInfo)) {

            return $this->_clientGoogle->createAuthUrl();

        } else {

            return $this->getUrl('cedsociallogin/google/disconnect');

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

               $text = "ced_google_connect";

        } else {

                $text = "ced_google_disconnect";
        }

        return $text;

    }
}