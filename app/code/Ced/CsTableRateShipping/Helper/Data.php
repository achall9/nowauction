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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Helper;

/**
 * Class Data
 * @package Ced\CsTableRateShipping\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_helper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\CsMarketplace\Helper\Data $marketplaceHelper
    )
    {
        parent::__construct($context);
        $this->_helper = $marketplaceHelper;
    }

    /**
     * @param int $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($storeId = 0)
    {

        if ($storeId == 0) {
            $storeId = $this->_helper->getStore()->getId();
        }
        return $this->_helper->getStoreConfig('ced_cstablerateshipping/general/active', $storeId);
    }

}
