<?php
/**
 * TrashBouncer - Abstract Lexer
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
 * @copyright  Holger Teichert 2011
 * @author     Holger Teichert <post@complanar.de>
 * @package    TrashBouncer 
 * @version    0.1.2 Stable
 * @link       http://www.complanar.de/trashbouncer.html
 * @license    GNU/LGPL
 */

require_once(dirname(dirname(__FILE__)).'/config/config.php');

/**
 * Abstract Class lexer
 * Provide methods regarding filtering of junk on statistical basis.
 * Split a text into several tokens and count tokens of different types.
 *
 * @copyright  Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    Lexer
 */

abstract class lexer {
/**
   * Case sensitive?
   * @access public
   * @var boolean
   */
  public $caseSensitive;

  /**
   * Allow pure numbers?
   * @access public
   * @var boolean
   */
  public $allowNumbers;

  /**
   * Non word character codes
   * @access public
   * @var string;
   */
  public $nonWordChars;

  /**
   * Minimum token length
   * @access public
   * @var integer
   */
  public $smallTokenLength;

  /**
   * Long token length
   * @access public
   * @var unknown_type
   */
  public $largeTokenLength;

  /**
   * Maximum token length
   * @access public
   * @var integer
   */
  public $fatalTokenLength;

  /**
   * Storage for results
   * @access private
   * @var array
   */
  private $result = array(
            'tokens' => array(),
            'shorttokens' => 0,
            'longtokens' => 0,
            'fataltokens' => 0
  );

  /**
   *
   * @param string $text
   * @return mixed array or FALSE
   */
  abstract public function getTokens($text);

}

?>