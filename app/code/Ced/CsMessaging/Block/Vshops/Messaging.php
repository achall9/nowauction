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
 * @package   Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMessaging\Block\Vshops;

/**
 * Class Messaging
 * @package Ced\CsMessaging\Block\Vshops`
 */
class Messaging extends \Magento\Framework\View\Element\Template
{
    /**
     * Messaging constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\Session $vendorSession,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->vendorSession = $vendorSession;
        parent::__construct($context, $data);
    }

    public function getLoggedInVendorId()
    {
        return $this->vendorSession->getVendorId();
    }



    /**
     * @return mixed
     */
    public function getVendorId()
    {
        if($this->_coreRegistry->registry('current_vendor'))
            return $this->_coreRegistry->registry('current_vendor')->getId();
    }
}
