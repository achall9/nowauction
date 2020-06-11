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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vendor\Dashboard;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ResourceConnection;

class MostSoldProducts extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_collectionFactory;

    /**
     * @var ResourceConnection
     */
    public $resourceConnection;

    /**
     * MostSoldProducts constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Catalog\Model\Product $collectionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Catalog\Model\Product $collectionFactory,
        ResourceConnection $resourceConnection
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    public function getBestSellerProducts()
    {
        $collection = $this->_collectionFactory
            ->getCollection();

        $collection->getSelect()
            ->joinLeft(
                array('item_table' => $this->resourceConnection->getTableName('sales_order_item')),
                'entity_id = item_table.product_id',
                array('qty_ordered' => 'SUM(item_table.qty_ordered)')
            )->where(
                'qty_ordered > 0'
            )->group('entity_id')
            ->order('qty_ordered ' . 'DESC');

        $collection->getSelect()
            ->join(
                array('vendor_table' => $this->resourceConnection->getTableName('ced_csmarketplace_vendor_products')),
                'entity_id = vendor_table.product_id AND vendor_table.vendor_id = ' . $this->getVendorId(),
                array('entity_id' => 'e.entity_id',
                    'vendor_id' => 'vendor_table.vendor_id',
                    'price' => 'vendor_table.price',
                    'name' => 'vendor_table.name')
            );

        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->getSelect()->limit(5);
        return $collection;
    }

}
