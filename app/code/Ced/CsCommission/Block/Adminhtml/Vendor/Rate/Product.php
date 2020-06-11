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

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Rate;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Product
 * @package Ced\CsCommission\Block\Adminhtml\Vendor\Rate
 */
class Product extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Rows cache
     *
     * @var array|null
     */
    protected $arrayRowsCache;

    protected $defaultRenderer;

    protected $actionRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array|null
     */
    public function getArrayRows()
    {
        if (null !== $this->arrayRowsCache) {
            return $this->arrayRowsCache;
        }

        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();

        if (!is_array($element->getValue()) && $element->getValue() != '') {
            $element_value = json_decode($element->getValue(), true);
        } else {
            $element_value = $element->getValue();
        }
        if ($element_value && is_array($element_value)) {
            foreach ($element_value as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->arrayRowsCache = $result;
        return $this->arrayRowsCache;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getProductTypesRenderer()
    {
        if (!$this->actionRenderer) {
            $this->actionRenderer = $this->getLayout()->createBlock(
                'Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Product\Type',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->actionRenderer->setExtraParams('style="width:90px"');
        }
        return $this->actionRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCalculationMethodRenderer()
    {
        if (!$this->defaultRenderer) {
            $this->defaultRenderer = $this->getLayout()->createBlock(
                'Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->defaultRenderer->setExtraParams('style="width:60px"');
        }
        return $this->defaultRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'types',
            [
                'label' => __('Product Types'),
                'renderer' => $this->_getProductTypesRenderer(),
            ]
        );

        $this->addColumn(
            'method',
            [
                'label' => __('Calculation Method'),
                'renderer' => $this->_getCalculationMethodRenderer(),
            ]
        );
        $this->addColumn(
            'fee',
            [
                'label' => __('Commission Fee'),
                'style' => 'width: 123px;',
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = 'Add New Rate';
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $types = $row->getTypes();
        $method = $row->getMethod();
        $options = [];
        $options['option_' . $this->_getProductTypesRenderer()->calcOptionHash($types)]
            = 'selected="selected"';
        $options['option_' . $this->_getCalculationMethodRenderer()->calcOptionHash($method)]
            = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= '<input type="hidden" name="product_dummy" id="' . $element->getHtmlId() . '" />';
        return $html;
    }
}
