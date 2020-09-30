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
 * @category    Ced
 * @package     Ced_CsAuction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\AddAuction\Edit;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid_records');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Auction List'));
        $this->setData('area','adminhtml');

    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {

        $this->addTab(
            'auction_details',
            [
                'label' => __('Auction Details'),
                'title' => __('Auction Details'),
                'content' => //$this->getLayout()->createBlock('Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab\Qlist')->toHtml()
                $this->getLayout()->createBlock('Ced\CsAuction\Block\AddAuction\Edit\Tab\AuctionList')
                        ->toHtml(),

            ]
        );

        $this->addTab(
            'pre_auction_details',
            [
                'label' => __('Pre Auction Details'),
                'title' => __('Pre Auction Details'),
                'url'       => $this->getUrl('*/PreAucionList/Grid', array('_current' => true)),    // Url of action which loads the grid
                'class'     => 'ajax',
            ]
        );

        $this->addTab(
            'bid_details',
            [
                'label' => __('Bid Details'),
                'title' => __('Bid Details'),
                'url'       => $this->getUrl('*/Biddetails/Grid', array('_current' => true)),    // Url of action which loads the grid
                'class'     => 'ajax',
            ]
        );
        return parent::_beforeToHtml();
    }
}