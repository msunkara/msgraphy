<?php
if (get_option('show_on_front') == "page" && is_front_page()) {
            include(dirname(__FILE__) . "/index.php");
} else {
    include(dirname(__FILE__) . "/single.php");
}
