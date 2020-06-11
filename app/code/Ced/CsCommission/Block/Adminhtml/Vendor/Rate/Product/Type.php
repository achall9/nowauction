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

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Product;

class Type extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Catalog\Model\Product\Type $productType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productType = $productType;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        $this->setExtraParams('style="width: 150px;"');
        if (!$this->getOptions()) {
            $types = $this->productType->getOptionArray();
            if (!empty($types)) {
                foreach ($types as $value => $label) {
                    if ($value == 'grouped' || $value == 'bundle' ) {
                        continue;
                    }
                    if (isset($value) && $value && isset($label) && $label) {
                        $this->addOption($value, $label);
                    }
                }
            }
        }
        return parent::_toHtml();
    }
}
