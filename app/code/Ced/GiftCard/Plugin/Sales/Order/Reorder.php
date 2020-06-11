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
namespace Ced\GiftCard\Plugin\Sales\Order;

use Magento\Framework\App\Action;
use Ced\GiftCard\Model\Product\Type\GiftCard;
use Magento\Framework\Serialize\Serializer\Serialize as SerializerInterface;

/**
 * Class Reorder
 * @package Ced\GiftCard\Plugin\Sales\Order
 */
class Reorder
{

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $phpCookieManager;

    /**
     * Reorder constructor.
     * @param Action\Context $context
     * @param SerializerInterface $serializer
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
     */
    public function __construct(
        Action\Context $context,
        SerializerInterface $serializer,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->serializer = $serializer;
        $this->cartFactory = $cartFactory;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->phpCookieManager = $phpCookieManager;
    }


    /**
     * {@inheritdoc}
     */
    public function aroundExecute($subject, $proceed)
    {
        $result = $proceed();
        $isMessageAdded = false;
        $productUrls = [];
        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->cartFactory->create();
        $quote = $cart->getQuote();

        foreach ($quote->getAllVisibleItems() as $key => $item) {
            try{
                if ($item->getProductType() == 'giftcard') {
                    $buyRequest = $item->getBuyRequest()->getData();
                    if (is_array($buyRequest) && !empty($buyRequest)) {
                        /*
                         * @Note: if deliverydate is null then add to cart,
                         *  otherwise remove and redirect to the product view page
                         * */
                        if($item->getBuyRequest()->getDeliverydate() == ''){

                            $item->setCustomPrice($item->getBuyRequest()->getGiftPrice());
                            $item->setOriginalCustomPrice($item->getBuyRequest()->getGiftPrice());
                            $item->getProduct()->setIsSuperMode(true);

                            unset($buyRequest['uenc']);
                            $ced_giftcarddata = $this->serializer->serialize($buyRequest);
                            $item->setCedGiftcarddata($ced_giftcarddata);
                            $item->setCedGiftToMail($item->getBuyRequest()->getGiftToEmail());
                        }else{
                            $isMessageAdded = true;
                            $productUrls[] = $item->getProduct()->getProductUrl();
                            $cart->removeItem($item->getId());
                        }
                    }
                }
            }catch(\Exception $e){}
        }
        $quote->collectTotals()->save();

        if ($isMessageAdded && !empty($productUrls)) {
            $message = __('To Reorder the Gift Card please re-fill the details.');
            $this->messageManager->addWarningMessage($message);
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->phpCookieManager
                ->deleteCookie('mage-cache-sessid', $metadata);

            return $result->setPath($productUrls[0]);

        }
        return $result;
    }
}