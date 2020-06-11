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
 * @package     Ced_CsSplitCart
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Cart;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CouponPost extends \Magento\Checkout\Controller\Cart\CouponPost
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }
    public function execute(){

        /** @var  \Magento\Quote\Model\Quote $quote */
        $cartId =0;
        $quote = $this->quoteRepository->getActive($cartId);
        $hasGiftCard = false;
        foreach($quote->getAllItems() as $item){
            if ($item->getProductType() == 'giftcard' ) {
                $hasGiftCard = true;
            }
        }

        if($hasGiftCard){

        	$this->messageManager->addErrorMessage(
        	    __(
        	        'Could not apply coupon code on the giftcard product.'
        	    )
        	); 
        	return $this->_goBack();
        }else{
            return parent::execute();
        } 
    }
}
