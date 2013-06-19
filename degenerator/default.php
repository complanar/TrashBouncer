<?php
/**
 * TrashBouncer - Default Degenerator
 * Copyright © 2010-2013 Holger Teichert
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    TrashBouncer
 * @version    0.1.2 Stable
 * @link       http://www.complanar.de/trashbouncer.html
 * @license    GNU/LGPL
 */

require_once(dirname(__FILE__).'/degenerator.php');

/**
 * Class degenerator_default
 * Provide methods regarding filtering of junk on statistical basis.
 * Try to find similar tokens, if a token is not known.
 *
 * @copyright  Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    Degenerator
 */
class degenerator_default extends degenerator {
  
  /**
   * Stores degenerated versions for tokens
   *
   * @access public
   * @var array
   */
  public $degenerates = array();

  /**
   * Generates a list of "degenerated" words for a list of words.
   *
   * @access public
   * @param array $words
   * @return array An array containing an array of degenerated tokens for each token
   */
  public function degenerate(array $words) {
    $degenerates = array();
    foreach($words as $word) {
      $degenerates[$word] = $this->_degenerate_word($word);
    }
    return $degenerates;
  }

  /**
   * If the original word is not found in the database then
   * we build "degenerated" versions of the word to lookup.
   *
   * @access private
   * @param string $word
   * @return array An array of degenerated words
   */

  protected function _degenerate_word($word) {

    // Check for any stored words so the process doesn't have to repeat
    if(isset($this->degenerates[$word]) === TRUE)
      return $this->degenerates[$word];

    $degenerate = array();
    // Add different version of upper and lower case and ucfirst
    array_push($degenerate, strtolower($word));
    array_push($degenerate, strtoupper($word));
    array_push($degenerate, ucfirst($word));

    // Some degenerates may be the same as the original word. These don't have
    // to be fetched, so we create a new array with only new tokens
    $real_degenerate = array();
    foreach($degenerate as $deg_word) {
      if($word != $deg_word)
      array_push($real_degenerate, $deg_word);
    }

    // Store the list of degenerates for the token
    $this->degenerates[$word] = $real_degenerate;

    return $real_degenerate;
  }

}

?>