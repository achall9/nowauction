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
 * @package     Ced_CsMessaging
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Adminhtml\Vadmin;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Changestatus
 * @package Ced\CsMessaging\Controller\Adminhtml\Vadmin
 */
class Changestatus extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $collectionFctory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFctory = $collectionFctory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $threadId = $this->getRequest()->getParam('thread_id');
        $collection = $this->collectionFctory->create();
        $collection->addFieldToFilter('thread_id', $threadId);

        if (!empty($collection)) {
            foreach ($collection as $message) {
                if ($message->getReceiverId() == \Ced\CsMessaging\Helper\Data::ADMIN_ID) {
                    $message->setStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['success' => true]);
        return $resultJson;
    }


}
