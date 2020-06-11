<?php
namespace Ced\CsCommission\Block\Adminhtml\Commission\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_commission_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Commission Information'));
    }
}