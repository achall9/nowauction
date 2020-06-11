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
namespace Ced\GiftCard\Plugin\Sales\Model;

use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class Order
 * @package Ced\GiftCard\Plugin\Sales\Model\Order
 */
class Order
{

    /**
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundCanCreditmemo($subject, callable $proceed)
    {
        $result = $proceed();
        if($result){
            $allowCreditMemo = 0;
            foreach($subject->getAllItems() as $item){
                if ($item->getProductType() != GiftCard::TYPE_CODE) {
                    $allowCreditMemo+=1;
                }
            }
            if($allowCreditMemo>0){

            }else{
                if(!$subject->getForcedCanCreditmemo()){
                    $result= false;
                }
            }

        }
      
        return $result;
    }
}
