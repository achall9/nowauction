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

namespace Ced\CsMessaging\Controller\Adminhtml\Vcustomer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Changestatus
 * @package Ced\CsMessaging\Controller\Adminhtml\Admin
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
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vcustomerMessageCollFactory = $vcustomerMessageCollFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $threadId = $this->getRequest()->getParam('thread_id');

        $vcustomerCollection = $this->vcustomerMessageCollFactory->create();

        $vcustomerCollection->addFieldToFilter('thread_id', $threadId);

        if (!empty($vcustomerCollection)) {
            foreach ($vcustomerCollection as $message) {
                if ($message->getSender() != \Ced\CsMessaging\Helper\Data::ADMIN_AS_SENDER) {
                    $message->setAdminStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['success' => true]);
        return $resultJson;
    }


}
