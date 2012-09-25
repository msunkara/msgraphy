<?php

class WiziappCms{
    public function activate(){
        $updatedApi = FALSE;
        $profile = $this->generateProfile();

        $blogUrl = get_bloginfo('url');
        $urlData = explode('://', $blogUrl);

        // Inform the admin server
        $response = wiziapp_http_request($profile, '/cms/activate/' . $urlData[0] . '?url=' . urlencode($urlData[1]), 'POST');
        $GLOBALS['WiziappLog']->write('info', "The response is " . print_r($response, TRUE), "cms.activate");

        if (!is_wp_error($response)) {
            $tokenResponse = json_decode($response['body'], TRUE);

            if (!empty($tokenResponse) && $tokenResponse['header']['status']){
                WiziappConfig::getInstance()->startBulkUpdate();

                WiziappConfig::getInstance()->app_token = $tokenResponse['plugin_token'];
                WiziappConfig::getInstance()->app_id = $tokenResponse['app_id'];
                WiziappConfig::getInstance()->main_tab_index = $tokenResponse['main_tab_index'];
                WiziappConfig::getInstance()->settings_done = $tokenResponse['settings_done'];
                WiziappConfig::getInstance()->app_live = $tokenResponse['app_live'];
                if (isset($tokenResponse['app_description'])){
                    WiziappConfig::getInstance()->app_description = $tokenResponse['app_description'];
                }

                if (isset($tokenResponse['appstore_url'])){
                    WiziappConfig::getInstance()->appstore_url = $tokenResponse['appstore_url'];
                }

                if (isset($tokenResponse['app_icon'])){
                    WiziappConfig::getInstance()->app_icon = $tokenResponse['app_icon'];
                }

                if (isset($tokenResponse['app_name'])){
                    WiziappConfig::getInstance()->app_name = $tokenResponse['app_name'];
                }

                if (isset($tokenResponse['email_verified'])){
                    WiziappConfig::getInstance()->email_verified = $tokenResponse['email_verified'];
                }

                $screensJson = stripslashes($tokenResponse['screens']);
                $screens = json_decode($screensJson, TRUE);

                $components = array();
                if ( isset($tokenResponse['components']) ){
                    $componentsJson = stripslashes($tokenResponse['components']);
                    $components = json_decode($componentsJson, TRUE);
                }

                $thumbs = json_decode($tokenResponse['thumbs'], TRUE);

                WiziappConfig::getInstance()->full_image_height = $thumbs['full_image_height'];
                WiziappConfig::getInstance()->full_image_width = $thumbs['full_image_width'];

                WiziappConfig::getInstance()->images_thumb_height = $thumbs['images_thumb_height'];
                WiziappConfig::getInstance()->images_thumb_width = $thumbs['images_thumb_width'];

                WiziappConfig::getInstance()->posts_thumb_height = $thumbs['posts_thumb_height'];
                WiziappConfig::getInstance()->posts_thumb_width = $thumbs['posts_thumb_width'];

                WiziappConfig::getInstance()->featured_post_thumb_height = $thumbs['featured_post_thumb_height'];
                WiziappConfig::getInstance()->featured_post_thumb_width = $thumbs['featured_post_thumb_width'];

                WiziappConfig::getInstance()->mini_post_thumb_height = $thumbs['mini_post_thumb_height'];
                WiziappConfig::getInstance()->mini_post_thumb_width = $thumbs['mini_post_thumb_width'];

                WiziappConfig::getInstance()->comments_avatar_height = $thumbs['comments_avatar_height'];
                WiziappConfig::getInstance()->comments_avatar_width = $thumbs['comments_avatar_width'];

                WiziappConfig::getInstance()->album_thumb_width = $thumbs['album_thumb_width'];
                WiziappConfig::getInstance()->album_thumb_height = $thumbs['album_thumb_height'];

                WiziappConfig::getInstance()->video_album_thumb_width = $thumbs['video_album_thumb_width'];
                WiziappConfig::getInstance()->video_album_thumb_height = $thumbs['video_album_thumb_height'];

                WiziappConfig::getInstance()->audio_thumb_width = $thumbs['audio_thumb_width'];
                WiziappConfig::getInstance()->audio_thumb_height = $thumbs['audio_thumb_height'];

                /**
                 * If the app is configured update the titles
                 */
                if ( !empty($tokenResponse['screen_titles'] ) ){
                    WiziappConfig::getInstance()->categories_title = $tokenResponse['screen_titles']['categories_title'];
                    WiziappConfig::getInstance()->tags_title = $tokenResponse['screen_titles']['tags_title'];
                    WiziappConfig::getInstance()->albums_title = $tokenResponse['screen_titles']['albums_title'];
                    WiziappConfig::getInstance()->videos_title = $tokenResponse['screen_titles']['videos_title'];
                    WiziappConfig::getInstance()->audio_title = $tokenResponse['screen_titles']['audio_title'];
                    WiziappConfig::getInstance()->links_title = $tokenResponse['screen_titles']['links_title'];
                    WiziappConfig::getInstance()->pages_title = $tokenResponse['screen_titles']['pages_title'];
                    WiziappConfig::getInstance()->favorites_title = $tokenResponse['screen_titles']['favorites_title'];
                    WiziappConfig::getInstance()->about_title = $tokenResponse['screen_titles']['about_title'];
                    WiziappConfig::getInstance()->search_title = $tokenResponse['screen_titles']['search_title'];
                    WiziappConfig::getInstance()->archive_title = $tokenResponse['screen_titles']['archive_title'];
                }

                $pagesJson = stripslashes($tokenResponse['pages']);
                $pages = json_decode($pagesJson, TRUE);

                update_option('wiziapp_pages', $pages, '', 'no');
                update_option('wiziapp_screens', $screens, '', 'no');
                update_option('wiziapp_components', $components, '', 'no');

                WiziappConfig::getInstance()->bulkSave();

                $updatedApi = TRUE;
            }
        }

        return $updatedApi;
    }

