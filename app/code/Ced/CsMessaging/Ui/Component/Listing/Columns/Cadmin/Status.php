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
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Ui\Component\Listing\Columns\Cadmin;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 * @package Ced\CsMessaging\Ui\Component\Listing\Columns\Cadmin
 */
class Status extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * OrderId constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {


                $messageCollection = $this->collectionFactory->create();
                $messageCollection->addFieldToFilter('thread_id',$item['id'])
                    ->addFieldToFilter('receiver_id',['eq'=>\Ced\CsMessaging\Helper\Data::ADMIN_ID])
                    ->addFieldToFilter('status',\Ced\CsMessaging\Helper\Data::STATUS_NEW);

                $fieldName = $this->getData('name');
                $item[$fieldName] = __('new').' ('.$messageCollection->getSize().')';
            }
        }
        return $dataSource;
    }
}
