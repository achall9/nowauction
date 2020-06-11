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

namespace Ced\VendorsocialLogin\Helper;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

/**
 * Class Facebook
 * @package Ced\VendorsocialLogin\Helper
 */
class Facebook extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Google constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);

    }

	/*
	*	connect existing account with facebook
	* 	@param int $customerId
	*	@param string $facebookId
	*	@param string $token
	*/
    public function connectByFacebookId(
		$customerId,
        $facebookId,
        $token
    )
    {
		$customer = $this->_customerFactory->create();
		$customer->load($customerId);
		$customer->setCedSocialloginFid($facebookId);
		$customer->setCedSocialloginFtoken($token);
		$customer->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

	/*
	*	connect new account with facebook
	*	@param string $email
	*	@param string $firstname
	*	@param string $lastname
	*	@param string $facebookId
	*	@param string $token
	*/
    public function connectByCreatingAccount(
        $email,
        $firstName,
        $lastName,
		$facebookId,
        $token
    )
    {
		$customer = $this->_customerFactory->create();
        $customerDetails = array(
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'sendemail' => 0,
            'confirmation' => 0,
			'ced_sociallogin_fid' =>$facebookId,
			'ced_sociallogin_ftoken' =>$token
        );
		$customer->setData($customerDetails);
		$customer->save();
		$customer->sendNewAccountEmail('confirmed', '');
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }
	
	/*
	*	login by customer
	*	@param \Magento\Customer\Model\Customer $customer
	*/
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)
    {
        if($customer->getConfirmation()) {

            $customer->setConfirmation(null);

            $customer->save();

        }

        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }
	
	/*
	*	get customer by facebook id
	*	@param int $facebookId
	*
	*	return \Magento\Customer\Model\Customer $customer
	*/
    public function getCustomersByFacebookId($facebookId)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()

            ->addAttributeToSelect('*')

            ->addAttributeToFilter('ced_sociallogin_fid', $facebookId)

            ->setPage(1, 1);

        return $collection;
    }
	
	/*
	*	get customer by email id
	*	@param string $email
	*
	*	return \Magento\Customer\Model\Customer $customer
	*/
    public function getCustomersByEmail($email)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()

            ->addAttributeToSelect('*')

            ->addAttributeToFilter('email', $email)

            ->setPage(1, 1);

        return $collection;
    }

}