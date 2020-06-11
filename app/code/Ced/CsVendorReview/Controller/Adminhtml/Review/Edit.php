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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Controller\Adminhtml\Review;

use Magento\Backend\App\Action;

/**
 * Class Edit
 * @package Ced\CsVendorReview\Controller\Adminhtml\Review
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsVendorReview\Model\Review
     */
    protected $review;

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
     * @param \Ced\CsVendorReview\Model\Review $review
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Session $session
     * @param Action\Context $context
     */
    public function __construct(
        \Ced\CsVendorReview\Model\Review $review,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $session,
        Action\Context $context

    )
    {
        $this->review = $review;
        $this->registry = $registry;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->review;

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->registry->register('csvendorreview_review', $model);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->set((__('Edit Review')));
        $this->_view->renderLayout();
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {

        switch ($this->getRequest()->getControllerName()) {
            case 'review':
                return $this->reviewAcl();
                break;
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::csmarketplace');
                break;
        }
    }

    /**
     * ACL check for Review
     *
     * @return bool
     */
    protected function reviewAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('Ced_CsVendorReview::manage_review');
                break;
        }
    }
}
