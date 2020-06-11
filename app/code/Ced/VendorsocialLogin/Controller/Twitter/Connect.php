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

      
namespace Ced\VendorsocialLogin\Controller\Twitter;
use Magento\Framework\App\Action\NotFoundException;
 
class Connect extends \Magento\Framework\App\Action\Action
 
{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Twitter
     */
    protected $_helperTwitter;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client
     */
    protected $_client;
    protected $_accountRedirect;

    /**
     * Connect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Twitter $helperTwitter
     * @param \Magento\Customer\Model\Account\Redirect $accountRedirect
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Twitter $helperTwitter,
        \Magento\Customer\Model\Account\Redirect $accountRedirect
    ){
        $this->_customerSession = $customerSession;
        $this->_client = $client;
        $this->_accountRedirect = $accountRedirect;
        $this->_helperTwitter = $helperTwitter;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->_connectCallback();
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __("Some error during Twitter login.")
            );
        }
        return $this->_sendResponse();
    }
    
    
    /**
    * connect to twitter account
    */

    protected function _connectCallback() {
        if (!($params = $this->getRequest()->getParams()) ||
            !($requestToken = unserialize($this->_customerSession
                ->getTwitterRequestToken()))
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                    __('Twitter Connect process aborted')
            );
            return;
        }

        $this->referer = $this->_customerSession->getTwitterRedirect();

        if(isset($params['denied'])) {
            $this->messageManager
            ->addNotice(
                __('Twitter Connect process aborted.')
            );
            return;
        }

        $client = $this->_client;

        $token = $client->getAccessToken();

        $userInfo = (object)array_merge(
            (array)($userInfo = $client->api('/account/verify_credentials.json', 'GET', array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true'))),
            array('email' => sprintf($userInfo->email, strtolower($userInfo->screen_name)))
        );

        $customersByTwitterId = $this->_helperTwitter
            ->getCustomersByTwitterId($userInfo->id);

        if($this->_customerSession->isLoggedIn()) {
            if($customersByTwitterId->count()){
                $this->messageManager
                    ->addNotice(
                        __('Your Twitter account is already connected to one of our store accounts.')
                    );
                return;
            }

            $customer = $this->_customerSession->getCustomer();
            $this->_helperTwitter->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccess(
                __('Your Twitter account is now connected to your new user accout at our store. You can login next time by the Twitter SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
            );
            return;
        }

        if($customersByTwitterId->count()) {
            /* Existing connected user - login*/
            $customer = $customersByTwitterId->getFirstItem();
            $this->_helperTwitter->loginByCustomer($customer);
            $this->messageManager->addSuccess(
                    __('You have successfully logged in using your Twitter account.')
                );
            return;
        }

        $customersByEmail = $this->_helperTwitter
            ->getCustomersByEmail($userInfo->email);


        if($customersByEmail->count()) {                
            /* Email account already exists - attach, login*/
            $customer = $customersByEmail->getFirstItem();
            $this->_helperTwitter->connectByTwitterId(
                $customer->getId(),
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccess(
                __('We find you already have an account at our store. Your Twitter account is now connected to your store account. Account confirmation mail has been sent to your email.')
            );
            return;
        }

        if(empty($userInfo->name)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, could not retrieve your Twitter name. Please try again.')
            );
        }

        $this->_helperTwitter->connectByCreatingAccount(
            $userInfo->email,
            $userInfo->name,
            $userInfo->id,
            $token
        );

        $this->messageManager->addSuccess(
            __('Your Twitter account is now connected to your new user accout at our store. You can login next time by the Twitter SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
        );

        $this->messageManager->addSuccess(
            sprintf(__('Since Twitter doesn\'t support third-party access to your email address, we were unable to send you your store accout credentials. To be able to login using store account credentials you will need to update your email address and password using our <a href="%s">Edit Account Information</a>.'), $this->urlModel->getUrl(customer/account/edit))
        );
    }

    /**
     * success login redirect to the customer account
     */
    protected function _sendResponse()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account');
        return $resultRedirect;
    }
}
