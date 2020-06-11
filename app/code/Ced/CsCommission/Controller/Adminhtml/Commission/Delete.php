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

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package Ced\CsCommission\Controller\Adminhtml\Commission
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsCommission\Model\Commission
     */
    protected $commission;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Ced\CsCommission\Model\Commission $commission
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsCommission\Model\Commission $commission
    )
    {
        parent::__construct($context);
        $this->commission = $commission;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $banner = $this->commission->load($id);
            $banner->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/', array('popup' => true));
        } else
            $this->_redirect('*/*/');
    }
}
