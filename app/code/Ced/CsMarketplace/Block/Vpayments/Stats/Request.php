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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMarketplace\Block\Vpayments\Stats;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Request extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $requested;

    /**
     * Request constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Payment $paymentHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $requested
     */
   public function __construct(
       \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
       \Magento\Customer\Model\CustomerFactory $customerFactory,
       Context $context,
       Session $customerSession,
       UrlFactory $urlFactory,
       \Ced\CsMarketplace\Helper\Payment $paymentHelper,
       \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
       \Ced\CsMarketplace\Model\Vpayment\Requested $requested
   )
   {
       $this->paymentHelper = $paymentHelper;
       $this->csmarketplaceHelper = $csmarketplaceHelper;
       $this->requested = $requested;
       parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
       $this->setPendingAmount(0.00);
       $this->setPendingTransfers(0);
       $this->setPaidAmount(0.00);
       $this->setCanceledAmount(0.00);
       $this->setRefundableAmount(0.00);
       $this->setRefundedAmount(0.00);
       $this->setEarningAmount(0.00);

       if ($this->getVendor() && $this->getVendor()->getId()) {
           $paymentHelper = $this->paymentHelper;
           $collection = $paymentHelper->_getTransactionsStats($this->getVendor());

           if (count($collection) > 0) {
               foreach ($collection as $stats) {
                   switch ($stats->getPaymentState()) {
                       case \Ced\CsMarketplace\Model\Vorders::STATE_OPEN :
                           $this->setPendingAmount($stats->getNetAmount());
                           $this->setPendingTransfers($stats->getCount() ? $stats->getCount() : 0);
                           break;
                       case \Ced\CsMarketplace\Model\Vorders::STATE_PAID :
                           $this->setPaidAmount($stats->getNetAmount());
                           break;
                       case \Ced\CsMarketplace\Model\Vorders::STATE_CANCELED :
                           $this->setCanceledAmount($stats->getNetAmount());
                           break;
                       case \Ced\CsMarketplace\Model\Vorders::STATE_REFUND :
                           $this->setRefundableAmount($stats->getNetAmount());
                           break;
                       case \Ced\CsMarketplace\Model\Vorders::STATE_REFUNDED :
                           $this->setRefundedAmount($stats->getNetAmount());
                           break;
                   }
               }
           }
           $amounts = 0.0000;
           $marketplaceHelper = $this->csmarketplaceHelper;
           $main_table = $marketplaceHelper->getTableKey('main_table');
           $amount_column = $marketplaceHelper->getTableKey('amount');
           $amounts = $this->requested
               ->getCollection()
               ->addFieldToFilter('vendor_id', array('eq' => $this->getVendorId()))
               ->addFieldToFilter('status', array('eq' => \Ced\CsMarketplace\Model\Vpayment\Requested::PAYMENT_STATUS_REQUESTED));

           $amounts->getSelect()->columns("SUM({$main_table}.{$amount_column}) AS amounts");

           if (count($amounts) > 0 && count($collection) > 0) {
               $amounts = $amounts->getFirstItem()->getData("amounts");
               $cancelled_amount = $collection->addFieldToFilter('payment_state', \Ced\CsMarketplace\Model\Vorders::STATE_CANCELED)->getData();
               if (sizeof($cancelled_amount) > 0) {
                   foreach ($cancelled_amount as $key => $value) {
                       $amounts -= $value['net_amount'];
                   }
               }
           } else {
               $amounts = 0.0000;
           }

           $this->setEarningAmount($this->getVendor()->getAssociatedPayments()->getFirstItem()->getBalance());
           $this->setRequestedAmount($amounts);
       }
   }
}
