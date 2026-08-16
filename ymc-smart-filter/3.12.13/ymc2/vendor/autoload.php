<?php


defined( 'ABSPATH' ) || exit;

/**
 * SVG Sanitizer PSR-4 autoloader.
 */

spl_autoload_register(
    static function ( $class ) {

        $prefix = 'enshrined\\svgSanitize\\';

        if ( strpos( $class, $prefix ) !== 0 ) {
            return;
        }

        $relative = substr( $class, strlen( $prefix ) );

        $relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );

        $file = __DIR__
            . '/svg-sanitize/src/'
            . $relative
            . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
);