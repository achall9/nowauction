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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class SalesQuoteItemSetGiftMessage
 * @package Ced\GiftCard\Observer
 */
Class SalesQuoteItemSetGiftMessage implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * SalesQuoteItemSetGiftMessage constructor.
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager
    )
    {
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $item = $observer->getQuoteItem();
        $product = $item->getProduct();

        try {
            if (!($product->getTypeId() == \Ced\GiftCard\Model\Product\Type\GiftCard::TYPE_CODE)) {
                return $this;
            }
            if (null !== $item->getBuyRequest()->getGiftToName() && null !== $item->getBuyRequest()->getGiftToEmail()) {

                $val = '';
                $val .= 'TO  : ' . $item->getBuyRequest()->getGiftToName() . ', To Email : ' . $item->getBuyRequest()->getGiftToEmail();

                $additionalOptions = [
                    [
                        'code' => 'gift_card_detail',
                        'label' => 'Gift Card Details',
                        'value' => $val
                    ],
                ];
                $item->addOption(
                    [
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => json_encode($additionalOptions),
                    ]
                );
            }
        } catch (\Exception $e) {
        }
    }
}
