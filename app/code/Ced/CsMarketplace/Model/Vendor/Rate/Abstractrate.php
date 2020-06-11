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

namespace Ced\CsMarketplace\Model\Vendor\Rate;


use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

class Abstractrate extends AbstractExtensibleModel
{
    protected $_order = null;
    protected $_vendorId = null;

    protected $request;

    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);

        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int) $this->request->getParam('store', 0);
        if($storeId) {
            return $this->storeManager->getStore($storeId);
        } else {
            return $this->storeManager->getStore(null);
        }
    }

    /**
     * Get current store
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     *  Set Order
     * @param $order
     * @return Abstractrate
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     *  Get Order
     *
     * @param null $order
     * @return null
     */
    public function getOrder($order = null)
    {
        if($this->_order == null) {
            $this->_order = $order;
        }
        return $this->_order;
    }

    /**
     *  Set Order
     * @param $vendorId
     * @return Abstractrate
     */
    public function setVendorId($vendorId)
    {
        $this->_vendorId = $vendorId;
        return $this;
    }

    /**
     *  Get Order
     * @param int $vendorId
     * @return int|null
     */
    public function getVendorId($vendorId = 0)
    {
        if ($this->_vendorId == null) {
            $this->_vendorId = $vendorId;
        }
        return $this->_vendorId;
    }

    /**
     * Get the commission based on group
     * @param int $grand_total
     * @param int $base_grand_total
     * @param int $base_to_global_rate
     * @param array $commissionSetting
     * @return bool
     */
    public function calculateCommission($grand_total = 0, $base_grand_total = 0, $base_to_global_rate = 1, $commissionSetting = array())
    {
        return false;
    }
}
