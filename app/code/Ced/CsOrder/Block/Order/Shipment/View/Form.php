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

namespace Ced\CsOrder\Block\Order\Shipment\View;

class Form extends \Magento\Shipping\Block\Adminhtml\View\Form
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
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsOrder\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
     * @param array $data
     */
  public function __construct(
      \Magento\Backend\Block\Template\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Sales\Helper\Admin $adminHelper,
      \Magento\Shipping\Model\CarrierFactory $carrierFactory,
      \Ced\CsOrder\Helper\Data $csorderHelper,
      \Magento\Customer\Model\Session $customerSession,
      \Ced\CsOrder\Model\Vorders $vordersCollection,
      array $data = []
  )
  {
      $this->csorderHelper = $csorderHelper;
      $this->customerSession = $customerSession;
      $this->vordersCollection = $vordersCollection;
      parent::__construct($context, $registry, $adminHelper, $carrierFactory, $data);
  }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
  public function getCsorderHelper(){
      return $this->csorderHelper;
  }

    /**
     * @return mixed
     */
  public function getSession(){
      return $this->customerSession;
  }

    /**
     * @return mixed
     */
  public function getVordersCollection(){
      return $this->vordersCollection->getCollection();
  }
}
