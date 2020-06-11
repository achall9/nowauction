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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Adminhtml\Vendor\Entity;

/**
 * Class Grid
 * @package Ced\CsMessaging\Block\Adminhtml\Vendor\Entity
 */
class Grid extends \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid
{

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->addItem('send_message',
            [
                'label'=> __('Send Message'),
                'url'  => $this->getUrl('csmessaging/vadmin/compose', ['_current'=>true]),
            ]
        );

        return $this;
    }

}
