<?php 

if (!defined('WP_WIZIAPP_BASE')) 
    exit();

/**
* Main database class for all the plugins needs
* 
* The database class is more of a helper when it comes to preforming queries on the data
* we save inside the CMS database. It doesn't handle the CMS queries, but it handles all
* of our custom tables queries. 
* 
* @package WiziappWordpressPlugin
* @subpackage Database
* @author comobix.com plugins@comobix.com
*/
// As long as we are supporting php < 5.3 we shouldn't extent the singleton class
//class WiziappDB extends WiziappSingleton implements WiziappIInstallable{
class WiziappDB implements WiziappIInstallable{
    /**
    * The possible types of the content
    * 
    * @var mixed
    */
    var $types = array('post' => 1, 'page' => 2, 'comment' => 3);
    
    /**
    * The possible types of the media we save
    * 
    * @var mixed
    */
    var $media_types = array('image' => 1, 'video' => 2, 'audio' => 3);

    private $media_table = 'wiziapp_content_media';

    private $internal_version = '0.1';

    private static $_instance = null;

    public static function getInstance() {
        if( is_null(self::$_instance) ) {
            self::$_instance = new WiziappDB();
        }

        return self::$_instance;
    }
    
    private function  __clone() {
        // Prevent cloning
    }

    private function __construct() {
        global $wpdb;

        $this->media_table = "{$wpdb->prefix}{$this->media_table}";
    }
    
    /**
    * A simple wrapper to the find_content_media method, 
    * its here to make the retrieval a bit easier
    * 
    * @see WiziappDB::find_content_media()
    * @param integer $id the id of the page
    * @param string $media_type the media type to retrieve
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function find_page_media($id, $media_type){
        return $this->find_content_media($id, $this->types['page'], $this->media_types[$media_type]);
    }
    
    /**
    * A simple wrapper to the find_content_media method, 
    * its here to make the retrieval a bit easier
    * 
    * @see WiziappDB::find_content_media()
    * @param integer $id the id of the post
    * @param string $media_type the media type to retrieve
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function find_post_media($id, $media_type){
        return $this->find_content_media($id, $this->types['post'], $this->media_types[$media_type]);
    }
    
    /**
    * A simple wrapper to the update_content_media method, 
    * its here to make the updating a bit easier
    * 
    * @param integer $post_id the id of the post the media was found in
    * @param string $media_type the media type
    * @param array $data the data gathered on the media
    * @param string $html the html representing the original html element
    * @returns mixed $result the id of the media if saved, false if there was a problem
    */
    function update_post_media($post_id, $media_type, $data, $html){
        return $this->update_content_media($post_id, $this->types["post"], 
                        $this->media_types[$media_type], $data, $html);
    }
    
    /**
    * A simple wrapper to the update_content_media method, 
    * its here to make the updating a bit easier
    * 
    * @param integer $page_id the id of the page the media was found in
    * @param string $media_type the media type
    * @param array $data the data gathered on the media
    * @param string $html the html representing the original html element
    * @returns mixed $result the id of the media if saved, false if there was a problem
    */
    function update_page_media($page_id, $media_type, $data, $html){
        return $this->update_content_media($page_id, $this->types["page"], 
                        $this->media_types[$media_type], $data, $html);
    }
    
