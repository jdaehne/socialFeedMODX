<?php
/*
 * socialFeed
 *
 * Snippet to show posts
 *
 * Usage examples:
 * [[socialFeed? &tpl=`yourTpl`]]
 *
 * @author Jan Dähne <jan.daehne@quadro-system.de>
 */

$corePath = $modx->getOption('socialfeed.core_path', null, $modx->getOption('core_path') . 'components/socialfeed/');
$socialfeed = $modx->getService('socialfeed', 'SocialFeed', $corePath . 'model/socialfeed/', array(
    'core_path' => $corePath
));

// properties
$tpl = $modx->getOption('tpl', $scriptProperties, 'socialFeedTpl', true);
$limit = $modx->getOption('limit', $scriptProperties, 12, true);
$offset = $modx->getOption('offset', $scriptProperties, 0, true);
$sortby = $modx->getOption('sortby', $scriptProperties, 'published_date', true);
$sortdir = $modx->getOption('sortdir', $scriptProperties, 'desc', true);
$filterUser = $modx->getOption('filterUser', $scriptProperties);
$filterContent = $modx->getOption('filterContent', $scriptProperties);
$filterChannelType = $modx->getOption('filterChannelType', $scriptProperties);
$cache = $modx->getOption('cache', $scriptProperties, true, true);
$cacheTime = $modx->getOption('cacheTime', 3600, true);
$cacheKey = $modx->getOption('cacheKey', $scriptProperties, 'socialFeed', true);


// get items
$items = $socialfeed->getItems($limit, $offset, $sortby, $sortdir, $filterUser, $filterContent, $filterChannelType, array(
    'cache' => $cache,
    'time' => $cacheTime,
    'key' => $cacheKey,
));

$output = '';

if (is_array($items)) {
    foreach ($items as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }
}


return $output;