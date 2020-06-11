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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vendor\Dashboard;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Ced\CsMarketplace\Model\Vproducts;

class Info extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $_vproducts;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollection;

    public $_vendorProducts = null;

    public $_vendorProductsData = null;

    /**
     * Info constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Vproducts $_vproducts
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Vproducts $_vproducts,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection
    )
    {
        $this->_vproducts = $_vproducts;
        $this->_objectManager = $objectManager;
        $this->vproductsCollection = $vproductsCollection;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    public function getHelper($class)
    {
        return $this->_objectManager->get($class);
    }

    /**
     * Get vendor's Products data
     * @return array
     */
    public function getVendorProductsData()
    {
        // Total Pending Products
        $data = array('total' => array(), 'action' => []);
        if ($vendorId = $this->getVendorId()) {
            $vproducts = $this->_vproducts;
            $pendingProducts = count($vproducts->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::PENDING_STATUS, $vendorId));
            $approvedProducts = count($vproducts->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS, $vendorId));
            $disapprovedProducts = count($vproducts->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::NOT_APPROVED_STATUS, $vendorId));

            if ($pendingProducts > 1000000000000) {
                $data['total'][] = round($pendingProducts / 1000000000000, 1) . 'T';
            } elseif ($pendingProducts > 1000000000) {
                $data['total'][] = round($pendingProducts / 1000000000, 1) . 'B';
            } elseif ($pendingProducts > 1000000) {
                $data['total'][] = round($pendingProducts / 1000000, 1) . 'M';
            } elseif ($pendingProducts > 1000) {
                $data['total'][] = round($pendingProducts / 1000, 1) . 'K';
            } else {
                $data['total'][] = round($pendingProducts);
            }
            $data['action'][] = $this->getUrl('*/vproducts/', array('_secure' => true, 'check_status' => 2));

            if ($approvedProducts > 1000000000000) {
                $data['total'][] = round($approvedProducts / 1000000000000, 1) . 'T';
            } elseif ($approvedProducts > 1000000000) {
                $data['total'][] = round($approvedProducts / 1000000000, 1) . 'B';
            } elseif ($approvedProducts > 1000000) {
                $data['total'][] = round($approvedProducts / 1000000, 1) . 'M';
            } elseif ($approvedProducts > 1000) {
                $data['total'][] = round($approvedProducts / 1000, 1) . 'K';
            } else {
                $data['total'][] = round($approvedProducts);
            }
            $data['action'][] = $this->getUrl('*/vproducts/', array('_secure' => true, 'check_status' => 1));

            if ($disapprovedProducts > 1000000000000) {
                $data['total'][] = round($disapprovedProducts / 1000000000000, 1) . 'T';
            } elseif ($disapprovedProducts > 1000000000) {
                $data['total'][] = round($disapprovedProducts / 1000000000, 1) . 'B';
            } elseif ($disapprovedProducts > 1000000) {
                $data['total'][] = round($disapprovedProducts / 1000000, 1) . 'M';
            } elseif ($disapprovedProducts > 1000) {
                $data['total'][] = round($disapprovedProducts / 1000, 1) . 'K';
            } else {
                $data['total'][] = round($disapprovedProducts);
            }
            $data['action'][] = $this->getUrl('*/vproducts/', array('_secure' => true, 'check_status' => 0));
        }
        return $data;
    }
    
    public function getVendorProducts(){
        $collection = $this->vproductsCollection->create()->addFieldToFilter('vendor_id',$this->getVendorId())->setOrder('id','DESC')->setPageSize(5)->setCurPage(1);
        return $collection;
    }
}
