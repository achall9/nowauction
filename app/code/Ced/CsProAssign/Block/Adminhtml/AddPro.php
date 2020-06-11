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
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProAssign\Block\Adminhtml;

/**
 * Adminhtml sales order create items block
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class AddPro extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Contains button descriptions to be shown at the top of accordion
     *
     * @var array
     */
    protected $_buttons = [];

    /**
     * Define block ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('items.phtml');
    }

    public function getGridHtml()
    {
        return $this->getLayout()->createBlock('Ced\CsProAssign\Block\Adminhtml\Items\Grid')->toHtml();
    }

    /**
     * Accordion header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Assign products');
    }

    /**
     * Add button to the items header
     *
     * @param  array $args
     * @return void
     */
    public function addButton($args)
    {
        $this->_buttons[] = $args;
    }

    /**
     * Render buttons and return HTML code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddButtonsHtml()
    {
        $html = '';
        $addButtonData = [
            'label' => 'Assign Product',
            'class' => 'add',
            'onclick' => 'addProduct()'
        ];
        $html .= $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            $addButtonData
        )->toHtml();

        return $html;
    }

    public function getAssignButtonsHtml()
    {
        $assignButtonData = [
            'label' => __('Assign Selected Product(s) to Vendor'),
            'onclick' => 'assignProduct()',
            'class' => 'add',
        ];
        return $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')->setData($assignButtonData)->toHtml();
    }

}

