<?php
/**
 * TrashBouncer - Abstract Degenerator
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
 * @copyright  Holger Teichert 2010–2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    TrashBouncer 
 * @version    0.1.2 Stable
 * @link       http://www.complanar.de/trashbouncer.html
 * @license    GNU/LGPL
 */

require_once(dirname(dirname(__FILE__)).'/config/config.php');

/**
 * Abstract Class degenerator
 * Provide methods regarding filtering of junk on statistical basis.
 * Try to find similar tokens, if a token is not known.
 *
 * @copyright  Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    Degenerator
 */
abstract class degenerator {
  
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
  abstract public function degenerate(array $words);

  /**
   * If the original word is not found in the database then
   * we build "degenerated" versions of the word to lookup.
   *
   * @access private
   * @param string $word
   * @return array An array of degenerated words
   */

  abstract protected function _degenerate_word($word);

}

?>
