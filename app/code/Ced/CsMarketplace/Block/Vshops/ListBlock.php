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

namespace Ced\CsMarketplace\Block\Vshops;

class ListBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var int
     */
    protected $_defaultColumnCount = 5;

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_cedCatalogLayer;

    /**
     * @var \Ced\CsMarketplace\Model\Vshop
     */
    protected $vshop;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var Vendor Collection
     */
    protected $_vendorCollection;

    /**
     * @var \Magento\Framework\Registry $_coreRegistry
     */
    public $_coreRegistry = null;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\Product\ProductList
     */
    public $_prodListHelper;

    /**
     * ListBlock constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\ProductList $prodListHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\ProductList $prodListHelper,
        array $data = []
    )
    {
        $this->_cedCatalogLayer = $layerResolver->get();
        $this->urlHelper = $urlHelper;
        $this->_coreRegistry = $context->getRegistry();
        $this->vshop = $vshop;
        $this->vendor = $vendor;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->storeManager = $storeManager;
        $this->_prodListHelper = $prodListHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve loaded category collection
     *
     */
    protected function _getVendorCollection()
    {
        $vendor_name = $this->_coreRegistry->registry('vendor_name');
        $name_filter = $this->_coreRegistry->registry('name_filter');
        $zip_code = $this->_coreRegistry->registry('zip_code');
        $country = $this->_coreRegistry->registry('country');

        if (is_null($this->_vendorCollection)) {
            $vendorIds = [0];
            $model = $this->vshop->getCollection()
                ->addFieldToFilter('shop_disable', array('eq'=>\Ced\CsMarketplace\Model\Vshop::DISABLED));

            if (count($model) > 0) {
                foreach($model as $row){
                    $vendorIds[] = $row->getVendorId();
                }
            }

            $this->_vendorCollection = $this->vendor
                ->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter('status',array('eq'=>\Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS));
            if ($name_filter == '') {
                $this->_vendorCollection = $this->_vendorCollection->addAttributeToSort('public_name', 'asc');
            }

            if (count($vendorIds) > 0) {
                if ($vendor_name!='' || $country!='' || $zip_code!='' || $name_filter!='') {
                    if ($vendor_name != '') {
                        $this->_vendorCollection->addAttributeToFilter(
                            array(
                                array('attribute'=>'public_name','like'=>'%'.$vendor_name.'%'),
                            ) );
                    }

                    if ($country != '') {
                        $this->_vendorCollection->addAttributeToFilter(
                            array( array('attribute'=>'country_id','like'=>'%'.$country.'%') ) );
                    }

                    if ($zip_code != '') {
                        $this->_vendorCollection->addAttributeToFilter(
                            array( array('attribute'=>'zip_code','like'=>'%'.$zip_code.'%') ) );
                    }

                    if ($name_filter != '') {
                        $this->_vendorCollection->addAttributeToSort('public_name', $name_filter);
                    }
                    $this->_vendorCollection = $this->_vendorCollection->addAttributeToFilter('entity_id',array('nin'=>$vendorIds));
                } else {
                    $this->_vendorCollection = $this->_vendorCollection->addAttributeToFilter('entity_id',array('nin'=>$vendorIds));
                }
            }

            if ($this->csmarketplaceHelper->isSharingEnabled()){
                $this->_vendorCollection->addAttributeToFilter('website_id',array('eq'=>$this->storeManager->getStore()->getWebsiteId()));
            }
            $this->prepareSortableFields();
        }

        return $this->_vendorCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     */
    public function getLoadedVendorCollection()
    {
        return $this->_getVendorCollection();
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Prepare Sort By fields from Category Data for Vshops
     * @return $this
     */
    public function prepareSortableFields()
    {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($this->_getConfig()->getAttributeUsedForSortByArray());
        }
        $cedAvailableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($defaultSortBy = $this->_getConfig()->getDefaultSortBy()) {
                if (isset($cedAvailableOrders[$defaultSortBy])) {
                    $this->setSortBy($defaultSortBy);
                }
            }
        }
        return $this;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_cedCatalogLayer;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        $currentMode = $this->getChildBlock('toolbar')->getCurrentMode();
        return $currentMode;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getVendorCollection();

        // use sortable parameters
        $cedorders = $this->getAvailableOrders();
        if ($cedorders) {
            $toolbar->setAvailableOrders($cedorders);
        }
        $cedsort = $this->getSortBy();
        if ($cedsort) {
            $toolbar->setDefaultOrder($cedsort);
        }
        $ceddir = $this->getDefaultDirection();
        if ($ceddir) {
            $toolbar->setDefaultDirection($ceddir);
        }
        $cedmodes = $this->getModes();
        if ($cedmodes) {
            $toolbar->setModes($cedmodes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        /*$this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getVendorCollection()]
        );*/

        $this->_getVendorCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToolbarBlock()
    {
        $cedBlockName = $this->getToolbarBlockName();
        if ($cedBlockName) {

            $cedBlock = $this->getLayout()->getBlock($cedBlockName);
            if ($cedBlock) {
                return $cedBlockName;
            }
        }

        $cedBlockName = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $cedBlockName;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        $cedAdditionalHtml = $this->getChildHtml('additional');
        return $cedAdditionalHtml;
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        $cedToolbar = $this->getChildHtml('toolbar');
        return $cedToolbar;
    }

    /**
     * @param Set AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_vendorCollection = $collection;
        return $this;
    }

    /**
     * @param array|string|integer|\Magento\Framework\App\Config\Element $code
     * @return $this
     */
    public function addAttribute($code)
    {
        $this->_getVendorCollection()->addAttributeToSelect($code);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceBlockTemplate()
    {
        $cedPriceBlock = $this->_getData('price_block_template');
        return $cedPriceBlock;
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return \Ced\CsMarketplace\Model\Vendor
     */
    protected function _getConfig()
    {
        return $this->vendor;
    }


    /**
     * Get post parameters
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $cedUrl = $this->getAddToCartUrl($product);
        return [
            'action' => $cedUrl,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($cedUrl),
            ]
        ];
    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();
        $productPrice = '';
        if ($priceRender) {
            $productPrice = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $productPrice;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_defaultColumnCount;
    }

}
