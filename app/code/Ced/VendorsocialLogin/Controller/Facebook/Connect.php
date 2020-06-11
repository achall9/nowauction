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
 * VendorsocialLogin    Facebook controller
 *
 * @category    Ced
 * @package        Ced_VendorsocialLogin
 * @author        CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Controller\Facebook;

use Magento\Framework\App\Action\NotFoundException;

class Connect extends \Magento\Framework\App\Action\Action

{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Facebook
     */
    protected $_helperFacebook;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client
     */
    protected $_client;

    protected $_accountRedirect;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Facebook $helperFacebook
     * @param \Magento\Customer\Model\Account\Redirect $accountRedirect
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Facebook $helperFacebook,
        \Magento\Customer\Model\Account\Redirect $accountRedirect
    )
    {
        $this->_customerSession = $customerSession;
        $this->_client = $client;
        $this->_accountRedirect = $accountRedirect;
        $this->_helperFacebook = $helperFacebook;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Exception
     * @throws \Magento\Framework\Exception
     */

    public function execute()

    {
        try {
            $this->_connectCallback();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __("Some error during Facebook login.")
            );
        }
        return $this->_sendResponse();
    }

    /**
     * @throws \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */

    protected function _connectCallback()
    {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if (!($errorCode || $code) && !$state) {
            /* Direct route access - deny*/
            return;
        }
        if (!$state || $state != $this->_customerSession->getFacebookCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Security check failed. Please try again.')
            );

        }

        $this->_customerSession->setFacebookCsrf('');

        if ($errorCode) {
            /* Facebook API read light - abort*/
            if ($errorCode === 'access_denied') {
                $this->messageManager
                    ->addNotice(
                        __('Facebook Connect process aborted.')
                    );
                return;
            }

            throw new \Magento\Framework\Exception\LocalizedException(
                sprintf(
                    __('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );
            return;
        }

        if ($code) {
            $client = $this->_client;
            $userInfo = $client->api('/me?fields=id,name,first_name,last_name,email');
            $token = $client->getAccessToken();
            $customersByFacebookId = $this->_helperFacebook
                ->getCustomersByFacebookId($userInfo->id);

            if ($this->_customerSession->isLoggedIn()) {
                /* Logged in user*/
                if ($customersByFacebookId->count()) {
                    /* Facebook account already connected to other account - deny*/
                    $this->messageManager
                        ->addNotice(
                            __('Your Facebook account is already connected to one of our store accounts.')
                        );
                    return;
                }

                /* Connect from account dashboard - attach*/
                $customer = $this->_customerSession->getCustomer();
                $this->_helperFacebook->connectByFacebookId(
                    $customer,
                    $userInfo->id,
                    $token
                );
                $this->messageManager->addSuccess(
                    __('Your Facebook account is now connected to your new user accout at our store. 
                    You can login next time by the Facebook SocialLogin button or Store user account. 
                    Account confirmation mail has been sent to your email.')
                );
                return;
            }

            if ($customersByFacebookId->count()) {
                /* Existing connected user - login*/
                $customer = $customersByFacebookId->getFirstItem();
                $this->_helperFacebook->loginByCustomer($customer);
                $this->messageManager->addSuccess(
                    __('You have successfully logged in using your Facebook account.')
                );
                return;
            }

            $customersByEmail = $this->_helperFacebook
                ->getCustomersByEmail($userInfo->email);

            if ($customersByEmail->count()) {
                /* Email account already exists - attach, login*/
                $customer = $customersByEmail->getFirstItem();
                $this->_helperFacebook->connectByFacebookId(
                    $customer->getId(),
                    $userInfo->id,
                    $token
                );

                $this->messageManager->addSuccess(
                    __('We find you already have an account at our store. 
                    Your Facebook account is now connected to your store account. 
                    Account confirmation mail has been sent to your email.')
                );

                return;
            }

            /* New connection - create, attach, login*/

            if (empty($userInfo->first_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your Facebook first name. Please try again.')
                );
            }

            if (empty($userInfo->last_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(

                    __('Sorry, could not retrieve your Facebook last name. Please try again.')
                );
            }

            $this->_helperFacebook->connectByCreatingAccount(

                $userInfo->email,
                $userInfo->first_name,
                $userInfo->last_name,
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccess(

                __('Your Facebook account is now connected to your new user accout at our store. 
                You can login next time by the Facebook SocialLogin button or Store user account. 
                Account confirmation mail has been sent to your email.')
            );
        }
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _sendResponse()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account');
        return $resultRedirect;
    }

}