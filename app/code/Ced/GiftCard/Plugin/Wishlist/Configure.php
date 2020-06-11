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

/**
 * Class Configure
 * @package Ced\GiftCard\Plugin\Wishlist
 */
class Configure
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultFactory;

    /**
     * Configure constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product $product) {

        $this->_redirect = $context->getRedirect();
        $this->_url = $context->getUrl();
        $this->messageManager = $context->getMessageManager();
        $this->product = $product;
        $this->resultFactory = $context->getResultRedirectFactory();
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(
        $subject,
        callable $proceed){

        $product_id = (int)$subject->getRequest()->getParam('product_id');
        $product = $this->product->loadByAttribute('entity_id', $product_id);
        if($product->getTypeId() == GiftCard::TYPE_CODE){

            $this->messageManager->addNoticeMessage(
                __('We can\'t add the item to the cart right now. Please Fill all the required values.')
            );
            $resultRedirect = $this->resultFactory->create();

            $redirectUrl = $this->_url->getUrl('catalog/product/view',['id'=> $product->getId()]);
            $resultRedirect->setUrl($redirectUrl);

            return $resultRedirect;
        }else{

            return $proceed();
            
        }
    }
}
