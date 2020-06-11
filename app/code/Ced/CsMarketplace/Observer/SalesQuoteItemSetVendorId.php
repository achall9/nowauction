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
 * @package   Ced_CsOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Ced\CsMarketplace\Model\VproductsFactory;

/**
 * Class SalesQuoteItemSetVendorId
 * @package Ced\CsMarketplace\Observer
 */
Class SalesQuoteItemSetVendorId implements ObserverInterface
{
    /**
     * @var VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * SalesQuoteItemSetVendorId constructor.
     * @param VproductsFactory $vProductsFactory
     * @param RequestInterface $request
     */
    public function __construct(
        VproductsFactory $vProductsFactory,
        RequestInterface $request
    ) {
        $this->vProductsFactory = $vProductsFactory;
        $this->request = $request;

    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $vProducts = $this->vProductsFactory->create();
        $item = $observer->getQuoteItem();

        if ($vendorId = $vProducts->getVendorIdByProduct($item->getProductId())) {
            /*set vendor id to the quote item*/
            $item->setVendorId($vendorId);
        }
    }
}
