<?php

/**
 * Sanitize SVG file
 */

if (! function_exists( 'ymc_sanitize_svg_file')) {
   function ymc_sanitize_svg_file( string $file ) : bool {

      if ( ! file_exists( $file ) ) {
         return false;
      }

      $svg = file_get_contents( $file );

      if ( $svg === false ) {
         return false;
      }

      /*
      * Remove scripts.
      */
      $svg = preg_replace(
         '#<script\b[^>]*>.*?</script>#is',
         '',
         $svg
      );

      /*
      * Remove inline events.
      */
      $svg = preg_replace(
         '/\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i',
         '',
         $svg
      );

      /*
      * Remove javascript: URLs.
      */
      $svg = preg_replace(
         '/javascript\s*:/i',
         '',
         $svg
      );

      /*
      * Remove foreignObject.
      */
      $svg = preg_replace(
         '#<foreignObject\b[^>]*>.*?</foreignObject>#is',
         '',
         $svg
      );

      file_put_contents(
         $file,
         $svg
      );

      return true;
   }
}




