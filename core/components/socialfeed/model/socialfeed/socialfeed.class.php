<?php
/**
 * @package socialFeed
 */
class SocialFeed {

    public $modx;

    /**
     * SocialFeed constructor
     *
     * @param MODX A reference to the MODX instance.
     */
     public function __construct(modX &$modx, array $config = array())
     {
        // init modx
        $this->modx = & $modx;

        // config
        $this->api_key = $this->modx->getOption('socialfeed.api_key');
        $this->feed_key = $this->modx->getOption('socialfeed.feed_key');
        $this->feed_id = $this->modx->getOption('socialfeed.feed_id');
        $this->image_path = $this->modx->getOption('socialfeed.image_path');
        $this->published = $this->modx->getOption('socialfeed.published');

        // add addPackage
        $basePath = $this->modx->getOption('socialfeed.core_path',$config,$this->modx->getOption('core_path').'components/socialfeed/');
        $assetsUrl = $this->modx->getOption('signfy.assets_url',$config,$this->modx->getOption('assets_url').'components/socialfeed/');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl.'connector.php',
        ),$config);

        $this->modx->addPackage('socialfeed',$this->config['modelPath']);
    }


    // get items
    public function getItems($limit, $offset, $sortby, $sortdir, $filterUser = NULL, $filterContent = NULL, $filterChannelType = NULL, $cache = array())
    {
        // check if items in cache
        if (!empty($cache['cache'])) {
            $itemsCache = $this->getCache($cache['key']);
            if ($itemsCache != false) {
                return $itemsCache;
            }
        }

        // prepare query
        $where = array(
            'published' => 1,
            'deleted' => 0,
        );

        // filterUser
        if (!empty($filterUser)) {
            $where = array_merge($where, array(
                'username' => $filterUser,
            ));
        }

        // filterContent
        if (!empty($filterContent)) {
            $where = array_merge($where, array(
                'content:LIKE' => '%' . $filterContent . '%'
            ));
        }

        // filterChannelType
        if (!empty($filterChannelType)) {
            $where = array_merge($where, array(
                'channel_type:In' => explode(',', $filterChannelType)
            ));
        }

        // query
        $c = $this->modx->newQuery('SocialFeedItem');
        $c->where($where);
        $c->sortby($sortby, $sortdir);
        $c->limit($limit, $offset);
        $items_array = $this->modx->getCollection('SocialFeedItem', $c);

        $items = array();
        $i = 1;

        if (is_array($items_array)) {
            foreach ($items_array as $item) {
                $items[] = array(
                    'id' => $item->get('id'),
                    'idx' => $i++,
                    'key' => $item->get('key'),
                    'username' => $item->get('username'),
                    'channel' => $item->get('channel_type'),
                    'type' => $item->get('media_type'),
                    'image' => $item->get('image_url'),
                    'url' => $item->get('media_url'),
                    'permalink' => $item->get('permalink'),
                    'content' => $item->get('content'),
                    'published_date' => $item->get('published_date'),
                    'properties' => json_decode($item->get('properties'), true),
                    'published' => $item->get('published'),
                    'deleted' => $item->get('deleted'),
                );
            }

            // create cache
            if (!empty($cache['cache'])) {
                $this->addCache($items, $cache['key'], $cache['time']);
            }

            return $items;
        }

        return;
    }


    // import items
    public function import()
    {
        $imports = array();
        $items = array();

        $items = $this->getItemsFromFeed();

        foreach ($items as $item) {

            // check if item already exists
            if (!$post = $this->modx->getObject('SocialFeedItem', array('key' => $item->id))) {
                $post = $this->modx->newObject('SocialFeedItem');
                $post->set('published', $this->published);
            }

            // save image to server
            $image = $this->copyImage($item->image_url, $item->id, $item->username);
            if ($image === false) {
                continue;
            }

            // save to database
            $post->set('key', $item->id);
            $post->set('username', $item->username);
            $post->set('channel_type', $item->channel_type);
            $post->set('media_type', $item->media_type);
            $post->set('media_url', $item->media_url);
            $post->set('image_url', $image);
            $post->set('permalink', $item->permalink);
            $post->set('content', $this->removeEmoji($item->content));
            $post->set('published_date', $item->published_at);
            $post->set('properties', $item->properties);
            $post->save();

            $imports[] = $item;
        }

        // clear cache
        $this->modx->cacheManager->refresh(array(
            'socialfeed' => '',
        ));

        return $imports;
    }


    // get items from socialFeed
    private function getItemsFromFeed($url = NULL)
    {
        if (empty($url)) {
            $url = 'https://api.socialfeed.pro/api/feed/' . $this->feed_id;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                "api-key: " . $this->api_key,
                "feed-key: " . $this->feed_key,
                "feed-id: " . $this->feed_id
            ),
        ));

        $response = curl_exec($curl);

        // error handling
        if ($response === false) {
            $this->modx->log(1, '[socialFeed] Error CURL: ' . curl_error($curl));
        }

        curl_close($curl);

        $data = json_decode($response);
        $media = $data->data;

        return $media;
    }


    // copy image to server
    private function copyImage($image, $key, $user_name) {

        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $image_info   = getimagesize($image);
        $image_mime   = $image_info['mime'];

        switch ($image_mime) {
            default:
            case 'image/jpeg':
                $image_ext = 'jpg';
                break;
            case 'image/gif':
                $image_ext = 'gif';
                break;
            case 'image/png':
                $image_ext = 'png';
                break;
        }

        $image_name = $key . '.' . $image_ext;
        $image_dir_path = $this->image_path . $user_name . '/';
        $image_path = $image_dir_path . $image_name;
        $image_save_dir_path = MODX_ASSETS_PATH . $image_dir_path;
        $image_save_path = $image_save_dir_path . $image_name;

        if (!file_exists($image_save_dir_path)) {
            mkdir($image_save_dir_path, 0755, true);
        }

        if (!copy($image, $image_save_path)) {
            return false;
        }

        return MODX_ASSETS_URL . $image_path;
    }


    // get items from cache
    public function getCache($key)
    {
        $options = array(
            xPDO::OPT_CACHE_KEY => 'socialfeed',
        );

        return  $this->modx->cacheManager->get($key . '.socialfeed');
    }

    // add items to cache
    public function addCache($items, $key, $time)
    {
        $options = array(
            xPDO::OPT_CACHE_KEY => 'socialfeed',
        );

        $this->modx->cacheManager->delete($key . '.socialfeed', $options);
        $this->modx->cacheManager->set($key . '.socialfeed', $items, $time, $options);

    }


    // Removes all emoji from content.
    public static function removeEmoji($text)
    {
        $text = iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return iconv('ISO-8859-15', 'UTF-8', $text);
   }

}
