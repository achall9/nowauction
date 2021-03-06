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
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsOrder\Block\Order\Shipment\Create;

/**
 * Adminhtml shipment create form
 */
class Form extends \Magento\Shipping\Block\Adminhtml\Create\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('csorder/*/save', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry(){
        return $this->registry;
    }
}
