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
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Ui\Coupons;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ced\GiftCard\Model\ResourceModel\GiftCoupon\CollectionFactory as GiftCollectionFactory;

/**
 * Class ProductDataProvider
 *
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;
 

    /**
     * @var \Magento\Framework\App\ResourceConnection $_coreresource
     */
    protected $_coreresource;
    

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        GiftCollectionFactory $giftCollectionFactory,
        \Magento\Framework\App\ResourceConnection $_coreresource,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
         
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
       
        $this->_coreresource = $_coreresource;



        $collection = $giftCollectionFactory->create();


        $this->collection = $collection;
       
    } 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
 
        $items = $this->collection->toArray();

        return [
            'totalRecords' => $items['totalRecords'],
            'items' => array_values($items['items']),
        ];
    }
 
}
