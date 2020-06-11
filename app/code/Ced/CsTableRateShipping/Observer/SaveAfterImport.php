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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class SaveAfterImport
 * @package Ced\CsTableRateShipping\Observer
 */
Class SaveAfterImport implements ObserverInterface
{

    /**
     * @var \Ced\CsTableRateShipping\Model\Resource\Carrier\TablerateFactory
     */
    protected $_advanceFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_object;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * SaveAfterImport constructor.
     * @param \Ced\CsTableRateShipping\Model\Resource\Carrier\TablerateFactory $advanceFactory
     * @param \Magento\Framework\DataObject $object
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Ced\CsTableRateShipping\Model\Resource\Carrier\TablerateFactory $advanceFactory,
        \Magento\Framework\DataObject $object,
        ManagerInterface $messageManager
    )
    {

        $this->_object = $object;
        $this->_advanceFactory = $advanceFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $eventData = $observer->getData();
        if (empty($_FILES['groups']['name']['tablerate']['import'])) {
            return $this;
        }
        try {
            $this->_advanceFactory->create()->uploadAndImport($this->_object);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }
}