    /**
    * look for media from a certain type related to a certain post or page
    * 
    * @param integer $id the id of the content the media was found in
    * @param string $type the the type of the content
    * @param string $media_type the type of the media
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function find_content_media($id, $type, $media_type) {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.content_id = %d AND c.content_type = %d AND c.attachment_type = %d", $id, $type, $media_type);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.find_content_media');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            return $result;
        } 
                                         
        return false;
    }

    function find_media($id, $media_type) {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.content_id = %d AND c.attachment_type = %d", $id,  $this->media_types[$media_type]);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.find_content_media');
        $result = $wpdb->get_results($sql, ARRAY_A);

        if ($result) {
            return $result;
        }

        return false;
    }

    function get_media_data($media_type = 'image', $key, $value){
        $media_type_id = $this->media_types[$media_type];

        global $wpdb;

        $where = "WHERE c.attachment_type = %d and c.attachment_info like %s";
        $equalKey = '%"' . $key . '":"' . $value . '"%';

        $unsafeSQL = "SELECT c.id, c.attachment_info, c.content_id FROM {$this->media_table} AS c {$where}";

        $sql = $wpdb->prepare($unsafeSQL, $media_type_id, $equalKey);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_media_data');
        $result = $wpdb->get_results($sql, ARRAY_A);

        if ($result) {
            $metadata = array();
            foreach($result as $media){
                $info = json_decode($media['attachment_info'], TRUE);
                $metadata[$media['id']] = array();
                $keys = array_keys($info['metadata']);
                for($k = 0, $total = count($keys); $k < $total; ++$k){
                    if (isset($info['metadata'][$keys[$k]])){
                        $metadata[$media['id']][$keys[$k]] = $info['metadata'][$keys[$k]];
                    }
                }
            }
            $metadata[$media['id']]['content_id'] = $media['content_id'];
            return $metadata;
        }

        return FALSE;
    }

    function get_images_for_albums(){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.attachment_type = %d AND (attachment_info LIKE %s OR attachment_info LIKE %s OR attachment_info LIKE %s OR attachment_info LIKE %s OR attachment_info LIKE %s OR attachment_info LIKE %s)", $this->media_types["image"], '%data-wiziapp-cincopa-id%', '%data-wiziapp-nextgen-album-id%', '%data-wiziapp-nextgen-gallery-id%', '%data-wiziapp-pageflipbook-id%', '%wordpress-gallery-id%', '%data-wiziapp-id%');
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_images_for_albums');

        $result = $wpdb->get_results($sql, ARRAY_A);

        if ($result) {
            // If we have some results organize them in an easy to handle way
            $data = array();
            foreach($result as $media){
                $info = json_decode($media['attachment_info']);
                $data[$media['content_id']][] = array(
                    'media_id'=>$media['id'],
                    'original_code'=>$media['original_code'],
                    'info'=>$info
                );
            }
            return $data;
        }
        return FALSE;
    }

    function get_media_metadata_equal($media_type = 'image', $key, $value){
        $media_type_id = $this->media_types[$media_type];
        
        global $wpdb;

        $where = "WHERE c.attachment_type = %d and c.attachment_info like %s and c.attachment_info like %s";
        $equalKey = '%"' . $key . '":"' . $value . '"%';
        
        $unsafeSQL = "SELECT c.id, c.attachment_info, c.content_id FROM {$this->media_table} AS c {$where}";
        
        $sql = $wpdb->prepare($unsafeSQL, $media_type_id, '%metadata%', $equalKey);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_media_metadata_equal');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            $metadata = array();
            foreach($result as $media){
                $info = json_decode($media['attachment_info'], TRUE);
                $metadata[$media['id']] = array();
                $keys = array_keys($info['metadata']);
                for($k = 0, $total = count($keys); $k < $total; ++$k){
                    if (isset($info['metadata'][$keys[$k]])){
                        $metadata[$media['id']][$keys[$k]] = $info['metadata'][$keys[$k]];
                    }
                }
            }
            $metadata[$media['id']]['content_id'] = $media['content_id'];
            return $metadata;
        } 
                                         
        return FALSE;           
        
    }
    function get_media_metadata_not_equal($media_type='image', $keys=array()){
        $media_type_id = $this->media_types[$media_type];
        
        global $wpdb;

        $where = "WHERE c.attachment_type = %d ";
        $equalKey= array($media_type_id);
        if(is_array($keys)){
            foreach($keys as $key=>$value){
                $where .= ' AND c.attachment_info NOT LIKE %s ';
                $equalKey[] = '%"' . $key . '":' . ($value?'"' . $value . '"':'') . '%';
            }
        }
        $unsafeSQL = "SELECT c.id, c.original_code, c.attachment_info, c.content_id FROM {$this->media_table} AS c {$where}";
        
        $sql = $wpdb->prepare($unsafeSQL, $equalKey);

        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_media_metadata_not_equal');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            $data = array();
            foreach($result as $media){
                $info = json_decode($media['attachment_info']);
                $data[$media['content_id']][] = array('media_id'=>$media['id'], 'original_code'=>$media['original_code'], 'info'=>$info);
            }
            return $data;
        } 
        return FALSE;           
    }
    
    /**
    * Get the media metadata for external plugins.
    * Plugins can save the data as a special data-wiziapp-param attribute
    * on the media html and this function will extract the metadata they are requesting
    * to use you must request for the same param you saved as an array and the function will return
    * an array or [media_id] => metadata...
    * 
    * @param string $media_type can be image/video/audio
    * @param array $keys a list of keys to extract from the metadata
    * 
    * @return array $metadata the metadata array is build from an associative array of media_id->metadata for the media
    */
    function get_media_metadata($media_type = 'image', $keys = array(), $operand = 'and'){
        $media_type_id = $this->media_types[$media_type];
        
        global $wpdb;

        $where = "WHERE c.attachment_type = %d and c.attachment_info like %s";
        
        $metaParams = array();
        if (!empty($keys)){
            for($k = 0, $total = count($keys); $k < $total; ++$k){
                $where .= " " . $operand . " c.attachment_info like %s";
                $metaParams[] = '%' . $keys[$k] . '%';
            }
        }
        $unsafeSQL = "SELECT c.id, c.attachment_info, c.content_id FROM {$this->media_table} AS c {$where}";
        
        $params = array_merge(array($unsafeSQL, $media_type_id, '%metadata%'), $metaParams);
        
        // Run $wpdb->prepare to make the query safe
        $sql = call_user_func_array(array($wpdb, 'prepare'), $params);
        
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.find_content_media');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            $metadata = array();
            foreach($result as $media){
                $info = json_decode($media['attachment_info'], TRUE);
                $metadata[$media['id']] = array();
                for($k = 0, $total = count($keys); $k < $total; ++$k){
                    if (isset($info['metadata'][$keys[$k]])){
                        $metadata[$media['id']][$keys[$k]] = $info['metadata'][$keys[$k]];
                    }
                }
            }
            return $metadata;
        }
        return FALSE;           
    }
    
    /**
    * Updates the content media in the database. Sicne the html might change we have no way to
    * validate if the record exists or not, therefore the records for the content must be deleted 
    * before being sent to this method. this method only adds to the database.
    * 
    * @todo Add the ability to update multiple records on the same time
    * 
    * @param integer $content_id the id of the content the media was found in
    * @param string $type the type of the content
    * @param string $media_type the media type
    * @param array $data the data we collected on the media
    * @param string $html the original html code that resulted in this media 
    * 
    * @returns mixed $result the id of the media if saved, false if there was a problem
    */
    function update_content_media($content_id, $type, $media_type, $data, $html){
        //$media = $this->find_content_media($content_id, $type, $media_type);
        $result = FALSE;
        //if ( $media ){
            // Just update
          //  $id = $media->id;     
            //$result = $this->do_update_content_media($id, $data);
        //} else {
            // Create
            $result = $this->add_content_media($content_id, $type, $media_type, $data, $html);
        //}
        return $result;
    }
    
    function add_content_medias($media_type, $items, $content_id, $content_type) {
        global $wpdb;
        //$wpdb->show_errors();

        $sql = "INSERT INTO {$this->media_table} (content_id, content_type, original_code, attachment_info, attachment_type, created_at, updated_at) VALUES ";
        
//        $sql .= '(%d, %d, %s, %s, %d, %s, %s),';
        $sql = substr_replace($sql, "", -1);
        
        for($a = 0, $total = count($items); $a < $total; ++$a){
            $obj = $items[$a]['obj'];
            $html = $items[$a]['html'];
            $sql .= "(" . $content_id . "," . $this->types[$content_type] . ",'" . $html . "','" . json_encode($obj) . "'," . 
                    $this->media_types[$media_type] . ",'" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "'),";
        }
        $sql = substr_replace($sql, "", -1);
        
        $query = $wpdb->prepare($sql);    
        
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.add_content_medias');
        
        $added = $wpdb->query($query);

        if ($added === FALSE) {
            return false;
        }
        
        $id = (int) $wpdb->insert_id;
        return $id;
    }
    
    /**
    * Preforms the media saving in the database. Adds a new record to the media
    * table according to the received information
    * 
    * @param integer $content_id the id of the content the media was found in
    * @param string $type the type of the content
    * @param string $media_type the media type
    * @param array $media_info the data we collected on the media
    * @param string $html the original html code that resulted in this media 
    */
    function add_content_media($content_id, $type, $media_type, $media_info, $html){
        global $wpdb;
        //$wpdb->show_errors();

        $sql = $wpdb->prepare(  "INSERT INTO {$this->media_table} (content_id, content_type, original_code, attachment_info, attachment_type, created_at, updated_at)
                                VALUES (%d, %d, %s, %s, %d, %s, %s)", 
                                $content_id, $type, $html, json_encode($media_info), $media_type, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));

        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.add_content_media');                                
        $added = $wpdb->query($sql);
        
        if ($added === FALSE) {
            return false;
        }
        
        $id = (int) $wpdb->insert_id;
        return $id;
    }

    /**
    * deletes the media related to the specified content
    *         
    * @param integer $content_id the content id
    * @param string $content_type the content type
    */
    function delete_content_media($content_id, $content_type='post'){
          global $wpdb;         

          $sql = $wpdb->prepare("DELETE FROM {$this->media_table} WHERE content_id = %d AND content_type = %d", $content_id, $this->types[$content_type]);
          $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.delete_content_media');
          
          $wpdb->query($sql);
    }
    
    /**
    * gets all the videos in the blog regardless of their related content
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function get_all_videos($offset = 0, $limit = 0){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.attachment_type = %d LIMIT %d, %d", $this->media_types["video"], $offset, $limit);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_all_videos');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            // Build the object from the query result    
            return $result;
        } 
                                         
        return FALSE;
    }
    
    /**
    * gets all the images in the blog regardless of their related content
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function get_all_images(){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.attachment_type = %d", $this->media_types["image"]);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_all_images');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            // Build the object from the query result    
            return $result;
        } 
                                         
        return FALSE;
    }

    /**
    * Gets the total scanned videos count (Limited to 15)
    * 
    * @return int the total videos found while scanning the blog
    */
    function get_videos_count(){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT COUNT(id) FROM {$this->media_table} AS c WHERE c.attachment_type = %d LIMIT 0, 15",
                $this->media_types["video"]);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_videos_count');
                
        return (int) $wpdb->get_var($sql);
    }
    
    /**
    * Gets the total scanned albums (wordpress, cincopa, nextgen, pageflip) count (Limited to 15)
    * 
    * @return int the total albums found while scanning the blog
    */
    function get_albums_count(){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT COUNT(id) 
                                FROM {$this->media_table} AS c
                                WHERE attachment_info LIKE '%data-wiziapp-cincopa-id%' 
                                OR attachment_info LIKE '%data-wiziapp-nextgen-album-id%'
                                OR attachment_info LIKE '%data-wiziapp-nextgen-gallery-id%'
                                OR attachment_info LIKE '%data-wiziapp-pageflipbook-id%'
                                OR attachment_info LIKE '%wordpress-gallery-id%'
                                OR attachment_info LIKE '%data-wiziapp-id%'
                                LIMIT 0, 15");
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_albums_count');
                
        return (int) $wpdb->get_var($sql);
    }
    
    /**
    * Get the number of posts that has more then $image_in_albums images inside of them
    * used as part of the CMS profile. (Limited to 15)
    * 
    * @param int $image_in_album
    * @return int the number of posts
    */
    function get_images_post_albums_count($image_in_album){
        global $wpdb;
        
        $sql = $wpdb->prepare("SELECT COUNT(id) from
                                (SELECT COUNT(id) AS total, content_id 
                                FROM {$this->media_table} w
                                WHERE attachment_type = %d group by content_id) as totals 
                                WHERE total > %d
                                LIMIT 0, 15", $this->media_types["image"], $image_in_album);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_images_post_albums_count');
        
        return (int) $wpdb->get_var($sql);                
    }
    
    /**
    * Get the number of posts that has more then $audio_in_album audios inside of them
    * used as part of the CMS profile. (Limited to 15)
    * 
    * @param int $audio_in_album
    * @return int the number of posts
    */
    function get_audios_post_albums_count($audio_in_album){
        global $wpdb;
        
        $sql = $wpdb->prepare("SELECT COUNT(id) from
                                (SELECT COUNT(id) AS total, content_id 
                                FROM {$this->media_table} w
                                WHERE attachment_type = %d group by content_id) as totals 
                                WHERE total > %d
                                LIMIT 0, 15", $this->media_types["audio"], $audio_in_album);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_audios_post_albums_count');
        
        return (int) $wpdb->get_var($sql);                
    }
    
    /**
    * gets all the audio in the blog regardless of their related content
    * 
    * @returns mixed $result an array containing the results of the search or false if none
    */
    function get_all_audios($offset = 0, $limit = 0){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.attachment_type = %d LIMIT %d, %d", $this->media_types["audio"], $offset, $limit);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_all_audios');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            // Build the object from the query result    
            return $result;
        } 
                                         
        return FALSE;
    }
    
    /**
    * get a specific video by it's id
    * 
    * @param integer $id the video id
    * @returns mixed $result an array containing the record of the search as an associative array or false if none
    */
    function get_videos_by_id($id){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$this->media_table} AS c WHERE c.attachment_type = %d AND id = %d", $this->media_types["video"], $id);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_videos_by_id');
        $result = $wpdb->get_row($sql, ARRAY_A);
        
        if ($result) {
            // Build the object from the query result    
            return $result;
        } 
                                         
        return FALSE;
    }

    /**
    * gets all the videos and audio found in the specified content id
    * 
    * @param integer $content_id the content id
    * @returns mixed $result an array (fields: id, original_code, attachment_info) containing the results of the search or false if none
    * @todo add paging support to the get_content_special_elements() method  (offset & limit)
    */
    function get_content_special_elements($content_id){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id, original_code, attachment_info, attachment_type FROM {$this->media_table} AS c WHERE c.attachment_type in (%d, %d) AND content_id = %d", $this->media_types['audio'], $this->media_types['video'], $content_id);
        
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_content_special_elements');                                                                
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            return $result;
        } 
                                         
        return FALSE;
    }
    
    /**
    * Gets all the images found in the specified content id
    * 
    * @param integer $content_id the content id
    * @returns mixed $result an array (fields: id, original_code, attachment_info) containing the results of the search or false if none
    * @todo add paging support to the get_content_images() method  (offset & limit)
    */
    function get_content_images($content_id){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id, original_code, attachment_type, attachment_info FROM {$this->media_table} AS c
                                WHERE c.attachment_type = %d AND content_id = %d", $this->media_types['image'], $content_id);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_content_images');
        $result = $wpdb->get_results($sql, ARRAY_A);
        
        if ($result) {
            return $result;
        } 
                                         
        return FALSE;
    }
    
    function get_content_by_media_id($id){
        global $wpdb;
        $sql = $wpdb->prepare("SELECT `content_id` FROM {$this->media_table} AS c WHERE c.id=%d", $id);
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_content_by_media_id');
        $result = $wpdb->get_var($sql);
        return $result;
    }

    // @todo replace this query with a "native" wordpress one
    function get_links_count(){
        global $wpdb;
        $tableName = $wpdb->links;
        $sql = $wpdb->prepare("SELECT COUNT(link_id) FROM {$tableName}");
        $GLOBALS['WiziappLog']->write('info', "About to run the sql: {$sql}", 'db.get_links_count');
        $result = $wpdb->get_var($sql);
        return $result;
    }

    public function isInstalled(){
        global $wpdb;

        return ($wpdb->get_var("show tables like '{$this->media_table}'") == $this->media_table);
    }

    /**
     * IMPORTANT!!!!!!!
     * If you change the sql in this method, or add new ones... make sure to update
     * $this->internal_version. This method will automatically run only once and when the
     * internal_version is changed.
     *
     * @return bool
     */
    public function install(){
        global $wpdb;

        // Use wordpress dbDelta functionality
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
         // Handle charset
        $charset_collate = '';
        if (version_compare(mysql_get_server_info(), '4.1.0', '>=')) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        // NOTE: Before changing the sql here read the function doc block...
        $sql = "CREATE TABLE {$this->media_table} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT ,
            content_id BIGINT(20) NOT NULL ,
            content_type INT(4) NOT NULL ,
            original_code MEDIUMTEXT NOT NULL ,
            attachment_info MEDIUMTEXT NOT NULL,
            attachment_type INT(4) NOT NULL,
            created_at DATETIME NULL ,
            updated_at DATETIME NULL ,
            PRIMARY KEY id (id),
            KEY content_id (content_id)
            ) {$charset_collate} ENGINE=INNODB;";

        // Note: dbDelta adds fields nicely but doesn't seem to remove them...
        dbDelta($sql);

        // save the database version for easy upgrades
        update_option("wiziapp_db_version", $this->internal_version);
        
        return $this->isInstalled();
    }

    public function uninstall(){
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$this->media_table}");

        // remove the flags on the posts metadata and on the users
        $wpdb->query("delete from {$wpdb->postmeta} where meta_key = 'wiziapp_processed'");

        delete_option('wiziapp_db_version');
    }

    public function needUpgrade(){
        $installedVer = get_option("wiziapp_db_version");
        return ( $installedVer != $this->internal_version );
    }

    public function upgrade(){
        /**
         * If there are any special upgrade between the versions
         * this is the place to call them, we can do this
         * by adding methods like upgradeFrom0_1 and checking if the method exists
         *
         * For now a simple diff that is handled by the install anyway is fine.
         */
        return $this->install();
    }
}

/**
* One database to talk with them all...
* @todo reconsider the global things...
* @global WiziappDB $GLOBALS['WiziappDB']
*/
if (!isset($GLOBALS['WiziappDB'])) {
    /**
     * Initiate the Wiziapp Database Object, for later cache reasons
     */
    unset($GLOBALS['WiziappDB']);
    $GLOBALS['WiziappDB'] = WiziappDB::getInstance();
}    