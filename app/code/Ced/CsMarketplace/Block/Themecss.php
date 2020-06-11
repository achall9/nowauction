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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block;

/**
 * Class Themecss
 * @package Ced\CsMarketplace\Block
 */
class Themecss extends \Magento\Framework\View\Element\Template {

    /**
     * Themecss constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct() {
        $themeColor = $this->_scopeConfig->getValue('ced_csmarketplace/general/theme_color',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->pageConfig->addPageAsset('css/seller-reg.css');
        $this->pageConfig->addPageAsset('css/color/'.$themeColor);
    }
}