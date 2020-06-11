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
 * Class MassDelete
 * @package Ced\CsVendorReview\Controller\Adminhtml\Review
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsVendorReview\Model\Review
     */
    protected $review;

    /**
     * MassDelete constructor.
     * @param \Ced\CsVendorReview\Model\Review $review
     * @param Action\Context $context
     */
    public function __construct(
        \Ced\CsVendorReview\Model\Review $review,
        Action\Context $context
    )
    {
        $this->review = $review;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $ids = $this->getRequest()->getParam('id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $this->review->load($id)->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
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
