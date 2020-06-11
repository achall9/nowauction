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
  * @category  Ced
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsOrder\Observer; 

use Magento\Framework\Event\ObserverInterface;

Class CreateVendorShipment implements ObserverInterface
{
    /**
     * @var \Ced\CsOrder\Model\Shipment
     */
    protected $shipment;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplacehelper;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $helper;
    
    public function __construct(
        \Ced\CsOrder\Helper\Data $helper,
        \Ced\CsMarketplace\Helper\Data $marketplacehelper,
        \Ced\CsOrder\Model\ShipmentFactory $shipment
    ) {
    
        $this->helper = $helper;
        $this->marketplacehelper = $marketplacehelper;
        $this->shipment = $shipment;
    }

    /**
     * Set vendor name and url to product incart
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if($this->helper->isActive()) {
                $shipment = $observer->getShipment();
                $allItems = $shipment->getAllItems();
                $shipmentVendor = [];
                foreach($allItems as $item){
                    $vendorId = $item->getvendorId();
                    if ($item->getVendorId() && !in_array($item->getVendorId(), $shipmentVendor))
                    $shipmentVendor[$vendorId] = $vendorId;
                }
                foreach($shipmentVendor as $vendorId){
                    try{
                        $id = $shipment->getId();
                        $vshipment = $this->shipment->create();
                        $vshipment->setShipmentId($id);
                        $vshipment->setVendorId($vendorId);
                        $vshipment->save();
                    }catch(\Exception $e){
                        $e->getMessage();
                    }
                }
            }
        }catch(\Exception $e) {
            $this->marketplacehelper->logException($e);
        }
    }
} 
