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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuoteToOrderObserver
 * @package Ced\GiftCard\Observer
 */
class QuoteToOrderObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Ced\GiftCard\Model\GiftCouponFactory
     */
    protected $giftCouponFactory;

    /**
     * QuoteToOrderObserver constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Ced\GiftCard\Model\GiftCouponFactory $giftCouponFactory
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Ced\GiftCard\Model\GiftCouponFactory $giftCouponFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->priceCurrency = $priceCurrency;
        $this->quoteRepository = $quoteRepository;
        $this->giftCouponFactory = $giftCouponFactory;
    }

    /**
     * @param $amount
     * @return float
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getProductType() == \Ced\GiftCard\Model\Product\Type\GiftCard::TYPE_CODE) {
                $options = $orderItem->getProductOptions();

                if (isset($options['info_buyRequest']) && !empty($options['info_buyRequest'])) {
                    $additionalOptions = [];
                    $option = $options['info_buyRequest'];
                    $val = 'To: ' . $option['gift_to_name'] . ', To Email: ' . $option['gift_to_email'];
                    $val .= ', Message: ' . $option['gift_message'];
                    $additionalOptions[] = [
                        'code' => 'gift_card_detail',
                        'label' => 'Gift Card Details',
                        'value' => $val
                    ];
                    if (count($additionalOptions) > 0) {
                        $options = $orderItem->getProductOptions();
                        $options['additional_options'] = $additionalOptions;
                        $orderItem->setProductOptions($options);
                    }
                }
            }
        }

        try {
            $order->save();
        } catch (\Exception $exception) {
            $messageManager = $this->messageManager;
            $message = __('Unable to transfer amount from GiftCard');
            $messageManager->addErrorMessage($message);
            throw new \Magento\Framework\Exception\CouldNotSaveException($message, $exception);
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        if (!$quote->getData('cs_giftcoupon_amount') || !$quote->getData('cs_giftcoupon_code')) {
            return $this;
        }
        $giftcoupon = $this->giftCouponFactory->create();
        $giftcoupon->load($quote->getData('cs_giftcoupon_code'), 'code');

        $currentAmount = $giftcoupon->getCurrentAmount();

        if ($currentAmount >= $quote->getData('cs_giftcoupon_amount')) {
            $order->setData('cs_giftcoupon_amount', $quote->getData('cs_giftcoupon_amount'));
            $order->setData('cs_giftcoupon_code', $quote->getData('cs_giftcoupon_code'));
            try {
                $order->save();
            } catch (\Exception $exception) {
                echo $exception; die('heree');
                $message = __('Unable to transfer amount from GiftCard');
                $this->messageManager->addErrorMessage($message);
                throw new \Magento\Framework\Exception\CouldNotSaveException($message, $exception);
            }
        } else {
            $message = __('Coupon Code %1 dosent have sufficient Amount. You have %2 credit left.', $quote->getData('cs_giftcoupon_code'), $giftcoupon->getCurrentAmount());
            $this->messageManager->addErrorMessage($message);
            throw new \Exception($message);
        }
        return $this;
    }


}