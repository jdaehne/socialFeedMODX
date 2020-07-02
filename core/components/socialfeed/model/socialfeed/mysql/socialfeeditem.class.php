<?php
/**
 * @package socialfeed
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/socialfeeditem.class.php');
class SocialFeedItem_mysql extends SocialFeedItem {}
?>