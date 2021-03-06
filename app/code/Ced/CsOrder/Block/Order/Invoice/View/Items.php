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
namespace Ced\CsOrder\Block\Order\Invoice\View;

/**
 * Adminhtml sales item renderer
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Items
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    public $vproducts;

    /**
     * Items constructor.
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->vproducts = $vproducts;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession(){
        return $this->customerSession;
    }
}
