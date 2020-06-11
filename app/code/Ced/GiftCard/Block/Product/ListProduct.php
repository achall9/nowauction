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
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Block\Product;

use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class ListProduct
 * @package Ced\GiftCard\Block\Product
 */
class ListProduct
{

    /**
     * @param $listProduct
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function beforegetAddToCartPostParams(
        $listProduct,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getTypeId() == GiftCard::TYPE_CODE){
            $product->setRequiredOptions([GiftCard::TYPE_CODE]);
        }
        return [$product];
    }
}
