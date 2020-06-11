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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Block\Vshops;

/**
 * Class ListBlock
 * @package Ced\CsVendorReview\Block\Vshops
 */
class ListBlock extends \Ced\CsMarketplace\Block\Vshops\ListBlock
{
    /**
     * @var \Magento\Review\Model\Rating
     */
    protected $rating;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * ListBlock constructor.
     * @param \Magento\Review\Model\Rating $rating
     * @param \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\ProductList $prodListHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Review\Model\Rating $rating,
        \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\ProductList $prodListHelper,
        array $data = []
    )
    {
        $this->rating = $rating;
        $this->reviewCollection = $reviewCollection;
        $this->ratingCollection = $ratingCollection;
        parent::__construct(
            $context,
            $layerResolver,
            $urlHelper,
            $vshop,
            $vendor,
            $csmarketplaceHelper,
            $storeManager,
            $prodListHelper,
            $data
        );
    }

    /**
     * @param $vendor_products
     * @return float|int
     */
    public function getProductRating($vendor_products)
    {
        $rating_sum = 0;
        foreach ($vendor_products as $product) {
            $rating = $this->rating->getEntitySummary($product['product_id']);
            if ($rating->getSum() != null) {
                $rating_sum += ($rating->getSum() / $rating->getCount());
            }
        }
        return $rating_sum;
    }

    /**
     * @param $vendor
     * @return string
     */
    public function getReviewsSummaryHtml($vendor)
    {
        if ($this->_scopeConfig->getValue('ced_csmarketplace/vendorreview/activation')) {
            $review_data = $this->reviewCollection->create()
                ->addFieldToFilter('vendor_id', $vendor->getId())
                ->addFieldToFilter('status', 1);

            $rating = $this->ratingCollection->create()
                ->addFieldToSelect('rating_code');
            $count = 0;
            $rating_sum = 0;

            foreach ($review_data as $key => $value) {
                foreach ($rating as $k => $val) {
                    if ($value[$val['rating_code']] > 0) {
                        $rating_sum += $value[$val['rating_code']];
                        $count++;
                    }
                }
            }
            if ($count > 0 && $rating_sum > 0) {
                $width = ceil($rating_sum / $count);
                return '<div class="rating-summary">     
							 <div title="' . $width . '%" class="rating-result">
								 <span style="width:' . $width . '%;"><span>' . $width . '%</span></span>
							 </div>
							</div>';
            } else {
                $width = 0;
                return '<div class="rating-summary">     
                             <div title="' . $width . '%" class="rating-result">
                                 <span style="width:' . $width . '%;"><span>' . $width . '%</span></span>
                             </div>
                            </div>';
            }
        } else {
            return '';
        }
    }
}
