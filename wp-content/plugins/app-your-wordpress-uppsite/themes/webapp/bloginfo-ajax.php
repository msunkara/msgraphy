<?php
print json_encode(
    array(
        'name' => get_bloginfo('name'),
        'url' => get_bloginfo('wpurl'),
        'version' => get_bloginfo('version')
    )
);
