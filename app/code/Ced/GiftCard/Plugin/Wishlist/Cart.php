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
namespace Ced\GiftCard\Plugin\Wishlist;
use \Ced\GiftCard\Model\Product\Type\GiftCard;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Cart
 * @package Ced\GiftCard\Plugin\Wishlist
 */
class Cart
{
    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultFactory;

    /**
     * Cart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Wishlist\Model\ItemFactory $itemFactory) {

        $this->_redirect = $context->getRedirect();
        $this->_url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->itemFactory = $itemFactory;
        $this->resultFactory = $context->getResultRedirectFactory();
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        $subject,
        callable $proceed){

        $itemId = (int)$subject->getRequest()->getParam('item');
        /* @var $item \Magento\Wishlist\Model\Item */
        $item = $this->itemFactory->create()->load($itemId);
        $product = $item->getProduct();
        if($product->getTypeId() == GiftCard::TYPE_CODE){

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create();

            $redirectUrl = $this->_url->getUrl('catalog/product/view',['id'=> $product->getId()]);
            $this->messageManager->addNoticeMessage(
                __('We can\'t add the item to the cart right now. Please Fill all the required values.')
            );
           
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        }else{
            return $proceed();
        }
    }
}
