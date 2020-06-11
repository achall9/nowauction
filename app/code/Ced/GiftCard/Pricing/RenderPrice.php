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

namespace Ced\GiftCard\Pricing;

use Ced\GiftCard\Model\Product\Type\GiftCard;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use \Ced\GiftCard\Helper\Config as ConfigHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;

/**
 * Class RenderPrice
 * @package Ced\GiftCard\Pricing
 */
class RenderPrice extends FinalPriceBox
{
    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param array $data
     * @param SalableResolverInterface $salableResolver
     * @param MinimalPriceCalculatorInterface $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = [],
        PricingHelper $pricingHelper,
        ConfigHelper $configHelper,
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    )
    {
        $this->configHelper = $configHelper;
        $this->pricingHelper = $pricingHelper;
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
    }

    /**
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        if (!($product = $this->getSaleableItem())) {
            return parent::wrapResult($html);
        }

        if ($product->getTypeId() == GiftCard::TYPE_CODE) {

            $priceHelper = $this->pricingHelper;

            $minPrice = $priceHelper->currency($product->getGiftMinAmount(), true, true);
            $maxPrice = $priceHelper->currency($product->getGiftMaxAmount(), true, true);
            $html = __(' In Stock') . '<div class="gift-price"> ' . __(' From ') . $minPrice . __(' To ') . $maxPrice . '</div>';
            if (!$product->isSalable()) {
                $config = $this->configHelper;
                $notAvaliableText = $config->giftNotAvaliableText();
                $html .= '<p class="not-salable-gift-card">' . $notAvaliableText . '</p>';
            }

            return parent::wrapResult($html);
        } else {
            return parent::wrapResult($html);
        }
    }
}
