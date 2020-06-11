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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Customer;

use Ced\CsMessaging\Model\ResourceModel\VadminMessage;
use Magento\Framework\View\Element\Template;

/**
 * Class Notification
 * @package Ced\CsMessaging\Block\Customer
 */
class Notification extends Template
{
    public function __construct(Template\Context $context,
                                \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollectionFactory,
                                \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $cadmnMessageCollectionFactory,
                                \Magento\Customer\Model\Session $session,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->vcustomerMessageCollectionFactory = $vcustomerMessageCollectionFactory;
        $this->cadmnMessageCollectionFactory = $cadmnMessageCollectionFactory;
        $this->session = $session;
    }

    public function getVcustomerNewMessages()
    {
        $collection = $this->vcustomerMessageCollectionFactory->create();
        $collection->addFieldToFilter('customer_id',$this->session->getCustomerId())
                    ->addFieldToFilter('sender',['neq'=>\Ced\CsMessaging\Helper\Data::CUSTOMER_AS_SENDER])
                    ->addFieldToFilter('receiver_status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);
        return $collection->getSize();
    }

    public function getCadminNewMessages()
    {
        $collection = $this->cadmnMessageCollectionFactory->create();
        $collection->addFieldToFilter('receiver_id',$this->session->getCustomerId())
            ->addFieldToFilter('status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);
        return $collection->getSize();
    }

}
