<?php
function mysiteapp_is_wpcom_vip() {
    return function_exists('wpcom_vip_load_plugin');
}
function mysiteapp_get_template_root() {
    if (mysiteapp_is_wpcom_vip()) {
        return "vip/plugins/uppsite/themes";
    }
    return "../plugins/app-your-wordpress-uppsite/themes";
}
function uppsite_change_webapp($state) {
    $myOpts = get_option(MYSITEAPP_OPTIONS_DATA);
    if (!isset($myOpts['fixes'])) {
        $myOpts['fixes'] = array();
    }
        $v = get_option('bnc_iphone_pages');
    if (isset($v)) {
        $serialize = !is_array($v);
        if ($serialize) {
            $v = unserialize($v);
        }
        if ($state == true && !array_key_exists('wptouch-enable-regular-default', $myOpts['fixes'])) {
                        $val = isset($v['enable-regular-default']) ? $v['enable-regular-default'] : false;
            $myOpts['fixes']['wptouch-enable-regular-default'] = $val;
            $v['enable-regular-default'] = "normal";
        } elseif ($state == false && array_key_exists('wptouch-enable-regular-default', $myOpts['fixes'])) {
                        if ($myOpts['fixes']['wptouch-enable-regular-default'] == false) {
                unset($v['enable-regular-default']);
            } else {
                $v['enable-regular-default'] = $myOpts['fixes']['wptouch-enable-regular-default'];
            }
            unset($myOpts['fixes']['wptouch-enable-regular-default']);
        }
        if ($serialize) {
            $v = serialize($v);
        }
        update_option('bnc_iphone_pages', $v);
    }
    update_option(MYSITEAPP_OPTIONS_DATA, $myOpts);
}
function uppsite_options_updated($oldValues, $newValues) {
        uppsite_change_webapp(isset($newValues['webapp_mode']) && $newValues['webapp_mode'] != 'none');
    $dataOpts = get_option(MYSITEAPP_OPTIONS_DATA);
    if (isset($newValues['uppsite_key']) && isset($newValues['uppsite_secret'])) {
	    $dataOpts['uppsite_key'] = $newValues['uppsite_key'];
	    $dataOpts['uppsite_secret'] = $newValues['uppsite_secret'];
	    update_option(MYSITEAPP_OPTIONS_DATA, $dataOpts);
	}
}
function uppsite_options_added($optionName, $newValues) {
    uppsite_options_updated(null, $newValues);
}
add_action('add_option_' . MYSITEAPP_OPTIONS_OPTS, 'uppsite_options_added', 10, 2);
add_action('update_option_' . MYSITEAPP_OPTIONS_OPTS, 'uppsite_options_updated', 10, 2);
function uppsite_update_status($act) {
    wp_remote_post(MYSITEAPP_AUTOKEY_URL,
        array(
            'body' => 'status='.$act.'&pingback=' . get_bloginfo('pingback_url'),
            'timeout' => 5
        )
    );
}
function uppsite_deactivated() {
    uppsite_change_webapp(false);
    uppsite_update_status("deactivated");
}
register_deactivation_hook(dirname(__FILE__) . "/uppsite.php", 'uppsite_deactivated');
function uppsite_activated() {
    uppsite_update_status("activated");
}
register_activation_hook(dirname(__FILE__) . "/uppsite.php", 'uppsite_activated');
if (!mysiteapp_is_wpcom_vip()):
        function mysiteapp_fix_seo_plugins() {
        global $msap;
        if (!$msap->is_mobile && !$msap->is_app) { return; }
                global $aioseop_options;
        if (is_array($aioseop_options)) {
            $curPage = trim($_SERVER['REQUEST_URI'],'/');
            if (!isset($aioseop_options['aiosp_ex_pages'])) {
                $aioseop_options['aiosp_ex_pages'] = $curPage;
            } else {
                $aioseop_options['aiosp_ex_pages'] .= ",".$curPage;
            }
        }
    }
    add_action('init', 'mysiteapp_fix_seo_plugins');
        function mysiteapp_fix_cache_plugins() {
                if (function_exists('wp_cache_edit_rejected_ua')) {
            global $valid_nonce, $cache_rejected_user_agent;
            if (!in_array(MYSITEAPP_AGENT, $cache_rejected_user_agent)) {
                $cache_rejected_user_agent[] = MYSITEAPP_AGENT;
                $valid_nonce = true;
                ob_start();
                $_POST['wp_rejected_user_agent'] = implode("\n", $cache_rejected_user_agent);
                wp_cache_edit_rejected_ua();
                ob_end_clean();
            }
        }
                if (class_exists('W3_Plugin_TotalCacheAdmin') &&
            (!isset($_REQUEST['page']) || stristr($_REQUEST['page'], "w3tc_") === false)) {
                        $w3_config = & w3_instance('W3_Config');
            $w3_total_cache_plugins = array('PgCache', 'Minify', 'Cdn');
            $save = array();
            foreach ($w3_total_cache_plugins as $w3tc_plugin) {
                                $key = strtolower($w3tc_plugin) . '.reject.ua';
                $rejectArr = $w3_config->get_array($key);
                if (!in_array(MYSITEAPP_AGENT, $rejectArr)) {
                    array_push($rejectArr, MYSITEAPP_AGENT);
                    $w3_config->set($key, $rejectArr);
                                        $save[] = $w3tc_plugin;
                }
            }
            if (count($save) > 0) {
                $w3_config->save(false);
                foreach ($save as $plugin) {
                    $w3tc_admin_instance = & w3_instance('W3_Plugin_' . $plugin . 'Admin');
                    if (!is_null($w3tc_admin_instance)) {
                        if (method_exists($w3tc_admin_instance, 'write_rules_core')) {
                            $w3tc_admin_instance->write_rules_core();
                        }
                        if (method_exists($w3tc_admin_instance, 'write_rules_cache')) {
                            $w3tc_admin_instance->write_rules_cache();
                        }
                    }
                }
            }
        }
    }
    add_action('admin_init','mysiteapp_fix_cache_plugins',10);
endif; 
