<?php

namespace Smartwave\Filterproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
//use Nowauction\Backwardbid\Block\Customer\Bidhistory\History as BidList;

class BidedList extends \Magento\Catalog\Block\Product\ListProduct {

    protected $_collection;

    protected $categoryRepository;

    protected $_resource;

    protected $_customer;

    protected $_eventFactory;

    /**
     * @var BidList
     */
    protected $bidedList;

    /**
     * BidedList constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param array $data
     */

    public function __construct(
            \Magento\Catalog\Block\Product\Context $context,
            \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
            \Magento\Catalog\Model\Layer\Resolver $layerResolver,
            CategoryRepositoryInterface $categoryRepository,
            \Magento\Framework\Url\Helper\Data $urlHelper,
            \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
            \Magento\Framework\App\ResourceConnection $resource,
            // \Nowauction\Backwardbid\Model\BidResource\Backwardbidhistory\CollectionFactory $eventFactory,
           // BidList $bidedList,
            array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->_collection = $collection;
        $this->_resource = $resource;
       // $this->bidedList = $bidedList;   
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        // $this->_eventFactory   = $eventFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $this->_customer = $objectManager->get ( 'Magento\Customer\Model\Session' );
    }

    protected function _getProductCollection() {
        return $this->getProducts();
    }
    
    public function getProducts() {
        $count = $this->getProductCount();
        $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);

        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }
        $category = $this->categoryRepository->get($category_id);
        if(isset($category) && $category) {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', [1], 'left');
            $collection->addAttributeToFilter('sw_featured', 1, 'left')
                ->addCategoryFilter($category);
        } else {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', [1], 'left');
            $collection->addAttributeToFilter('sw_featured', 1, 'left');
        }

        $collection->getSelect()
            ->order('rand()')
            ->limit($count);

        return $collection;
    }

    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    public function getProductCount() {
        $limit = $this->getData("product_count");
        if(!$limit)
            $limit = 10;
        return $limit;
    }

    /**
     * @inheritdoc
     */
    public function getProductCollection()
    {
        $count = $this->getProductCount();
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);

        if ($this->_customer->isLoggedIn()) {
            // $list = $this->bidedList->getEvents();
            $productIds = null;
            $objectModelManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $eventModel = $objectModelManager->get ( 'Nowauction\Backwardbid\Model\Backwardbid' );
            $eventCollection = $eventModel->getCollection ();
            $eventCollection->addFieldToFilter('customer_id', $this->_customer->getCustomerId());
            $data            = $eventCollection->getData();
            foreach ($data as $product) {
                $productIds[] = $product['product_id'];
            }
            // var_dump($productIds);exit();
            $collection->addIdFilter($productIds);
            // $collection->addAttributeToFilter('entity_id', array('in' => $productIds));
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite();
        }
        return $collection;
    }
    
    /**
     * [getProductName Get the name of the product]
     * @param  int $id Product Id
     * @return string Product Name
     */
    public function getProductName($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product       = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
        return $product->getName();
    }

    /**
     * [getProductImage Get the image of the product]
     * @param  int $id  Product Id
     * @return Product image
     */
    public function getProductImage($id)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product       = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
        return $product->getImage();

    }

    /**
     * [getBaseUrl Get the base url of the media file]
     * @return Base Url of media file
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
    }

    /**
     * [getDefaultImage Get the place holder image of the product]
     * @param  int $id  Product Id
     * @return Place holder image
     */
    public function getDefaultImage($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product       = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
        $imagehelper   = $objectManager->create('Magento\Catalog\Helper\Image');
        $image         = $imagehelper->init($product, 'category_page_list')->constrainOnly(false)->keepAspectRatio(true)->keepFrame(false)->resize(400)->getUrl();
        return $image;
    }

    /**
     * [getProductLink Get the link of the product]
     * @param  int $id  Product Id
     * @return string Product url
     */
    public function getProductLink($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product       = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
        return $product->getProductUrl();
    }

    /**
     * [getBidPosition get current bid position]
     * @param  int $productId product id
     * @param  int $bidAmount bidamount
     * @return int coun+1 for showing currect position
     */
    public function getBidPosition($productId,$bidAmount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $backwardbid_model = $objectManager->create('Nowauction\Backwardbid\Model\Backwardbid');   
        return $backwardbid_model->getBidsHigherthanCurrentCustomer($productId,$bidAmount);
        
    }
}
