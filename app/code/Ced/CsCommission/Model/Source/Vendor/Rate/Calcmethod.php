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
 * @category    Ced
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * Ced_CsCommission source updates type
 *
 * @category    Ced
 * @package     Ced_CsCommission
 * @author        CedCommerce Core Team <coreteam@cedcommerce.com>
 */

namespace Ced\CsCommission\Model\Source\Vendor\Rate;

class Calcmethod extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function toOptionArray()
    {
        return [
            [
                'value' => \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_FIXED,
                'label' => __('Fixed')
            ],
            [
                'value' => \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_PERCENTAGE,
                'label' => __('Percentage')
            ]
        ];
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $label = '';
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                $label = $v['label'];
            }
        }
        return $label;
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = [];
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }
}
