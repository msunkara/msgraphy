<?php

unset( $GLOBALS['csstidy']['all_properties']['binding'] );

// Support browser prefixes for properties only in the latest CSS draft
foreach ( $GLOBALS['csstidy']['all_properties'] as $property => $levels ) {
	if ( strpos( $levels, "," ) === false ) {
		$GLOBALS['csstidy']['all_properties']['-moz-' . $property] = $levels;
		$GLOBALS['csstidy']['all_properties']['-webkit-' . $property] = $levels;
		$GLOBALS['csstidy']['all_properties']['-ms-' . $property] = $levels;
		$GLOBALS['csstidy']['all_properties']['-o-' . $property] = $levels;
		$GLOBALS['csstidy']['all_properties']['-khtml-' . $property] = $levels;

		if ( in_array( $property, $GLOBALS['csstidy']['unit_values'] ) ) {
			$GLOBALS['csstidy']['unit_values'][] = '-moz-' . $property;
			$GLOBALS['csstidy']['unit_values'][] = '-webkit-' . $property;
			$GLOBALS['csstidy']['unit_values'][] = '-ms-' . $property;
			$GLOBALS['csstidy']['unit_values'][] = '-o-' . $property;
			$GLOBALS['csstidy']['unit_values'][] = '-khtml-' . $property;
		}

		if ( in_array( $property, $GLOBALS['csstidy']['color_values'] ) ) {
			$GLOBALS['csstidy']['color_values'][] = '-moz-' . $property;
			$GLOBALS['csstidy']['color_values'][] = '-webkit-' . $property;
			$GLOBALS['csstidy']['color_values'][] = '-ms-' . $property;
			$GLOBALS['csstidy']['color_values'][] = '-o-' . $property;
			$GLOBALS['csstidy']['color_values'][] = '-khtml-' . $property;
		}
	}
}

/**
 * CSS Animation
 *
 * @see https://developer.mozilla.org/en/CSS/CSS_animations
 */
$GLOBALS['csstidy']['at_rules']['-webkit-keyframes'] = 'at';
$GLOBALS['csstidy']['at_rules']['-moz-keyframes'] = 'at';
$GLOBALS['csstidy']['at_rules']['-ms-keyframes'] = 'at';

/**
 * Non-standard CSS properties.  They're not part of any spec, but we say 
 * they're in all of them so that we can support them.
 */
$GLOBALS['csstidy']['all_properties']['filter'] = 'CSS2.0,CSS2.1,CSS3.0';
$GLOBALS['csstidy']['all_properties']['scrollbar-face-color'] = 'CSS2.0,CSS2.1,CSS3.0';
$GLOBALS['csstidy']['all_properties']['-ms-interpolation-mode'] = 'CSS2.0,CSS2.1,CSS3.0';
$GLOBALS['csstidy']['all_properties']['text-rendering'] = 'CSS2.0,CSS2.1,CSS3.0';
$GLOBALS['csstidy']['all_properties']['-webkit-transform-origin-x'] = 'CSS3.0';
$GLOBALS['csstidy']['all_properties']['-webkit-transform-origin-y'] = 'CSS3.0';
$GLOBALS['csstidy']['all_properties']['-webkit-transform-origin-z'] = 'CSS3.0';
$GLOBALS['csstidy']['all_properties']['-webkit-font-smoothing'] = 'CSS3.0';
$GLOBALS['csstidy']['all_properties']['-font-smooth'] = 'CSS3.0';
$GLOBALS['csstidy']['all_properties']['font-smoothing'] = 'CSS3.0';
