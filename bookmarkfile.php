<?php
/*
Plugin Name: Bookmarkfile
Plugin URI: http://www.ekkart.de/?page_id=212
Description: Displays bookmark files as link list.
Version: 1.1.2
Author: Ekkart Kleinod
Author URI: http://www.ekkart.de/
License: GPL2
*/
/*	Copyright 2010  Ekkart Kleinod  (email: ekkart@ekkart.de)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * This file contains the complete plugin functionality.
 *
 * In order to use unique function names, the prefix 'bf_' will be used.
 * Please stick to the coding standards and the inline documentation standards as well.
 *
 * @package WordPress
 */

/** Needed constants. */
define ( 'TOP_CAT_NAME', 'topCategory' );


/**
 * Main handler for bookmarkfile shortcode.
 *
 * Reads the arguments specified in the shortcode and uses them to generate the required link list.
 *
 * The file is read and processed line by line. Another approach could read the whole file and process
 * it at once. I think my approach (line by line) uses less resourcesbut I cannot prove it.
 *
 * @todo Internationalize the plugin.
 *
 * @since 0.1
 * @package WordPress
 * @param array $atts array of shortcode attributes
 * @param string $content text within enclosing form of shortcode element
 * @param string $code the shortcode found, when == callback name
 */
function bf_bookmarkfile_handler($atts, $content=null, $code='') {
  // read attributes, eliminate unsupported attributes and declare default values
  extract( shortcode_atts( array(
      'filename' => null,
      'target' => null
      ), $atts ) );

  // start paragraph
  $output = '<p>';

  // open file
  $bookmarkfile = fopen( $filename, "r" );
  if ( $bookmarkfile ) {

    // top category
    $topCategory = new BF_Category();
    $topCategory->name = TOP_CAT_NAME;
    $topCategory->description = 'Generated category.';

    // which kind of bookmark file?
    $line = trim( fgets( $bookmarkfile ) );

    // opera?
    if ( strcasecmp( 'Opera Hotlist version 2.0', $line ) == 0 ) {

      // stack of current categories
      $currentCategories = array($topCategory);

      // read and process file line by line, store values in categories
      while ( !feof( $bookmarkfile ) ) {
        $line = trim( fgets( $bookmarkfile ) );

        // folder start - create new category, store it, push to stack of categories
        if ( $line == '#FOLDER' ) {
          $newCategory = new BF_Category();
          bf_process_opera_item( $bookmarkfile, $newCategory );

          if ( is_null ( end($currentCategories) ) ) {
            $output .= 'error: no parent category for new folder<br />';
            break;
          }

          end( $currentCategories )->addCategory( $newCategory );

          array_push( $currentCategories, $newCategory );
        }

        // folder end - remove category from stack
        if ( $line == '-' ) {
          array_pop( $currentCategories );
        }

        // link - store in current category
        if ( $line == '#URL' ) {
          $newLink = new BF_Link();
          bf_process_opera_item( $bookmarkfile, $newLink );

          if ( is_null ( end($currentCategories) ) ) {
            $output .= 'error: no parent category for new link<br />';
            break;
          }

          end( $currentCategories )->addLink( $newLink );
        }
      }

    } else {
      // unrecognized bookmark file
      $output .= 'Unrecognized type of bookmark file.';
    }

    // close file
    fclose( $bookmarkfile );

    // output data (same output for all file types)
    $output .= $topCategory->toHTML($target);

  } else {
    // error while opening file
    $output .= sprintf( 'Could not open bookmarkfile "%s".', $filename );
  }

  // end paragraph
  $output .= '</p>';

  // return generated output
  return $output;
}

/**
 * Reads an opera item (folder or url) and stores the values in the given storage object.
 *
 * @param $bookmarkfile bookmarkfile to read from
 * @param $storage storage object to store to (BF_Link or BF_Category)
 */
function bf_process_opera_item($bookmarkfile, $storage) {
  $end_of_item = false;

  // read file line by line till end of item or end of file
  while ( !$end_of_item && !feof( $bookmarkfile ) ) {
    $line = trim( fgets( $bookmarkfile ) );

    // name
    $compare = 'NAME=';
    if ( substr( $line, 0, strlen( $compare ) ) == $compare ) {
      $storage->name = trim( substr( $line, strlen( $compare ) ) );
    }

    // url (only for BF_Link)
    $compare = 'URL=';
    if ( substr( $line, 0, strlen( $compare ) ) == $compare ) {
      $storage->url = trim( substr( $line, strlen( $compare ) ) );
    }

    // description
    $compare = 'DESCRIPTION=';
    if ( substr( $line, 0, strlen( $compare ) ) == $compare ) {
      $storage->description = trim( substr( $line, strlen( $compare ) ) );
    }

    // stop
    if ($line == '') {
      $end_of_item = true;
    }

  }
}

