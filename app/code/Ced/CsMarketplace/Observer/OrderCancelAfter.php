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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;


use Magento\Framework\Event\ObserverInterface;
use Ced\CsMarketplace\Model\Vorders;

/**
 * Class OrderCancelAfter
 * @package Ced\CsMarketplace\Observer
 */
Class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $_vendorOrderCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $_marketplaceMail;

    /**
     * OrderCancelAfter constructor.
     * @param \Ced\CsMarketplace\Helper\Mail $marketplaceMail
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceHelper
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vendorOrderCollectionFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Mail $marketplaceMail,
        \Ced\CsMarketplace\Helper\Data $marketplaceHelper,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vendorOrderCollectionFactory
    ) {
        $this->_marketplaceMail = $marketplaceMail;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
    }

    /**
     * Cancel the asscociated vendor order
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_marketplaceHelper->logProcessedData(
            $order->getIncrementId(),
            \Ced\CsMarketplace\Helper\Data::SALES_ORDER_CANCELED
        );

        try {
            $vOrders = $this->_vendorOrderCollectionFactory->create()
                ->addFieldToFilter('order_id', array('eq'=>$order->getIncrementId()));
            if (count($vOrders) > 0) {
                foreach ($vOrders as $vOrder) {
                    if ($vOrder->canCancel()) {
                        $vOrder->setOrderPaymentState(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED);
                        $vOrder->setPaymentState(Vorders::STATE_CANCELED);
                        $vOrder->save();
                    } else if ($vOrder->canMakeRefund()) {
                        $vOrder->setPaymentState(Vorders::STATE_REFUND);
                        $vOrder->save();
                    }

                    $this->_marketplaceHelper->logProcessedData(
                        $vOrder->getData(),
                        \Ced\CsMarketplace\Helper\Data::VORDER_CANCELED
                    );

                    $this->_marketplaceMail->sendOrderEmail(
                        $order,
                        Vorders::ORDER_CANCEL_STATUS,
                        $vOrder->getVendorId(),
                        $vOrder
                    );
                }
            }
        } catch(\Exception $e) {
            $this->_marketplaceHelper->logException($e);
        }
        return $this;
    }
}