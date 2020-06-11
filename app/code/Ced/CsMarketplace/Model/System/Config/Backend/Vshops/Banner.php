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
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Backend\Vshops;

/**
 * Class Banner
 * @package Ced\CsMarketplace\Model\System\Config\Backend\Vshops
 */
class Banner extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }

    /**
     * Save uploaded file before saving config value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $filevalue = $this->getValue();
        $tmpName = $this->_requestData->getTmpName($this->getPath());
        $file = [];
        if ($tmpName) {
            $file['tmp_name'] = $tmpName;
            $file['name'] = $this->_requestData->getName($this->getPath());
        } elseif (!empty($filevalue['tmp_name'])) {
            $file['tmp_name'] = $filevalue['tmp_name'];
            $file['name'] = $filevalue['name'];
        }

        if (isset($file['tmp_name'])) {

            /*
             * @note: to check if image is in correct ratio or not
             * */
            $imageData = getimagesize($file['tmp_name']);
            if (is_array($imageData) && count($imageData) > 0){
                $width = $imageData[0];
                $height = $imageData[1];
                $ratio = $width/$height;
                $correctImage = $width > $height;
                $minimage = $width >= 1000 && $height >= 300;
                $allowedRatio  = 1000/300;

                $validate  = ($ratio >= 3.16  && $ratio <= 3.5);

                if (!$correctImage || !$minimage || !$validate) {
                    $this->setValue('');
                    $msg = __("Minimum allowed banner dimension is 1000px X 300px and width to height ratio must be around 10:3.");
                    throw new \Magento\Framework\Exception\LocalizedException($msg);
                }
            }
            /*
             * @note: update image
             * */
            $uploadDir = $this->_getUploadDir();
            try {
                $csUploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $csUploader->setAllowedExtensions($this->_getAllowedExtensions());
                $csUploader->setAllowRenameFiles(true);
                $csUploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $csUploader->save($uploadDir);

            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            $csFileName = $result['file'];
            if ($csFileName) {
                if ($this->_addWhetherScopeInfo()) {
                    $csFileName = $this->_prependScopeInfo($csFileName);
                }
                $this->setValue($csFileName);
            }
        } else {
            if (is_array($filevalue) && !empty($filevalue['delete'])) {
                $this->setValue('');
            } else {
                $this->unsValue();
            }
        }
        return $this;
    }
}