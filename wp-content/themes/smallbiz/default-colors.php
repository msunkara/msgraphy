<?php
// If called with header as a parameter, act as an ajax call:
if(isset($_REQUEST["header"])){
    $colors =    smallbiz_get_default_colors_for_header($_REQUEST["header"]);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');    
    echo json_encode($colors);
}

// main function -- can be included as well.
function smallbiz_get_default_colors_for_header($header){
    $header = strtolower($header);    
    switch ($header) {
        case "iceberg-blue":
            $arr = array(
                'name_color'       => 'FFFFFF',
                'sub_header_color' => 'FFFFFF',
                'street_color'     => 'FFFFFF',
                'city_color'       => 'FFFFFF',
                'state_color'      => 'FFFFFF',
                'zip_color'        => 'FFFFFF',
                'telephone_color'  => 'FFFFFF',
                'headeremail_color'=> 'FFFFFF',
        
                'menu_color'       => '0B3775',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '109BC8',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '0B3775',        
            );			            
            break;
        case "lights-orange":
            $arr = array(
                'name_color'       => '333333',
                'sub_header_color' => '333333',
                'street_color'     => '333333',
                'city_color'       => '333333',
                'state_color'      => '333333',
                'zip_color'        => '333333',
                'telephone_color'  => '333333',
                'headeremail_color'=> '333333',
        
                'menu_color'       => '333333',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '999999',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '333333',        
            );	
             break;
        case "lights-lightgrey":
            $arr = array(
                'name_color'       => '333333',
                'sub_header_color' => '333333',
                'street_color'     => '333333',
                'city_color'       => '333333',
                'state_color'      => '333333',
                'zip_color'        => '333333',
                'telephone_color'  => '333333',
                'headeremail_color'=> '333333',
        
                'menu_color'       => '333333',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '999999',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '333333',             
            );			
            break;
        case "iceberg-teal":
            $arr = array(
                'name_color'       => 'FFFFFF',
                'sub_header_color' => 'FFFFFF',
                'street_color'     => 'FFFFFF',
                'city_color'       => 'FFFFFF',
                'state_color'      => 'FFFFFF',
                'zip_color'        => 'FFFFFF',
                'telephone_color'  => 'FFFFFF',
                'headeremail_color'=> 'FFFFFF',
        
                'menu_color'       => '15626A',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '4BA1A9',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '15626A',        
            );	
             break;
        case "concrete_brown":
            $arr = array(
                'name_color'       => '2F190D',
                'sub_header_color' => '2F190D',
                'street_color'     => '2F190D',
                'city_color'       => '2F190D',
                'state_color'      => '2F190D',
                'zip_color'        => '2F190D',
                'telephone_color'  => '2F190D',
                'headeremail_color'=> '2F190D',
        
                'menu_color'       => '2F190D',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '946B40',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '2F190D',        
            );	
            break;
        case "concrete_sage_green":
            $arr = array(
                'name_color'       => 'FFFFFF',
                'sub_header_color' => 'FFFFFF',
                'street_color'     => 'FFFFFF',
                'city_color'       => 'FFFFFF',
                'state_color'      => 'FFFFFF',
                'zip_color'        => 'FFFFFF',
                'telephone_color'  => 'FFFFFF',
                'headeremail_color'=> 'FFFFFF',
        
                'menu_color'       => '3A3617',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '838656',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '838656',        
            );	
             break;
        case "dark_swoop_teal":
            $arr = array(
                'name_color'       => 'FFFFFF',
                'sub_header_color' => 'FFFFFF',
                'street_color'     => 'FFFFFF',
                'city_color'       => 'FFFFFF',
                'state_color'      => 'FFFFFF',
                'zip_color'        => 'FFFFFF',
                'telephone_color'  => 'FFFFFF',
                'headeremail_color'=> 'FFFFFF',
        
                'menu_color'       => '262525',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '4A7885',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '325C68',        
            );	
             break;
        case "dark_swoop_purple":
            $arr = array(
                'name_color'       => 'FFFFFF',
                'sub_header_color' => 'FFFFFF',
                'street_color'     => 'FFFFFF',
                'city_color'       => 'FFFFFF',
                'state_color'      => 'FFFFFF',
                'zip_color'        => 'FFFFFF',
                'telephone_color'  => 'FFFFFF',
                'headeremail_color'=> 'FFFFFF',
        
                'menu_color'       => '262525',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '8A348B',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '65176B',        
            );	
             break;
        case "ribbon_tan":
            $arr = array(
                'name_color'       => '4D2B12',
                'sub_header_color' => '4D2B12',
                'street_color'     => '4D2B12',
                'city_color'       => '4D2B12',
                'state_color'      => '4D2B12',
                'zip_color'        => '4D2B12',
                'telephone_color'  => '4D2B12',
                'headeremail_color'=> '4D2B12',
        
                'menu_color'       => '4D2B12',
                'active_color'     => 'FFFFFF',
                'passive_color'    => 'AC8074',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => 'AC8074',        
            );	
            break;
        case "light_swoop_green":
            $arr = array(
                'name_color'       => '333333',
                'sub_header_color' => '333333',
                'street_color'     => '333333',
                'city_color'       => '333333',
                'state_color'      => '333333',
                'zip_color'        => '333333',
                'telephone_color'  => '333333',
                'headeremail_color'=> '333333',
        
                'menu_color'       => '262525',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '88AB4F',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '85AC54',        
            );			
             break;
        default:
            // Defaults:
            $arr = array(
                'name_color'       => '333333',
                'sub_header_color' => '333333',
                'street_color'     => '333333',
                'city_color'       => '333333',
                'state_color'      => '333333',
                'zip_color'        => '333333',
                'telephone_color'  => '333333',
                'headeremail_color'=> '333333',
        
                'menu_color'       => '333333',
                'active_color'     => 'FFFFFF',
                'passive_color'    => '999999',
                'hover_color'      => 'FFFFFF',
        
                'headertag_color'  => '333333',        
            );			
           
    }            
    return $arr;
}   
?>