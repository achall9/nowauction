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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

 
namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit;
 
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
 	protected $_directoryHelper;
     
    public function __construct(
    		\Magento\Backend\Block\Template\Context $context,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Data\FormFactory $formFactory,
    		\Magento\Directory\Helper\Data $directoryHelper,
    		array $data = []
    ) {
    	parent::__construct($context, $registry,$formFactory,$data);
    	$this->_directoryHelper = $directoryHelper;
    	$this->_coreRegistry = $registry;
    }
    
	protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }

	protected function _prepareForm()
	{
		$form = $this->_formFactory->create([
						'data' => [
                                'id' => 'edit_form',
                                'action' => $this->getUrl('*/*/save', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]),
                                'method' => 'post',
        						'enctype' => 'multipart/form-data',
                        ],
					]	
				);

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
	/**
	 * Get form HTML
	 *
	 * @return string
	 */
	public function getFormHtml()
	{
		if (is_object($this->getForm())) {
		$html ='';
		
		$html .= "<script type=\"text/javascript\">" .
				"require(['mage/adminhtml/form'], function(){" .
				"window.updater = new RegionUpdater('country_id'," .
				" 'region', 'region_id', " .
				$this->_directoryHelper->getRegionJson() .
				", 'disable');});</script>";
		
		$html.="<script>require(['jquery','jquery/ui'
								   ], function($){
				                
									   	var company_banner = $('#company_banner');
									   	company_banner.attr('accept', 'image/*');
									 	company_banner.change(function(e) {
									 		var fileUpload = this;  
									 	     
									 	            //Check whether HTML5 is supported.
									 	            if (typeof (fileUpload.files) != 'undefined') {
									 	                //Initiate the FileReader object.
									 	                var reader = new FileReader();
									 	                //Read the contents of Image File.
									 	                reader.readAsDataURL(fileUpload.files[0]);
									 	                reader.onload = function (e) {
									 	                    //Initiate the JavaScript Image object.
									 	                    var image = new Image();
									 	     
									 	                    //Set the Base64 string return from FileReader as source.
									 	                    image.src = e.target.result;
									 	                           
									 	                    //Validate the File Height and Width.
									 	                    image.onload = function () {
						                                        var width = this.width;
						                                        var height = this.height;
									 	                        var ratio = width/height; 
									 	                        var correctImage = width > height; 
									 	                        var minimage = width >= 1000 && height >= 300;
									 	                        var allowedRatio  = 1000/300; 
									 	                        var validate  = (ratio >= 3.16  && ratio <= 3.5);
									 	                        
									 	                        if (!correctImage || !minimage || !(validate)) {
									 	                        	alert(\"Minimum allowed banner dimension is 1000px X 300px and width to height ratio must be around 10:3. Current image dimension is \"+width+\"px X \"+height+\"px. \");
									 	                             
									 	                            company_banner.val(null);
									 	                            return false;
									 	                        } 
									 	                        return true;
									 	                    };

									 	                }
									 	            } else {
									 	                alert('This browser does not support HTML5.');
									 	                company_banner.val(null);
									 	                return false;
									 	            } 

									 	});
										  $( document ).ready(function() {
				
										    var country_id = document.getElementById('country_id').value;
										    var rurl ='".$this->getUrl('*/*/country',array('_nosid'=>true))."';
										    var formkey = '".$this->getFormKey()."';
										    $.ajax({
												url: rurl,
												type: 'POST',
												data: {cid:country_id,form_key:formkey},
												dataType: 'html',
												success: function(stateform) {
										    		 stateform =  JSON.parse(stateform);
													 if(stateform=='true'){
										          		 document.getElementById('region').parentNode.parentNode.style.display='none';
										          		 document.getElementById('region_id').parentNode.parentNode.style.display='block';
										        	   }else{
										          		 document.getElementById('region_id').parentNode.parentNode.style.display='none'; 
										          		 document.getElementById('region').parentNode.parentNode.style.display='block'; 
										         		}
												}
										    });
										var element = document.getElementById('region_id');
										if(element){
										  if($(element).is(':visible')){
										     element.value = '".$this->_coreRegistry->registry('vendor_data')->getRegionId()."';
									          }else{
		                                        	                         setTimeout(function(){ element.value = '".$this->_coreRegistry->registry('vendor_data')->getRegionId()."';
										    }, 5000);
		                                                                    }
										  }										    
									   	 }); 

										window.onload = function() {
											var country_id = document.getElementById('country_id');
										   	country_id.onchange = function() {
											    var country_id_val = document.getElementById('country_id').value;
											    var rurl ='".$this->getUrl('*/*/country',array('_nosid'=>true))."';
											    $.ajax({
													url: rurl,
													type: 'POST',
													data: {cid:country_id_val},
													dataType: 'html',
													success: function(stateform) {
											    		stateform =  JSON.parse(stateform);
														 if(stateform=='true'){
											          		document.getElementById('region').parentNode.parentNode.style.display='none';
											          		document.getElementById('region_id').parentNode.parentNode.style.display='block';
											        	   }else{
											          		 document.getElementById('region_id').parentNode.parentNode.style.display='none'; 
											          		 document.getElementById('region').parentNode.parentNode.style.display='block'; 
											         		}
													}
											    });
										   	};
									   }
									   	  
								   });
								</script>";
			return $this->getForm()->getHtml().$html;
		}
		return '';
	}
}
