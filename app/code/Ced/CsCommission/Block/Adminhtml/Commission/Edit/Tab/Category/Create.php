<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * New attribute panel on product edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Ced\MealsUnite\Block\Adminhtml\BookingSetting\Edit\Tab\BookingCancelationPolicy;

use Magento\Backend\Block\Widget\Button;

class Create extends Button
{
    /**
     * Config of create new attribute
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_config = null;

    /**
     * Retrieve config of new attribute creation
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->setId(
            'create_attribute_' . $this->getConfig()->getGroupId()
        )->setType(
            'button'
        )->setClass(
            'action-add'
        )->setLabel(
            __('Add Booking Cancellation Policy')
        )->setDataAttribute(
            [
                'mage-init' => [
                    'cancellationPolicySetting' => [
                        'url' => $this->getUrl(
                            'mealsunite/bookingcancelationpolicy/edit',
                            [
                                'popup' => 1
                            ]
                        ),
                    ],
                ],
            ]
        );

        $this->getConfig()->setUrl(
            $this->getUrl(
                'mealsunite/bookingsetting/edit',
                [
                    'popup' => 1
                ]
            )
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setCanShow(true);
        $this->_eventManager->dispatch(
            'adminhtml_mealsunite_booking_setting_create_html_before',
            ['block' => $this]
        );
        if (!$this->getCanShow()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }
}
