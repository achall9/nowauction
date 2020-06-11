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
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Category
 * @package Ced\CsCommission\Helper
 */
class Category extends AbstractHelper
{

    const CONFIG_DB_CATEGORY_USAGE_OPTIONS = 'ced_vpayments/general/commission_cw';

    const OPTION_CATEGORY_PREFIX = '';

    const OPTION_CATEGORY_PREFIX_SEPARATOR = '';

    /**
     * @var \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category constructor.
     * @param Context $context
     * @param \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $category
     * @return string
     */
    public function getCodeValue($category = 'all')
    {
        return self::OPTION_CATEGORY_PREFIX . self::OPTION_CATEGORY_PREFIX_SEPARATOR . $category;
    }

    /**
     * @param $value
     * @return false|string
     */
    public function getSerializedOptions($value)
    {
        $uniqueValues = [];
        if (is_array($value)) {
            $cnt = 0;
            foreach ($value as $key => $val) {
                if (!is_array($val)) {
                    continue;
                }
                if (isset($val['method']) && !in_array($val['method'], ['fixed', 'percentage'])) {
                    $val['method'] = 'fixed';
                }
                switch ($val['method']) {
                    case "fixed":
                        $val['fee'] = round($val['fee'], 2);
                        break;
                    case "percentage":
                        $val['fee'] = min((int)$val['fee'], 100);
                        break;
                }
                if (isset($val['priority']) && !is_numeric($val['priority'])) {
                    $lengthPriority = strlen($val['priority']);
                    if ($lengthPriority > 0) {
                        $val['priority'] = (int)$val['priority'];
                    } else {
                        $val['priority'] = $cnt;
                    }
                }

                if (!isset($uniqueValues[$this->getCodeValue($val['category'])])) {
                    $uniqueValues[$this->getCodeValue($val['category'])] = $val;
                } elseif (isset($uniqueValues[$this->getCodeValue($val['category'])]) &&
                    isset($uniqueValues[$this->getCodeValue($val['category'])]['priority']) &&
                    isset($val['priority'])
                    && (int)$val['priority'] < (int)$uniqueValues[$this->getCodeValue($val['category'])]['priority']) {
                    $uniqueValues[$this->getCodeValue($val['category'])] = $val;
                }
                $cnt++;
            }
        }
        if ($uniqueValues != '') {
            return json_encode($uniqueValues);
        } else {
            return '';
        }
    }

    /**
     * @param int $vendorId
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUnserializedOptions($vendorId = 0, $storeId = 0)
    {
        $arr = [];

        if ($storeId) {
            $type = 'store';
            $collection = $this->collectionFactory->create()->load();
            $collection->addFieldToFilter('type', $type);
            $collection->addFieldToFilter('type_id', $storeId);
            $collection->addFieldToFilter('vendor', $vendorId);
            $collection->setOrder('priority', 'ASC');
            if ($collection->count() == 0) {
                $store = $this->storeManager->getStore(1);
                $websiteId = $store->getWebsiteId();
                $collection->addFieldToFilter('type', 'website');
                $collection->addFieldToFilter('type_id', $websiteId);
                $collection->addFieldToFilter('vendor', $vendorId);
                $collection->setOrder('priority', 'ASC');
                /* search for website*/
            }

            if ($collection->count() == 0) {
                /* search for default value of vendor */
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('type', 'default')
                    ->addFieldToFilter('type_id', 0)
                    ->addFieldToFilter('vendor', $vendorId)
                    ->setOrder('priority', 'ASC');
            }
        } else {
            $type = 'default';
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('type', 'default')
                ->addFieldToFilter('type_id', 0)
                ->addFieldToFilter('vendor', $vendorId)
                ->setOrder('priority', 'ASC');
        }

        if ($collection->count() == 0) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('type', 'default')
                ->addFieldToFilter('type_id', 0)
                ->addFieldToFilter('vendor', 0)
                ->setOrder('priority', 'ASC');
        }
        $data = [];
        if ($collection->count() > 0) {
            $collection = $collection->toArray();
            foreach ($collection['items'] as $commission) {
                $data[$commission['category']] = $commission;
            }
        }

        return $data;
    }

    /**
     * @param null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOptions($storeId = null)
    {
        $rawOptions = $this->getUnserializedOptions($storeId);
        return $rawOptions;
        $options = [];
        if (is_array($rawOptions) && !empty($rawOptions)) {
            foreach ($rawOptions as $option) {
                $options[$option['code']] = $option;
            }
        }
        return $options;
    }

    /**
     * @param null $storeId
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultOption($storeId = null)
    {
        $options = $this->getUnserializedOptions($storeId);
        //last one set as default
        foreach ($options as $k => $val) {
            if ($val['default'] == 1) {
                return $val;
            }
        }
        return [];
    }
}
