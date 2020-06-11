<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */


use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'MGS_StoreLocator', __DIR__);


if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Block/Locator/Grid/Options/License/License.php')) {
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Block/Locator/Grid/Options/License/License.php');
}
