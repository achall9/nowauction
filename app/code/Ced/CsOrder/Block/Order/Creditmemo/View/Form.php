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
namespace Ced\CsOrder\Block\Order\Creditmemo\View;

/**
 * Creditmemo view form
 *
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Form
{
    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $vordersCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;
    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsOrder\Model\ResourceModel\Vorders\Collection $vordersCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsOrder\Model\Vorders $vordersCollection,
        array $data = []
    )
    {
        $this->csorderHelper = $csorderHelper;
        $this->customerSession = $customerSession;
        $this->vordersCollection = $vordersCollection;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Get order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('csorder/vorders/view', ['order_id' => $this->getCreditmemo()->getOrderId()]);
    }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
    public function getCsorderHelper(){
        return $this->csorderHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession(){
        return $this->customerSession;
    }

    /**
     * @return mixed
     */
    public function getVendorOrders(){
        return $this->vordersCollection->getCollection();
    }

}
