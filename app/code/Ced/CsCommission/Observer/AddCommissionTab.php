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
 * @package     Ced_CsProAssign
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Context;
use \Magento\Framework\Event\Observer;

/**
 * Class AddCommissionTab
 * @package Ced\CsCommission\Observer
 */
class AddCommissionTab implements ObserverInterface
{
    /**
     * AddCommissionTab constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getTabs();
        $block->addTabAfter(
            'commission',
            [
                'label' => __('Commission Configurations'),
                'title' => __('Commission Configurations'),
                'content' => $this->context->getLayout()
                    ->createBlock('Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit\Tab\Configurations')
                    ->toHtml()
            ],
            'vpayments'
        );
    }
}
