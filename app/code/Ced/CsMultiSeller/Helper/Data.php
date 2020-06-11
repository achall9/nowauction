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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiSeller\Helper;

/**
 * Class Data
 * @package Ced\CsMultiSeller\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $modulemanager;

    /**
     * Data constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $modulemanager
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $modulemanager
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->storeManager = $storeManager;
        $this->modulemanager = $modulemanager;
    }

    /**
     * Check Product Admin Approval required
     */
    public function isProductApprovalRequired()
    {
        return $this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/ced_csmultiseller/approval',
            $this->storeManager->getStore(null)->getId());
    }

    /**
     * Check Product Admin Approval required
     */
    public function isEnabled()
    {
        if ($this->modulemanager->isEnabled('Ced_CsMarketplace')) {
            return $this->csmarketplaceHelper->getStoreConfig('ced_csmultiseller/general/activation_csmultiseller', 0);
        }
        return false;
    }

}
