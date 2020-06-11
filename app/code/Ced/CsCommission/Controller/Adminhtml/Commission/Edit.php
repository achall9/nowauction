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

namespace Ced\CsCommission\Controller\Adminhtml\Commission;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Ced\CsCommission\Controller\Adminhtml\Commission
 */
class Edit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsCommission\Model\Commission
     */
    protected $commission;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\CsCommission\Model\Commission $commission
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\CsCommission\Model\Commission $commission,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $session
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->commission = $commission;
        $this->registry = $registry;
        $this->session = $session;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        $model = $this->commission;

        $registryObject = $this->registry;

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('cscommission_commission', $model);
        $this->resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            $this->resultPage->getLayout()->getUpdate()->addHandle('ced_popup');
        }
        return $this->resultPage;
    }
}
