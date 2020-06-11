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
 * @package     Ced_CsMarketplace
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Controller\Vpayments;

use Magento\Framework\App\Action\Context;

/**
 * Class Requestpost
 * @package Ced\CsTransaction\Controller\Vpayments
 */
class Requestpost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_getSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $cstransactionItems;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $vpaymentRequested;

    /**
     * Requestpost constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Ced\CsTransaction\Model\Items $cstransactionItems
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Ced\CsTransaction\Model\Items $cstransactionItems,
        \Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested
    )
    {
        $this->_getSession = $customerSession;
        $this->datetime = $datetime;
        $this->cstransactionItems = $cstransactionItems;
        $this->vpaymentRequested = $vpaymentRequested;
        parent::__construct($context);
    }

    /**
     * @return $this|bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_getSession->getVendorId()) {
            return false;
        }
        $orderIds = $this->getRequest()->getParam('payment_request');
        if (strlen($orderIds) > 0) {
            $orderIds = explode(',', $orderIds);
        }

        if (!is_array($orderIds)) {
            $this->messageManager->addErrorMessage(__('Please select amount(s).'));
        } else {
            if (!empty($orderIds)) {
                try {
                    $updated = 0;
                    foreach ($orderIds as $orderId) {
                        $items_model = $this->cstransactionItems->load($orderId);

                        $amount = $items_model->getItemFee();
                        $order_increment_id = $this->cstransactionItems
                            ->load($orderId)->getOrderIncrementId();

                        $data = array('vendor_id' => $this->_getSession->getVendorId(), 'order_id' => $order_increment_id, 'amount' => $amount, 'status' => \Ced\CsMarketplace\Model\Vpayment\Requested::PAYMENT_STATUS_REQUESTED, 'created_at' => $this->datetime->date('Y-m-d H:i:s'), 'item_id' => $items_model->getOrderItemId());
                        $items_model->setIsRequested(1)->save();
                        $this->vpaymentRequested->addData($data)->save();
                        $updated++;
                    }
                    if ($updated) {
                        $this->messageManager->addSuccessMessage(__('Total of %1 amount(s) have been requested for payment.', $updated));
                    } else {
                        $this->messageManager->addSuccessMessage(__('Payment(s) have been already requested for payment.'));
                    }
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                }
            }
        }
        return false;

    }

}
