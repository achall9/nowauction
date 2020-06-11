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

/**
 * Class CreateVendorCreditmemo
 * @package Ced\CsOrder\Observer
 */
Class CreateVendorCreditmemo implements ObserverInterface
{

    protected $request;

    private $messageManager;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplacehelper;

    /**
     * @var \Ced\CsOrder\Model\CreditmemoFactory
     */
    protected $vcreditmemo;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $helper;

    /**
     * CreateVendorCreditmemo constructor.
     * @param \Ced\CsOrder\Helper\Data $helper
     * @param \Ced\CsOrder\Model\CreditmemoFactory $vcreditmemo
     * @param \Ced\CsMarketplace\Helper\Data $marketplacehelper
     */
    public function __construct(
        \Ced\CsOrder\Helper\Data $helper,
        \Ced\CsOrder\Model\CreditmemoFactory $vcreditmemo,
        \Ced\CsMarketplace\Helper\Data $marketplacehelper
    ) {
        $this->helper = $helper;
        $this->vcreditmemo = $vcreditmemo;
        $this->marketplacehelper = $marketplacehelper;
    }

    /**
     *Set vendor naem and url to product incart
     *
     *@param $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if($this->helper->isActive()) {
                $creditmemo = $observer->getCreditmemo();
                $allItems = $creditmemo->getAllItems();
                $creditmemoVendor = [];
                foreach($allItems as $item){
                    $vendorId = $item->getVendorId();
                    $creditmemoVendor[$vendorId] = $vendorId;
                }

                foreach($creditmemoVendor as $vendorId){
                    try{
                        $id = $creditmemo->getId();
                        $vCreditmemo = $this->vcreditmemo->create();
                        $vCreditmemo->setCreditmemoId($id);
                        $vCreditmemo->setVendorId($vendorId);
                        $vCreditmemo->save();
                    }catch(\Exception $e){
                        $this->marketplacehelper->logException($e);
                    }
                }
            }
        } catch(\Exception $e) {
            $this->marketplacehelper->logException($e);
        }
    }
}  
