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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory as VOrdersCollection;
use Ced\CsMarketplace\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

/**
 * Class ChangeOrderPaymentState
 * @package Ced\CsMarketplace\Observer
 */
class ChangeOrderPaymentState implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $itemCollection;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var VOrdersCollection
     */
    protected $vOrdersCollectionFactory;

    /**
     * ChangeOrderPaymentState constructor.
     * @param CollectionFactory $itemCollection
     * @param ResourceConnection $resourceConnection
     * @param Data $dataHelper
     * @param VOrdersCollection $vOrdersCollectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        CollectionFactory $itemCollection,
        ResourceConnection $resourceConnection,
        Data $dataHelper,
        VOrdersCollection $vOrdersCollectionFactory,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->itemCollection = $itemCollection;
        $this->resourceConnection = $resourceConnection;
        $this->dataHelper = $dataHelper;
        $this->vOrdersCollectionFactory = $vOrdersCollectionFactory;
        $this->_request = $request;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getDataObject()->getOrder();
        $this->dataHelper->logProcessedData($order->getIncrementId(), Data::SALES_ORDER_PAYMENT_STATE_CHANGED);

        $vOrders = $this->vOrdersCollectionFactory->create()
            ->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ced_csmarketplace_vendor_sales_order');
        $invoiced_item = $this->_request->getPost('invoice');

        if (count($vOrders) > 0) {
            foreach ($vOrders as $vOrder) {
                try {
                    $qtyOrdered = $qtyInvoiced = $invoiced = 0;
                    $vendorId = $vOrder->getVendorId();

                    $vOrderItems = $this->itemCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('vendor_id', $vendorId)
                        ->addFieldToFilter('order_id', $order->getId());

                    foreach ($vOrderItems as $item) {
                        if (isset($invoiced_item['items'])) {
                            foreach ($invoiced_item['items'] as $k=>$item_id) {
                                if ($k == $item->getItemId()) {
                                    $invoiced = (int) $item_id + (int)$item->getData('qty_invoiced');
                                }
                            }
                        }

                        if ($invoiced == 0) {
                            $invoiced = (int)$item->getData('qty_invoiced');
                        }

                        $qtyOrdered += (int)$item->getQtyOrdered();
                        $qtyInvoiced += (int)$invoiced;
                    }

                    if ($qtyOrdered > $qtyInvoiced) {
                        if ($qtyInvoiced != 0) {
                            $sql = "Update " . $tableName . " Set order_payment_state = " . \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid::STATE_PARTIALLY_PAID . " where order_id = '{$vOrder->getOrderId()}' and vendor_id = '{$vOrder->getVendorId()}'";
                        } else {
                            $sql = "Update " . $tableName . " Set order_payment_state = " . \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid::ORDER_NEW_STATUS . " where order_id = '{$vOrder->getOrderId()}' and vendor_id = '{$vOrder->getVendorId()}'";
                        }
                    } else {
                        $sql = "Update " . $tableName . " Set order_payment_state = " . \Magento\Sales\Model\Order\Invoice::STATE_PAID . " where order_id = '{$vOrder->getOrderId()}' and vendor_id = '{$vOrder->getVendorId()}'";
                    }

                    $connection->query($sql);
                    $this->dataHelper->logProcessedData($vOrder->getData(), Data::VORDER_PAYMENT_STATE_CHANGED);
                } catch (\Exception $e) {
                    $this->dataHelper->logException($e);
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error Occurred While Placing The Order'));
                }
            }
        }
    }
}  
