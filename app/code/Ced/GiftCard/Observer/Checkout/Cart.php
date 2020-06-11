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
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Cart
 * @package Ced\GiftCard\Observer\Checkout
 */
class Cart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * @var \Ced\GiftCard\Helper\Image
     */
    protected $helperImage;


    /**
     * Cart constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Ced\GiftCard\Helper\Image $helperImage
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Ced\GiftCard\Helper\Image $helperImage,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
       $this->request = $request;
       $this->helperImage = $helperImage;
       $this->serialize = $serialize;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $postValue = $this->request->getPostValue();

        $item = $observer->getEvent()->getData('quote_item');
        try{
            $product = $item->getProduct();

            if(!($product->getTypeId() == 'giftcard')) {
                return $this;
            }  
            if (isset($postValue['giftcard']) && $postValue['giftcard'] == 'giftcard'){
                $item = ( $item->getParentItem() ? $item->getParentItem() : $item );

                $giftData['gift_from_name'] = $postValue['gift_from_name'];
                $giftData['gift_to_name'] = $postValue['gift_to_name'];
                $giftData['gift_to_email'] = $postValue['gift_to_email'];
                $giftData['gift_message'] = $postValue['gift_message'];
                $giftData['gift_price'] = $postValue['gift_price'];
                $giftData['image'] = $postValue['image'];
                $giftData['image_label'] = $postValue['image_label'];
                $giftData['template_id'] = $postValue['template_id'];
                $giftData['deliverydate'] = $postValue['deliverydate'];

                /*set all data in quote item table for later use*/
                $ced_giftcarddata = $this->serialize->serialize($giftData);

                $item->setCedGiftcarddata($ced_giftcarddata);

                $item->setCustomPrice($postValue['gift_price']);
                $item->setOriginalCustomPrice($postValue['gift_price']);

                $item->getProduct()->setIsSuperMode(true);

                $val =  __('To').': '.$postValue['gift_to_name'].', '.__('Message').': '.$postValue['gift_message'];

                $additionalOptions = array(array(
                  'code'  => 'gift_card_detail',
                  'label'  => 'Gift Card Details',
                  'value' => $val
                ));

                $serializedOptions = $this->serialize->serialize($additionalOptions);

                $item->addOption(
                        new \Magento\Framework\DataObject(
                            [
                                'product' => $item->getProduct(),
                                'code' => 'additional_options',
                                'value' => $serializedOptions
                            ]
                        )
                    );
            }
        }catch(\Exception $e){
        }
    }

}