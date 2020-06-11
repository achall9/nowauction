<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMessaging\Model\System\Vcustomer;

/**
 * Class Notification
 * @package Ced\CsMessaging\Model\System\Vcustomer
 */
class Notification implements \Magento\Framework\Notification\MessageInterface
{
    public function __construct(\Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollectionFactory,
                                \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->vcustomerMessageCollectionFactory = $vcustomerMessageCollectionFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    protected function getNewMessages()
    {
        $collection = $this->vcustomerMessageCollectionFactory->create();
        $collection->addFieldToFilter('sender',['neq'=>\Ced\CsMessaging\Helper\Data::ADMIN_AS_SENDER])
                   ->addFieldToFilter('admin_status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);
        return $collection->getSize();
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return 'ced_csmessaging_vcustomer';
    }

    public function isDisplayed()
    {
        if ($this->getNewMessages() > 0)
            return true;
        else
            return false;
    }

    public function getText()
    {
        $msg = '';
        if ($this->getNewMessages() > 0)
        {
            $msg =   __("You have ".$this->getNewMessages()." new vendor-customer messages, kindly ").
                '<a href="'.$this->_urlBuilder->getUrl("csmessaging/vcustomer/vcustomer").'">'.__("click here").'</a>';
        }
        return $msg;
    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}