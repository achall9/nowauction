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
namespace Ced\GiftCard\Ui\DataProvider\ProductFormModifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for "Custom Attribute" field of product page
 */
class Attribute extends AbstractModifier
{

    /**
     * Attribute constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }


    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {

        $meta = $this->customizeAttribute($meta);

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }


    /**
     * @param array $meta
     * @return array
     */
    protected function customizeAttribute(array $meta)
    {

        $gift_expiry_days = 'gift_expiry_days';
        $elementPathgift_expiry_days = $this->arrayManager->findPath($gift_expiry_days, $meta, null, 'children');
        $containergift_expiry_days = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . $gift_expiry_days, $meta, null, 'children');

        if ($elementPathgift_expiry_days) {

            $meta = $this->arrayManager->merge(
                $containergift_expiry_days,
                $meta,
                [
                    'children'  => [
                        'gift_expiry_days' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'validation' => [
                                            'required-entry' => 1,
                                            'validate-number' => 1,
                                            'validate-greater-than-zero' => 1,
                                            'integer' => 1,
                                            'less-than-equals-to' => 365,
                                        ]
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            );
        }

        $gift_min_amount = 'gift_min_amount';
        $elementPathgift_gift_min_amount = $this->arrayManager->findPath($gift_min_amount, $meta, null, 'children');
        $containergift_gift_min_amount = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . $gift_min_amount, $meta, null, 'children');
        if ($elementPathgift_gift_min_amount) {

            $meta = $this->arrayManager->merge(
                $containergift_gift_min_amount,
                $meta,
                [
                    'children'  => [
                        $gift_min_amount => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'notice' => __('Please Ensure manually, that it is less than the maximum amount of giftcard')
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            );
        }
        $gift_max_amount = 'gift_max_amount';
        $elementPathgift_gift_max_amount = $this->arrayManager->findPath($gift_max_amount, $meta, null, 'children');
        $containergift_gift_max_amount = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . $gift_max_amount, $meta, null, 'children');
        if ($elementPathgift_gift_max_amount) {

            $meta = $this->arrayManager->merge(
                $containergift_gift_max_amount,
                $meta,
                [
                    'children'  => [
                        $gift_max_amount => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'notice' => __('Please Ensure manually, that it is more than the minimum amount of giftcard')
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }

}