    public function deactivate(){
        // Inform the system control
        $blogUrl = get_bloginfo('url');
        $urlData = explode('://', $blogUrl);

        $response = wiziapp_http_request(array(), '/cms/deactivate?app_id=' . WiziappConfig::getInstance()->app_id . '&url=' . urlencode($urlData[1]), 'POST');

        $this->deleteUser();
    }

    protected function deleteUser(){
        $userName = 'wiziapp';
        $userId = username_exists($userName);
        if (!$userId) {
            $GLOBALS['WiziappLog']->write('error', "User " . $userName . " was not found, and therefore couldnt be deleted.", "install.delete_user_wiziapp");
        } else {
            $userId = wp_delete_user($userId);
            if (!$userId) {
                $GLOBALS['WiziappLog']->write('error', "Error deleting user " . $userName, "install.delete_user_wiziapp");
            } else {
                $GLOBALS['WiziappLog']->write('info', "User " . $userName . ' deleted successfuly.', "install.delete_user_wiziapp");
            }
        }
    }

    // If the blog allows to create users, we register our user to be able to give to apple for appstore approval
    protected function registerUser() {
        $userData = array();
        $blogAllowRegistration = get_option('users_can_register') ? TRUE : FALSE;
        $userName = 'wiziapp';
        $password = 'ERROR';

        if ($blogAllowRegistration) {
            $blogName = get_bloginfo('name');
            $userId = username_exists($userName);

            $password = substr(str_replace(" " , "", $blogName), 0, 5) . '1324'; // wp_generate_password(12, false);
            if (!$userId) {
                $userId = wp_create_user($userName, $password); //wp_create_user($userName, $password, $user_email)
                if (!$userId) {
                    $GLOBALS['WiziappLog']->write('error', "Error creating user " . $userName, "install.register_user_wiziapp");
                } else {
                    $GLOBALS['WiziappLog']->write('info', "User " . $userName . " created successfuly.", "install.register_user_wiziapp");
                }
            } else {
                // Might be our user... should see if we can login with our password
                $user = wp_authenticate($userName, $password);
                if ( is_wp_error($user) ){
                    $password = 'ERROR';
                    $GLOBALS['WiziappLog']->write('error', "User " . $userName . " already exists and was NOT created.", "install.register_user_wiziapp");
                } 
            }
        }

        $userData['blog_allows_registration'] = $blogAllowRegistration;
        $userData['blog_username'] = $userName;
        $userData['blog_password'] = $password;

        return $userData;
    }

