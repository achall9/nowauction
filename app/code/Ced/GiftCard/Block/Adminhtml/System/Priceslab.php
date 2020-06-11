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
 * @category  Ced
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Block\Adminhtml\System;

/**
 * Class Priceslab
 * @package Ced\GiftCard\Block\Adminhtml\System
 */
class Priceslab extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var $_elementFactory
     */
    protected $_elementFactory;

    /**
     * @var array
     */
    protected $_columns = [];
    /**
     * @var
     */
    protected $_customerGroupRenderer;
    /**
     * @var
     */
    protected $_customHours;
    /**
     * @var
     */
    protected $_custompriceslab;
    /**
     * @var bool
     */
    protected $_addAfter = true;
    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     *
     */
    protected function _prepareToRender()
    {

        $this->addColumn('priceslab',
            [
                'label' => __('Prices'),
                'class' => 'required-entry validate-number validate-greater-than-zero integer required',
                'renderer' => [],
            ]); 
        $this->_addButtonLabel = __('Add Priceslab');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustompriceslab()
    {
        if (!$this->_custompriceslab) {
            $this->_custompriceslab = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_custompriceslab;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {

        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->getCustompriceslab()->calcOptionHash($row->getData('priceslab'))] =
            'selected="selected"';
 
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}