<?php
/**
 * socialFeed cron
 *
 * @package socialfeed
 *
 * @var modX $modx
 */

 // For access without authorization
define('MODX_REQP', false);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('socialfeed.core_path', null, $modx->getOption('core_path') . 'components/socialfeed/');
$socialFeed = $modx->getService('socialfeed', 'SocialFeed', $corePath . 'model/socialfeed/', array(
    'core_path' => $corePath
));

$result = $socialFeed->import();

//echo '<pre>';
//die(print_r($result));

return $result;
