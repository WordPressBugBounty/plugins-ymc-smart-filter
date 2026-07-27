<?php

use enshrined\svgSanitize\Sanitizer;


if ( ! class_exists( Sanitizer::class ) ) {
    return;
}

if ( ! function_exists( 'ymc_sanitize_svg_file' ) ) {

    /**
     * Sanitize SVG file.
     *
     * @param string $file SVG file path.
     * @return bool
     */
    function ymc_sanitize_svg_file( string $file ) : bool {

      if ( ! file_exists( $file ) ) {
         return false;
      }

      $svg = file_get_contents( $file );

      if ( $svg === false ) {
         return false;
      }

      /*
      * Normalize encoding to UTF-8.
      */
      if ( function_exists( 'mb_convert_encoding' ) ) {
         $svg = mb_convert_encoding( $svg, 'UTF-8', 'UTF-8, ISO-8859-1, UTF-16, UTF-32' );
      }

      $sanitizer = new Sanitizer();

      $sanitizer->removeRemoteReferences( true );
      $sanitizer->removeXMLTag( false );
      $sanitizer->minify( true );

      $clean = $sanitizer->sanitize($svg);

      if ($clean === false || trim($clean) === '') {
         return false;
      }

      if ( stripos( $clean, '<svg' ) === false ) {
         return false;
      }

      return file_put_contents($file, $clean) !== false;
   }
}




