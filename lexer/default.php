<?php
/**
 * TrashBouncer - Default Lexer
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

require_once(dirname(__FILE__).'/lexer.php');

/**
 * Class lexer_default
 * Provide methods regarding filtering of junk on statistical basis.
 * Split a text into several tokens and count tokens of different types.
 *
 * @copyright  Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    Lexer
 */

class lexer_default extends lexer {

  /**
   * Case sensitive?
   * @access public
   * @var boolean
   */
  public $caseSensitive = TRUE;

  /**
   * Allow pure numbers?
   * @access public
   * @var boolean
   */
  public $allowNumbers = FALSE;

  /**
   * Non word character codes
   * @access public
   * @var string;
   */
  public $nonWordChars = '\\x00-\\x26\\x28-\\x2F\\x3A-\\x3F\\x5B-\\x5F\\x7B-\\x7F';

  /**
   * Minimum token length
   * @access public
   * @var integer
   */
  public $smallTokenLength = 3;

  /**
   * Long token length
   * @access public
   * @var unknown_type
   */
  public $largeTokenLength = 20;

  /**
   * Maximum token length
   * @access public
   * @var integer
   */
  public $fatalTokenLength = 60;

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
  public function getTokens($text) {

    $this->result = array(
      'tokens' => array(),
      'smalltokens' => 0,
      'largetokens' => 0,
      'fataltokens' => 0
    );

    $tokens = preg_split('~['.$this->nonWordChars.']+~', $text);

    if ($tokens) {
      foreach ($tokens as $token) {
        // If we do this case sensitive we only trim the words, otherwise
        // every token is converted to lowercase
        if ($this->caseSensitive) {
          $token = trim($token);
        } else {
          $token = strtolower(trim($token));
        }

        if ($this->_isValid($token)) {
          if(isset($this->result['tokens'][$token]) === FALSE) {
            $this->result['tokens'][$token] = 1;
          } else {
            $this->result['tokens'][$token] += 1;
          }
        }
      }
    }
    return $this->result;
  }

  /**
   * Check if the token is valid
   *
   * @param strin $token
   * @return boolean
   */
  protected function _isValid($token) {
    if ('' == $token) {
      return FALSE;
    } elseif (strlen($token) < $this->smallTokenLength) {
      $this->result['smalltokens'] += 1;
      return FALSE;
    } else {
      if (strlen($token) > $this->fatalTokenLength) {
        $this->result['fataltokens'] += 1;
        return FALSE;
      } elseif (strlen($token) > $this->largeTokenLength) {
        $this->result['largetokens'] += 1;
      }
      if (!$this->allowNumbers && preg_match('/^[0-9]+$/', $token) > 0 ) {
        return FALSE;
      } else {
        return TRUE;
      }
    }
    return TRUE;
  }

}
?>