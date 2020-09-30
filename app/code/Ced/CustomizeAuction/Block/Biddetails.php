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
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CustomizeAuction\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Ced\Auction\Model\ResourceModel;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ced\Auction\Helper;
use Magento\Catalog\Model\ProductRepository;

class Biddetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    protected $collection = null;
    protected $resourceBidDetails;
    /**
     * Index constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        UrlInterface $urlInterface,
        DateTime $datetime,
        Helper\ConfigData $configHelper,
        ProductRepository $productRepository,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        array $data = []
    )
    {
        $this->winnerCollection = $winnerCollection;
        $this->auctionCollection = $auctionCollection;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->date = $datetime;
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
        $this->_stockItemRepository = $stockItemRepository;
        $this->resourceBidDetails = $resourceBidDetails;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getBidDetails(){

        if($this->collection!==null)
            return $this->collection;
        $id = $this->customerSession->getData('customer_id');

        $this->collection = $this->resourceBidDetails->create()->addFieldToFilter('customer_id', $id);

       /* $purchasedDay = $this->configHelper->getConfigData('auction_entry_1/standard/remove_purchase_link');
        $days = '+' . $purchasedDay. ' day';

        foreach ($this->collection as $item){
            if($purchasedDay) {
                $biddingDate = strtotime($item->getBidDate());
                $biddingEnddate = date("Y-m-d", strtotime($days, $biddingDate));
                $now = date("Y-m-d", strtotime($this->date->gmtDate()));
                $status = $item->getStatus();
                if ($biddingEnddate <= $now && $status == 'not purchased') {
                    $item->setData('status', 'purchase link expired');
                    $item->save();
                }
            }
        }

        if($this->collection){
             $this->collection = $this->winnerCollection->create()->addFieldToFilter('customer_id', $id);
        } */
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getUrlInterface(){

        return $this->urlInterface->getUrl();
    }

    public function getProductName($productId){
        return $this->productRepository->getById($productId)->getName();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getBidDetails()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'reward.history.pager'
            )->setCollection(
                    $this->getBidDetails()
                );
            $this->setChild('pager', $pager);
            $this->getBidDetails()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function checkProductAvailability($productid){

        $product = $this->productRepository->getById($productid);
        if($product) {
            $qty = $this->productRepository->getById($productid)->getExtensionAttributes()->getStockItem()->getIsInStock();
        }

        if(!$qty || empty($product) ){
            return true;
        }
        else{
            return false;
        }

    }
}