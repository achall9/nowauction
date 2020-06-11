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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source ;

/**
 * Class Paymentmethods
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class Paymentmethods  extends AbstractBlock
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    const XML_PATH_CED_CSMARKETPLACE_VENDOR_PAYMENT_METHODS = 'ced_csmarketplace/vendor/payment_methods';

    /**
     * Paymentmethods constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->_context = $context;
    }

    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payment_methods = $this->_context->getScopeConfig()->getValue(self::XML_PATH_CED_CSMARKETPLACE_VENDOR_PAYMENT_METHODS);
        $options = [];
        foreach ($payment_methods as $payment_method =>$model_class) {
            $payment_method = strtolower(trim($payment_method));
            $options[] = [
                'value' => $payment_method,
                'label' => __(ucfirst($payment_method)),
                'model_class' => $model_class
            ];
        }
        return $options;
    }
}
