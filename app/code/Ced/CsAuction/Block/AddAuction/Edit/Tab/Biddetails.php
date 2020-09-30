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
 * @package     Ced_CsAuction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\AddAuction\Edit\Tab;

use Magento\Catalog\Model\ProductRepository;
use Ced\Auction\Model\Auction;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Biddetails extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $_productFactory;
    protected $_vproduct;
    protected $_type;
    protected $pageLayoutBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        \Ced\Auction\Model\AuctionFactory $auction,
        \Magento\Customer\Model\Session $session,
        \Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_collectionFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->_vproduct = $vproduct;
        $this->auctionCollection = $auction;
        $this->session = $session;
        $this->resourceBidDetails = $resourceBidDetails;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area','adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorpreproductGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    protected function _prepareCollection()
    {
        $rowId = $this->getRequest()->getParam('id');
        $vendorId = $this->session->getVendorId();
        $auctionCollection = $this->auctionCollection->create()->load($rowId);
        $productId = $auctionCollection->getProductId();
        $collection = $this->resourceBidDetails->create()->addFieldToFilter('product_id',$productId)->addFieldToFilter('is_preauction',0);
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {


        $this->addColumn('product_id', ['header' => __('Product Id'), 'index' => 'product_id','type' => 'number']);

        $this->addColumn('customer_name', ['header' => __('Name'), 'index' => 'customer_name']);

        $this->addColumn(
            'total_bid_price',
            [
                'header' => __('Total Order Amount'),
                'type'  => 'currency',
                'index' => 'total_bid_price',
            ]
        );

        $this->addColumn('bid_price',
            array(
                'header'=> __('Bid Price'),
                'type'  => 'currency',
                'index' => 'bid_price',
            ));

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );

        $this->addColumn(
            'bid_date',
            [
                'header' => __('Bid DateTime'),
                'index' => 'bid_date',
            ]
        );

       /* $this->addColumn(
            'edits',
            [
                'header' => __(''),
                'caption' => __(''),
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Renderer\PlaceOrder',
                'sortable' => false,
                'filter' => false
            ]
        ); */


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product_id');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('csauction/auctionlist/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/PreAucionList/grid', ['_current' => true]);
    }
}