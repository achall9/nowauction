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
namespace Ced\GiftCard\Ui\FormDataProvider;

use Ced\GiftCard\Model\ResourceModel\GiftTemplate\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * GiftCardProvider
 *
 */

class GiftCardProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftCard\Collection
     */
    protected $collection;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /*
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * */
    protected $_product;
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) { 
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $blockCollectionFactory->create();
        $this->filterPool = $filterPool;
        $this->_product = $productCollectionFactory->create();

    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {   
        if (isset($this->loadedData)) {
            return $this->loadedData;
        } 
        $items = $this->collection;
         
        foreach ($items as $bannerData) {
            $this->loadedData[$bannerData->getId()] = $bannerData->getData();
        }
         
        return $this->loadedData;
    }
}
