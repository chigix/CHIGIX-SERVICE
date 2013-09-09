<?php

/**
 * 别名定义
 * @author Richard Lea <chigix@zoho.com>
 */
defined('CHIGI_PATH') or define('CHIGI_PATH', dirname(__FILE__) . '/');

function chigi_alias($configarr = array()) {
    $orig = array(
        'ChigiAction' => CHIGI_PATH . 'ChigiAction.class.php',
        'ChigiApi' => CHIGI_PATH . 'ChigiApi.class.php',
        'ChigiService' => CHIGI_PATH . 'ChigiService.class.php',
        'ChigiCouple' => CHIGI_PATH . 'ChigiCouple.class.php',
        'ChigiReturn' => CHIGI_PATH . 'ChigiReturn.class.php',
        'ChigiAlert' => CHIGI_PATH . 'ChigiAlert.class.php',
        'ChigiRole' => CHIGI_PATH . 'ChigiRole.class.php',
        'ChigiForm' => CHIGI_PATH . 'ChigiForm.class.php'
    );
    if (APP_DEBUG) {
        $orig['ThinkTemplate'] = CHIGI_PATH . 'ChigiTemplate.class.php';
        $orig['Chiji'] = CHIGI_PATH . 'Chiji.class.php';
    }
    return array_merge($orig, $configarr);
}

?>