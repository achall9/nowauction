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

namespace Ced\CsMarketplace\Observer;


use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class SalesModelServiceQuoteSubmitBeforeObserver
 * @package Ced\CsMarketplace\Observer
 */
class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var array
     */
    private $quoteItems = [];
    /**
     * @var null
     */
    private $quote = null;
    /**
     * @var null
     */
    private $order = null;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * SalesModelServiceQuoteSubmitBeforeObserver constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_vendorFactory = $vendorFactory;
        $this->_state = $state;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_state->getAreaCode() !== 'adminhtml') {
            $this->quote = $observer->getQuote();
            $this->order = $observer->getOrder();
            foreach ($this->order->getItems() as $orderItem) {
                if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId(), $this->quote)) {
                    $additionalOptions = [];
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptions = array_merge($additionalOptionsQuote, $additionalOptionsOrder);
                        } else {
                            $additionalOptions = $additionalOptionsQuote->getValue();
                        }
                        $additionalOptions = class_exists(
                            "\\Magento\\Framework\\Serialize\\Serializer\\Json")? $this->_serializer->unserialize($additionalOptions) : unserialize($additionalOptions);
                    }
                    if ($quoteItem->getVendorId() && ($quoteItem->getProductType() !== Configurable::TYPE_CODE)) {
                        $vendor = $this->_vendorFactory->create()->load($quoteItem->getVendorId());
                        $publicName = $vendor->getPublicName();
                        $additionalOptions[] = [
                            'code'  => 'vendor_name',
                            'label'  => 'Vendor',
                            'value' => $publicName
                        ];
                    }
                    if (count($additionalOptions) > 0) {
                        $options = $orderItem->getProductOptions();
                        $options['additional_options'] = $additionalOptions;
                        $orderItem->setProductOptions($options);
                    }
                }
            }
        }
    }

    /**
     * @param $id
     * @param $quote
     * @return mixed|null
     */
    private function getQuoteItemById($id, $quote)
    {
        if (empty($this->quoteItems)) {
            /* @var  \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                //filter out config/bundle etc product
                $this->quoteItems[$item->getId()] = $item;
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }
}
