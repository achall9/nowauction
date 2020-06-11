<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category  Ced
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\CsOrder\Block\Adminhtml\Vendor\Products;

class Grid extends \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vproducts
{
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'remove',
            [
                'header' => __('Remove'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Remove'),
                        'url' => [
                            'base' => 'csassign/assign/remove'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ], 'status'
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->addItem(
            'remove', array(
                'label' => __('Remove'),
                'url' => $this->getUrl('csassign/assign/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
    }
}