/**
 * Storage class for links.
 *
 * Links are defined by:
 * - a name (mandatory)
 * - an url (mandatory)
 * - a description (optional)
 *
 * At the moment, the class only stores the values, therefore no getters and setters were defined,
 * access to the class variables is public.
 */
class BF_Link {
  public $name = null;
  public $url = null;
  public $description = null;

  /**
   * Return HTML representation of link.
   *
   * @param target target of HTML links
   * @return HTML representation
   */
  public function toHTML($target) {
    $targetHTML = (is_null( $target )) ? "" : sprintf( ' target="%s"', $target );
    $html = sprintf( '<a href="%s"%s>%s</a>', esc_url( $this->url ), $targetHTML, $this->name );
    if (!is_null ( $this->description ) ) {
      $html .= sprintf( '<br /><span style="font-style: italic; font-size: smaller;">%s</span>', $this->description );
    }
    return $html;
  }

  /**
   * Compares two links.
   *
   * @param $link_a first links
   * @param $link_b second links
   * @return comparison result
   *  @retval an integer less than zero if the first argument is less than the second
   *  @retval an integer equal to zero if the first argument is equal to the second
   *  @retval an integer greater than zero if the first argument is greater than the second
   */
  public static function compare($link_a, $link_b) {
    return strcasecmp ( $link_a->name, $link_b->name );
  }

}

/**
 * Storage class for a category.
 *
 * Categories are defined by:
 * - a name (mandatory)
 * - a description (optional)
 * - a list of links (may be empty)
 * - a list of categories (may be empty)
 *
 * The name can be accessed directly, the arrays are accessed via methods.
 */
class BF_Category {
  public $name = null;
  public $description = null;
  private $links = array();
  private $categories = array();

  /**
   * Adds a new link.
   * @param $newLink new link object
   */
  public function addLink($newLink) {
    array_push( $this->links, $newLink );
  }

  /**
   * Get all links.
   * @return sorted array of links
   */
  public function getLinks() {
    usort ( $this->links, array( 'BF_Link', 'compare' ) );
    return $this->links;
  }

  /**
   * Adds a new category.
   * @param $newCategory new category object
   */
  public function addCategory($newCategory) {
    array_push( $this->categories, $newCategory);
  }

  /**
   * Get all categories.
   * @return sorted array of categories
   */
  public function getCategories() {
    usort ( $this->categories, array( 'BF_Category', 'compare' ) );
    return $this->categories;
  }

  /**
   * Return HTML representation of category.
   *
   * HTML representation is:
   * - name and description of category
   * - list of links
   * - list of categories
   *
   * @param target target of HTML links
   * @return HTML representation
   */
  public function toHTML($target) {
    $html = "";

    // name and description of category (do not print top category's name and description)
    if ( strcmp ( $this->name, TOP_CAT_NAME ) != 0 ) {
      $html .= sprintf( '<strong>%s</strong>', $this->name );
      if (!is_null ( $this->description ) ) {
        $html .= sprintf( '<br /><em>%s</em>', $this->description );
      }
    }

    $html .= "<ul>";

    // list of links
    foreach ( $this->getLinks() as $link ) {
      $html .= sprintf( '<li>%s</li>', $link->toHTML($target) );
    }

    // list of categories
    foreach ( $this->getCategories() as $category ) {
      $html .= sprintf( '<li>%s</li>', $category->toHTML($target) );
    }

    $html .= "</ul>";
    return $html;
  }

  /**
   * Compares two categories.
   *
   * @param $cat_a first category
   * @param $cat_b second category
   * @return comparison result
   *  @retval an integer less than zero if the first argument is less than the second
   *  @retval an integer equal to zero if the first argument is equal to the second
   *  @retval an integer greater than zero if the first argument is greater than the second
   */
  public static function compare($cat_a, $cat_b) {
    return strcasecmp ( $cat_a->name, $cat_b->name );
  }

}

// register the shortcode within WordPress
add_shortcode('bookmarkfile', 'bf_bookmarkfile_handler');

?>
