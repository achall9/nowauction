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
 * @author 		   CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source;

/**
 * Class Status
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class Status extends AbstractBlock
{
    /**
     * Status constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve Option values array
     *
     * @param bool $defaultValues
     * @return array
     */
    public function toOptionArray($defaultValues = false)
    {
        $options = array();
        $new = \Ced\CsMarketplace\Model\Vendor::VENDOR_NEW_STATUS;
        $approve = \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS;
        $disapprove = \Ced\CsMarketplace\Model\Vendor::VENDOR_DISAPPROVED_STATUS;

        if ($defaultValues) {
            $options[$new] = __('New');
        }
        $options[$approve] = __('Approved');
        $options[$disapprove] = __('Disapproved');
        return $options;
    }
}
