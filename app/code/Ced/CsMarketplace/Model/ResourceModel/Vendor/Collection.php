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

namespace Ced\CsMarketplace\Model\ResourceModel\Vendor;

use Ced\CsMarketplace\Model\Vpayment;

/**
 * Class Collection
 * @package Ced\CsMarketplace\Model\ResourceModel\Vendor
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * Name of collection model
     */
    const CUSTOMER_MODEL_NAME = 'Ced\CsMarketplace\Model\Vendor';

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $vOrderCollectionFactory;

    /**
     * @var string
     */
    protected $_modelName;

    /**
     * Collection constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vOrderCollectionFactory
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param string $modelName
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vOrderCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->vOrderCollectionFactory = $vOrderCollectionFactory;
        $this->_modelName = $modelName;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $connection
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->_modelName, 'Ced\CsMarketplace\Model\ResourceModel\Vendor');
    }

    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->resetJoinLeft();

        return $select;
    }

    /**
     * Reset left join
     *
     * @param  int $limit
     * @param  int $offset
     * @return \Magento\Framework\DB\Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }

    /**
     * Retrieve Option values array
     * @param int $vendor_id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray($vendor_id = 0)
    {
        $options = array();
        $vendors = $this->addAttributeToSelect(array('name','email'));
        if ($vendor_id) {
            $vendors->addAttributeToFilter('entity_id', array('eq'=>(int)$vendor_id));
        }
        $options['']=__('-- please select vendor --');
        foreach ($vendors as $vendor) {
            $options[$vendor->getId()] = $vendor->getName().' ('.$vendor->getEmail().')';
        }
        return $options;
    }

    /**
     * Retrieve Option values array for payment
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionpayArray()
    {
        $vorders = $this->vOrderCollectionFactory->create()
            ->addFieldToFilter('payment_state', ['in' => [Vpayment::PAYMENT_STATUS_OPEN, Vpayment::PAYMENT_STATUS_REFUND]])
            ->addFieldToFilter('order_payment_state', ['in' => [Vpayment::PAYMENT_STATUS_PAID, Vpayment::PAYMENT_STATUS_REFUND]]);

        $vorders->getSelect()->group('vendor_id');

        $verdersArray = $vorders->getColumnValues('vendor_id');

        $options = array();
        $vendors = $this->addAttributeToSelect(array('name','email'));

        if (!empty($verdersArray)){
            $vendors->addAttributeToFilter('entity_id', ['in'=>$verdersArray]);

            $options[''] = __('-- please select vendor --');
            foreach ($vendors as $vendor) {
                $options[$vendor->getId()] = $vendor->getName().' ('.$vendor->getEmail().')';
            }

        } else {
            $options[''] = __('-- No vendor is available for payment --');
        }
        return $options;
    }
}