    protected function generateProfile(){
        $version = get_bloginfo('version');
        /**
        * @todo check if wp_touch is installed and try to get it's configuration
        * for blog name and description
        */
        $profile = array(
            'cms' => 'wordpress',
            'cms_version' => (float) substr($version, 0, strrpos($version, '.')),
            'name' => get_bloginfo('name'),
            'tag_line' => get_bloginfo('description'),
            'profile_data' => json_encode(array(
                'plugins' => $this->getActivePlugins(),
                'pages' => $this->getPagesList(),
                'stats' => $this->getCMSProfileStats(),
            )),
            'comment_registration' => get_option('comment_registration') ? 1 : 0,
        );

        $profile = array_merge($profile, $this->registerUser());

        return $profile;
    }

    /**
    * Gets the list of the active plugins installed in the blogs
    *
    * @return array|bool $listActivePlugins or false on error
     */
    protected function getActivePlugins() {
        /**
         * Uses the wordpress function - get_plugins($plugin_folder = [null])
         * to retrieve all plugins installed in the defined directory
         * filters out the none active plugins and then stores the name and version
         * of the active plugins in $listPlugins array and returns it.
         */
        $listActivePlugins = array();
        if ($folder_plugins = get_plugins()) {
            foreach($folder_plugins as $plugin_file => $data) {
                if(is_plugin_active($plugin_file)) {
                    $listActivePlugins[$data['Name']] = $data['Version'];
                }
            }
            return $listActivePlugins;
         }
         else return false;
    }

    /**
    * Gets the pages in the blog
    *
    * @return array $list of pages
    */
    protected  function getPagesList() {
        $pages = get_pages(array(
            'number' => 15,
        ));
        $list = array();

        foreach ($pages as $p) {
            $list[] = $p->post_title;
        }

        return $list;
    }

    /**
    * Gets the statistics for the CMS profile
    * @return array $stats
    */
    protected  function getCMSProfileStats() {
        $GLOBALS['WiziappLog']->write('info', "Getting the CMS profile", "wiziapp_getCMSProfileStats");

        $audiosAlbums = array();
        $playlists = array();

        ob_start();
    //    $imagesAlbums = apply_filters('wiziapp_images_albums_request', $imagesAlbums);
        $imagesAlbums = $GLOBALS['WiziappDB']->get_albums_count();
        $audioAlbums = apply_filters('wiziapp_audios_albums_request', $audiosAlbums);
        $playlists = apply_filters('wiziapp_playlists_request', $playlists);
        ob_end_clean();

        $numOfCategories = count(get_categories(array(
            'number' => 15,
        )));

        $numOfTags = count(get_tags(array(
            'number' => 15,
        )));
        $postImagesAlbums = $GLOBALS['WiziappDB']->get_images_post_albums_count(5);
        $videosCount = $GLOBALS['WiziappDB']->get_videos_count();
        $postAudiosAlbums = $GLOBALS['WiziappDB']->get_audios_post_albums_count(2);
        $linksCount = count(get_bookmarks(array(
            'limit' => 15,
        )));

        $stats = array(
            'numOfCategories' => $numOfCategories,
            'numOfTags' => $numOfTags,
            'postImagesAlbums' => $postImagesAlbums,
            'pluginImagesAlbums' => count($imagesAlbums),
            'videosCount' => $videosCount,
            'postAudiosAlbums' => $postAudiosAlbums,
            'pluginAudioAlbums' => count($audioAlbums),
            'pluginPlaylists' => count($playlists),
            'linksCount' => $linksCount,
        );
        $GLOBALS['WiziappLog']->write('info', "About to return the CMS profile: " . print_r($stats, TRUE), "wiziapp_getCMSProfileStats");

        return $stats;
    }
}