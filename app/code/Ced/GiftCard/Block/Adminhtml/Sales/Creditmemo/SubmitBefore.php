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
 * @package     Ced_GiftCard
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Block\Adminhtml\Sales\Creditmemo;
use \Ced\GiftCard\Model\ResourceModel\GiftCoupon\CollectionFactory as GiftCouponCollection;

class SubmitBefore extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    public $_hasGiftCard = null;

    public $currentGiftCards = null;

    /**
     * @var GiftCouponCollection
     */
    protected $_giftCoupons;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        GiftCouponCollection $_giftCoupons,
        array $data = []
    ) {

        $this->_giftCoupons = $_giftCoupons;
        $this->_coreRegistry = $registry;

        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }
    public function giftReturnAmount(){
        $returnData = [];

        if($this->hasGiftCard() && $this->currentGiftCards){
             foreach($this->currentGiftCards as $itemId => $giftCards){
                 foreach($giftCards as $giftCard){
                    $returnData[$giftCard->getCode()] = $giftCard->getCurrentAmount();
                 }
             }
        }
        return $returnData;
    }
    public function hasGiftCard(){

        if ($this->_hasGiftCard === null) {
            $creditMemo = $this->getCreditmemo();
            $incrementId = $this->getCreditmemo()->getOrder()->getIncrementId();
            $giftCoupon = $this->_giftCoupons->create();
            $giftCoupon = $giftCoupon->addFieldToFilter('increment_id', $incrementId);

            foreach ($creditMemo->getAllItems() as $item) {
                $giftCouponCopy = clone $giftCoupon;

                $giftCouponCopy->addFieldToFilter('product_id', $item->getProductId());
                if(!empty($giftCouponCopy->getData())){
                    $this->currentGiftCards[$item->getOrderItemId()] =  $giftCouponCopy;

                    $this->_hasGiftCard = true;
                }
            }
        }

        return $this->_hasGiftCard;
    }

    /**
     * Get credit memo
     *
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }
}