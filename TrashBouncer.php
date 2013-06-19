<?php
/**
 * TrashBouncer - Filter Class
 * Copyright © 2010–2013 Holger Teichert
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
 * @copyright  2010-2013 Holger Teichert
 * @author     Holger Teichert <post@complanar.de>
 * @author     Tobias Leupold <tobias.leupold@web.de>
 * @package    TrashBouncer
 * @version    0.1.2 Stable
 * @link       http://www.complanar.de/trashbouncer.html
 * @license    GNU/LGPL
 *
 * Thanks very much to Tobias Leupold <tobias.leupold@web.de> and his
 * b8 spamfilter (version 0.5 from the public svn) from which I borrowed a
 * lot of code.
 * I took his algorithm of calculating the spamminess of the text and added
 * new features of defining ignore words that are excluded from calculating
 * and/or stopwords that block a text no matter what probability it has.
 * I didn't like the b8 way of handling the database (because calculating
 * spamminess and database actions are mixed up) so I changed the overall
 * design of the spamfilter a little bit.
 *
 * Please visit http://www.complanar.de/trashbouncer.html for help and more
 * information.
 *
 */

require_once(dirname(__FILE__).'/config/config.php');

/**
 * Class TrashBouncer
 * Provides methods regarding filtering of junk on statistical basis.
 *
 * @copyright  Holger Teichert 2010–2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    TrashBouncer
 *
 */
class TrashBouncer {
  
  const HAM = 1;
  const SPAM = -1;
  const UNKNOWN = 0;
  const LEARN = '+';
  const UNLEARN = '-';
  const STOPWORD = 0;
  const IGNOREWORD = 1;
  
  static public $VERSION;
  static protected $TABLE_TOKENS;
  static protected $TABLE_CATEGORIES;
  static protected $TABLE_SPECIALTOKENS;
  static protected $TABLE_LOG;
  
  /**
   * Current configuration regarding the calculation of the probabilities
   *
   * @access public
   * @var array
   */
  public $config;
  
  /**
   * Preferences regarding the handling of potential spam messages
   * 
   * @access public
   * @var array
   */
  public $prefs;
  
  /**
   * Lexer Object
   *
   * @access protected
   * @var object
   */
  protected $lexer = NULL;
  
  /**
   * Database Object
   *
   * @access protected
   * @var object
   */
  protected $db = NULL;
  
  /**
   * Degenerator Object
   *
   * @access protected
   * @var object
   */
  protected $degenerator = NULL;
  
  /**
   * Cache Array
   * 
   * @access protected
   * @var array
   */
  protected $arrCache = array();
  
  /**
   * Relevant tokens
   * 
   * @access protected
   * @var array
   */
  protected $arrRelevantTokens = array();
  
  /**
   * Constructor - Create a new instance
   *
   * @access public
   * @param array $config
   * @param array $config
   * @return void
   */
  public function __construct($config = NULL, $prefs = NULL, &$databaseObj = NULL) {
    // Create version number
    self::$VERSION = sprintf('%s.%s.%s %s',
      TRASHBOUNCER_VERSION,
      TRASHBOUNCER_MINOR_VERSION,
      TRASHBOUNCER_MAINTENANCE_VERSION,
      TRASHBOUNCER_STATUS);
    
    // Load default configuration from config/config.php
    self::$TABLE_TOKENS        = TRASHBOUNCER_TABLE_tokens;
    self::$TABLE_CATEGORIES    = TRASHBOUNCER_TABLE_categories;
    self::$TABLE_SPECIALTOKENS = TRASHBOUNCER_TABLE_specialtokens;
    self::$TABLE_LOG           = TRASHBOUNCER_TABLE_log;
    
    $this->config = array(
      'lexer'        => TRASHBOUNCER_CONFIG_lexer,
      'degenerator'  => TRASHBOUNCER_CONFIG_degenerator,
      'useRelevant'  => TRASHBOUNCER_CONFIG_useRelevant,
      'minRelevance' => TRASHBOUNCER_CONFIG_minRelevance,
      'robS'         => TRASHBOUNCER_CONFIG_robS,
      'probUnknown'  => TRASHBOUNCER_CONFIG_probUnknown);
    // Check and load custom configuration if any
    if (is_array($config) && !empty($config)) {
      foreach($config as $name => $value) {
        switch($name) {
          case 'lexer':
            if (is_string($value)) {
              $this->config[$name] = $value;
            }
            break;
          case 'degenerator':
            if (is_string($value)) {
              $this->config[$name] = $value;
            }
            break;
          case 'useRelevant':
            if (is_integer($value) && $value > 0) {
              $this->config[$name] = $value;
            }
            break;
          case 'minRelevance':
            if (is_numeric($value) && $value > 0 && $value < 0.5) {
              $this->config[$name] = $value;
            }
            break;
          case 'robS':
            if (is_numeric($value) && $value > 0 && $value < 0.5) {
              $this->config[$name] = $value;
            }
            break;
          case 'probUnknown':
            if (is_numeric($value) && $value >= 0 && $value <= 1) {
              $this->config[$name] = $value;
            }
            break;
          case 'table_tokens':
          	if (is_string($value)) {
              self::$TABLE_TOKENS = $value;
            }
            break;
          case 'table_categories':
            if (is_string($value)) {
              self::$TABLE_CATEGORIES = $value;
            }
            break;
          case 'table_specialtokens':
            if (is_string($value)) {
              self::$TABLE_SPECIALTOKENS = $value;
            }
            break;
          case 'table_log':
            if (is_string($value)) {
              self::$TABLE_LOG = $value;
            }
            break;
          default:
            continue;
        }
      }
    }
    
    // Load default preferences from config/config.php
    $this->prefs = array(
      'logEnabled'                  => TRASHBOUNCER_PREFS_logEnabled,
      'autolearnEnabled'            => TRASHBOUNCER_PREFS_autolearnEnabled,
      'autolearnOnHam'              => TRASHBOUNCER_PREFS_autolearnOnHam,
      'autolearnOnSpam'             => TRASHBOUNCER_PREFS_autolearnOnSpam,
      'autolearnOnStopwords'        => TRASHBOUNCER_PREFS_autolearnOnStopwords,
      'autolearnMaxSpamProbability' => TRASHBOUNCER_PREFS_autolearnMaxSpamProbability,
      'autolearnMinSpamProbability' => TRASHBOUNCER_PREFS_autolearnMinSpamProbability,
      'autolearnMaxHamProbability'  => TRASHBOUNCER_PREFS_autolearnMaxHamProbability,
      'autolearnMinHamProbability'  => TRASHBOUNCER_PREFS_autolearnMinHamProbability,
      'stopwordsEnabled'            => TRASHBOUNCER_PREFS_stopwordsEnabled,
      'stopwordsMax'                => TRASHBOUNCER_PREFS_stopwordsMax,
      'ignorewordsEnabled'          => TRASHBOUNCER_PREFS_ignorewordsEnabled,
      'pivotPoint'                  => TRASHBOUNCER_PREFS_pivotPoint);
    // Check and load custom preferences if any
    if (is_array($prefs) && !empty($prefs)) {
      foreach($prefs as $name => $value) {
        switch($name) {
          case 'logEnabled':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnEnabled':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnOnHam':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnOnSpam':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnOnStopwords':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnMaxSpamProbability':
            if (is_numeric($value) && $value >= 0 && $value <= 1) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnMinSpamProbability':
            if (is_numeric($value) && $value >= 0 && $value <= 1) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnMaxHamProbability':
            if (is_numeric($value) && $value >= 0 && $value <= 1) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'autolearnMinHamProbability':
            if (is_numeric($value) && $value >= 0 && $value <= 1) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'stopwordsEnabled':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'stopwordsMax':
            if (is_int($value) && $value >= 0) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'ignorewordsEnabled':
            if (is_bool($value)) {
              $this->prefs[$name] = $value;
            }
            break;
          case 'pivotPoint':
          	if (is_numeric($value) && $value >= 0 && $value <= 1) {
          	  $this->prefs[$name] = $value;
          	}
            break;
        }
      }
    }
    
    // load Database Object
    if ($databaseObj == NULL) {
      require_once(dirname(__FILE__).'/database/database.php');
      $this->db = Database::getInstance();
    } else {
      if (is_object($databaseObj)) {
        $this->db = &$databaseObj;
      } else {
        throw new Exception('Could not load database object. Variable $databaseObj is no valid object.');
      }
    }
    
    // load Lexer Object
    $lexerFile = dirname(__FILE__).'/lexer/'.$this->config['lexer'].'.php';
    if (file_exists($lexerFile)) {
      require_once($lexerFile);
      $lexerClass = 'lexer_'.$this->config['lexer'];
      if (class_exists($lexerClass)) {
        $this->lexer = new $lexerClass;
      } else {
        throw new Exception(sprintf('Could not load lexer object. Class %s not found.', $lexerClass));
      }
    } else {
      throw new Exception(sprintf('Could not load lexer object. File %s not found.', $lexerFile));
    }
  }
  
  /**
   * Checks a text for being spam, logs and learns a text if prefs match
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @return array
   */
  public function check($text, $lang, $infotext = 'Spam Log') {
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    
    $result = $this->classify($text, $lang);
    $learned = FALSE;
    $logged = FALSE;
    var_dump($learned);
    // Autolearn text
    if ($this->prefs['autolearnEnabled'] === TRUE) {
      echo 'autolearn';
      // Learn as Spam if text is Spam and the probability is between the defined borders …
      if (($result['isSpam'] === TRUE && $this->prefs['autolearnOnSpam'])
           &&
           ($result['probability'] >= $this->prefs['autolearnMinSpamProbability'] &&
           $result['probability'] <= $this->prefs['autolearnMaxSpamProbability'])
           ||
           // … or if the text contains too much stopwords
           ($this->prefs['stopwordsEnabled'] === TRUE &&
           $result['stopwordscount'] > $this->prefs['stopwordsMax'] &&
           $this->prefs['autolearnOnStopwords'])) {
        $learned = $this->learn($text, $lang, self::SPAM);
      }
      // Learn as Ham if text is Ham and the probability is between the defined borders
      elseif ($this->prefs['autolearnOnHam'] &&
                $result['probability'] >= $this->prefs['autolearnMinHamProbability'] &&
                $result['probability'] <= $this->prefs['autolearnMaxHamProbability']) {
        echo 'learned as ham.';
        $learned = $this->learn($text, $lang, self::HAM);
      }
    }
    var_dump($learned);
    // Log text
    if ($this->prefs['logEnabled'] === TRUE) {
      if(isset($learned) == TRUE && $learned === TRUE) {
        if($result['isSpam'] === TRUE) {
          $category = self::SPAM;
        } else {
          $category = self::HAM;
        }
      } else {
        $category = self::UNKNOWN;
      }
      echo $category;
      $logged = $this->log($text, $lang, $infotext, $category);
    }
    return array(
             'result' => $result,
             'learned' => $learned,
             'logged' => $logged
           );
  }
  
  /**
   * Determines if a text may be spam or not
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @return boolean
   */
  public function isSpam($text, $lang, $infotext = 'Spam Log') {
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    
    $checked = $this->check($text, $lang, $infotext);
    return $checked['result']['isSpam'];
  }
  
  /**
   * Classifies a string and returns details about how spammish it is
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @return array
   */
  public function classify($text, $lang) {
    $result = array(
                'isSpam' => NULL,
                'probability' => NULL,
                'stopwords' => NULL,
                'stopwordscount' => NULL,
                'ignorewords' => NULL,
                'ignorewordscount' => NULL,
                'tokens' => NULL,
                'tokenscount' => NULL,
                'smalltokenscount' => NULL,
                'largetokenscount' => NULL,
                'fataltokenscount' => NULL,
                'relevanttokens' => NULL
              );
    
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    
    // Get number of already learned HAM and SPAM texts
    $catData = $this->_getCategories($lang);
    $textsHam = $catData[self::HAM];
    $textsSpam = $catData[self::SPAM];
    
    $lexerTokens = $this->lexer->getTokens($text);
    $result['smalltokenscount'] = $lexerTokens['smalltokens'];
    $result['largetokenscount'] = $lexerTokens['largetokens'];
    $result['fataltokenscount'] = $lexerTokens['fataltokens'];
    $result['tokenscount'] = count($lexerTokens['tokens']);
    $result['ignorewordscount'] = $this->_countIgnorewords($lexerTokens['tokens'], $lang);
    $result['stopwordscount'] = $this->_countStopwords($lexerTokens['tokens'], $lang);
    $result['stopwords'] = array_intersect(array_keys($lexerTokens['tokens']), $this->_loadStopwords($lang));
    $result['ignorewords'] = array_intersect(array_keys($lexerTokens['tokens']), $this->_loadIgnorewords($lang));
    $result['tokens'] = $lexerTokens['tokens'];
    $tokens = $lexerTokens['tokens'];
    
    // Fetch available data for all tokens from the database
    $tokenData = $this->__getTokenData(array_keys($tokens), $lang);
    
    // Create degenereates if nessecary and fetch their data
    $missingTokens = array_diff(array_keys($tokens), array_keys($tokenData));
    if (!empty($missingTokens)) {
      // Load degenerator only if nessecary to save RAM
      $degeneratorFile = dirname(__FILE__).'/degenerator/'.$this->config['degenerator'].'.php';
      if (file_exists($degeneratorFile)) {
        require_once($degeneratorFile);
        $degeneratorClass = 'degenerator_'.$this->config['degenerator'];
        if (class_exists($degeneratorClass)) {
          $this->degenerator = new $degeneratorClass();
          $degenerates = $this->degenerator->degenerate($missingTokens);
          $degeneratesList = array();
          foreach ($degenerates as $token => $tokenDegenerates) {
            $degeneratesList = array_merge($degeneratesList, $tokenDegenerates);
          }
          // Add available data of degenerated versions to our array
          $tokenData = array_merge($tokenData, $this->__getTokenData($degeneratesList, $lang));
        } else {
          throw new Exception(sprintf('Warning: Could not load degenerator.  Class %s not found in %s. Try to continue without degenerated tokens …', $degeneratorClass, $degeneratorFile));
        }
      } else {
        throw new Exception(sprintf('Warning: Could not find degenerator. File %s not found. Try to continue without degenerated tokens…', $degeneratorFile));
      }
    } // Now we have all available data (original tokens and degenerates) in $tokenData
    
    $dataTokens = array();
    $dataDegenerates = array();
    
    foreach(array_keys($tokens) as $token) {
      // The token was found in the database so we add this data
      if (isset($tokenData[$token]) === TRUE) {
        $dataTokens[$token] = $tokenData[$token];
      }
      // The token was not found so we look if we find data for degenerated tokens
      else {
        // We loop through all degenerated forms of the token
        foreach($degenerates[$token] as $degenerate) {
          // We found this degenerate
          if(isset($tokenData[$degenerate]) === TRUE) {
            $dataDegenerates[$token][$degenerate] = $tokenData[$degenerate];
          }
        }
      }
    }
    // Now all token data directly found in the database is in $dataTokens and
    // all data for degenerated versions is in $dataDegenerates
    
    
    // Calculate the spamminess and importance of each token (or a degenerated form of it)
    $tokenCount = array();
    $rating = array();
    $importance = array();
    
    // Loop through all tokens of the original text
    foreach ($tokens as $token => $count) {
      // We ignore Ignorewords
      if ($this->_isIgnoreword($token, $lang) === FALSE or $this->prefs['ignorewordsEnabled'] === FALSE) {
        $tokenCount[$token] = $count;
        $tData = isset($dataTokens[$token])?$dataTokens[$token]:NULL;
        $dData = isset($dataDegenerates[$token])?$dataDegenerates[$token]:NULL;
        $rating[$token] = $this->__getTokenProbability($token, $tData, $dData, $textsHam, $textsSpam);
        $importance[$token] = abs(0.5 - $rating[$token]);
      }
    }
    
    // Order by importance
    arsort($importance);
    reset($importance);
    $relevantTokens = array();
    
    // Get the most interesting tokens or use all if we have less than the given number
    $relevant = array();
    for($i = 0; $i < $this->config['useRelevant']; $i++) {
      if($tmp = each($importance)) {
        // Important tokens remain, if the token's rating is important enough, use it
        $relevance = abs(0.5 - $rating[$tmp['key']]);
        if($relevance > $this->config['minRelevance']) {
          // Tokens that appear more than once count more than once
          for($x = 0, $l = $tokenCount[$tmp['key']]; $x < $l; $x++) {
            array_push($relevant, $rating[$tmp['key']]);
            $relevantTokens[$tmp['key']]  = array(
              'relevance'=>$tmp['value'], 
              'probability'=>$rating[$tmp['key']]
            );
          }
        }
      } else {
        // We have less words to use so we've already done
        break;
      }
    }
    
    // Calculate the spamminess of the whole text
    // The first step is to set $hammines and $spamminess to 1 for the first loop
    $hamminess = 1;
    $spamminess = 1;
    
    // We loop through all relevant ratings
    foreach($relevant as $value) {
      $hamminess *= (1.0 - $value);
      $spamminess *= $value;
    }
    
    // If we had no token that was good enough we still have both values at 1
    // So we don't know how to classify this text and set both values to 0.5
    if($hamminess === 1 and $spamminess === 1) {
      $hamminess = 0.5;
      $spamminess = 0.5;
      // n is the number of relevant ratings - we set this to 1 because we must
      // not divide through 0
      $n = 1;
    } else {
      // n is the number of relevant ratings
      $n = count($relevant);
    }
    
    // Now we calculate the combined probability of $hamminess and $spamminess
    // Scaling …
    $hamminess = 1 - pow($hamminess, (1 / $n));
    $spamminess = 1 - pow($spamminess, (1 / $n));
    // Combining …
    // If both values are zero we have not learned anything yet.
    // So our probability is now 0, later it becomes 0.5
    if ($hamminess + $spamminess == 0) {
      $probability = 0;
    } else {
      $probability = ($hamminess - $spamminess) / ($hamminess + $spamminess);
    }
    
    // We don't want a value between -1 and 1 but between 0 and 1
    $probability = (1 + $probability) / 2;
    
    // Ready!
    $result['probability'] = $probability;
    // Is this Spam?
    $result['isSpam'] = $this->_isSpam($result);
    $result['relevanttokens'] = $relevantTokens;
    
    return $result;
  }
  
  /**
   * Get all Ignorewords
   * @access public
   * @param string
   * @return array
   */
  public function getIgnorewords($lang) {
    return $this->_loadIgnorewords($lang);
  }
  
  /**
   * Get all Stopwords
   * @access public
   * @param string
   * @return array
   */
  public function getStopwords($lang) {
    return $this->_loadStopwords($lang);
  }
  
  /**
   * Logs a text and saves it for later classifying/categorizing
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @param string $infotext
   * @param string $category
   * @return boolean
   */
  public function log($text, $lang, $info = 'Spam Log', $category = self::UNKNOWN) {
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    
    $result = $this->db->execute(sprintf('INSERT INTO %s (info, text, cat, lang, ip, tstamp)
                                          VALUES (\'%s\', \'%s\', \'%d\', \'%s\', \'%s\', \'%s\');',
                                 self::$TABLE_LOG,
                                 $info, $text, $category, $lang, $_SERVER['REMOTE_ADDR'],
                                 time()));
    if($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  
  /**
   * Learn a text
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @param string $category
   * @return boolean
   */
  public function learn($text, $lang, $category) {
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    return $this->_train($text, $lang, $category, self::LEARN);
  }
  
  /**
   * Test if a token is a ignoreword
   * @access public
   * @param string
   * @param string
   * @return boolean
   */
  public function isIgnoreword($token, $lang) {
    return $this->_isIgnoreword($token, $lang);
  }
  
  /**
   * Test if a token is a stopword
   * @access public
   * @param string
   * @param string
   * @return boolean
   */
  public function isStopword($token, $lang) {
    return $this->_isStopword($token, $lang);
  }
  
  /**
   * Unlearn a text
   *
   * @access public
   * @param string $text
   * @param string $lang
   * @param string $category
   * @return boolean
   */
  public function unlearn($text, $lang, $category) {
    // Everything OK?
    if (!$this->__valid()) {
      return FALSE;
    }
    return $this->_train($text, $lang, $category, self::UNLEARN);
  }
  
  protected function _train($text, $lang, $category, $learnOrUnlearn) {
    
    // Look wich data we have and find unknown tokens
    $catData = $this->_getCategories($lang);
    $lexerTokens = $this->lexer->getTokens($text);
    $tokenData = $this->__getTokenData(array_keys($lexerTokens['tokens']), $lang);
    $missing = array_diff(array_keys($lexerTokens['tokens']), array_keys($tokenData));
    $missingTokens = array();
    foreach ($missing as $token) {
      $missingTokens[$token] = $lexerTokens['tokens'][$token];
    }
    
    // Collect all known data for updating
    $update = $tokenData;
    if ($category == self::HAM) {
      // We add the text as Ham
      if ($learnOrUnlearn == self::LEARN) {
        foreach($tokenData as $token => $count) {
          $update[$token]= array(
                              'ham' => ($count[self::HAM] + $lexerTokens['tokens'][$token]),
                              'spam' => $count[self::SPAM]
                            );
        }
      }
      // We remove the text as Ham
      elseif ($learnOrUnlearn == self::UNLEARN) {
        foreach($tokenData as $token => $count) {
          $update[$token]= array(
                              'ham' => max(0, ($count[self::HAM] - $lexerTokens['tokens'][$token])),
                              'spam' => $count[self::SPAM]
                            );
        }
      }
    } elseif ($category == self::SPAM) {
      // We add the text as Spam
      if ($learnOrUnlearn == self::LEARN) {
        foreach($tokenData as $token => $count) {
          $update[$token] = array(
                              'ham' => $count[self::HAM],
                              'spam' => ($count[self::SPAM] + $lexerTokens['tokens'][$token])
                            );
        }
      }
      // We remove the text as Spam
      elseif ($learnOrUnlearn == self::UNLEARN) {
        foreach($tokenData as $token => $count) {
          $update[$token] = array(
                              'ham' => $count[self::HAM],
                              'spam' => max(0, ($count[self::SPAM] - $lexerTokens['tokens'][$token]))
                            );
        }
      }
    }
    
    // Collect all unknown tokens for inserting
    // We need to do this only when we learn new texts
    $insert = array();
    if ($learnOrUnlearn == self::LEARN) {
      if ($category == self::HAM) {
        foreach ($missingTokens as $token => $count) {
          $insert[$token] = array('ham' => $count, 'spam' => 0);
        }
      } elseif ($category == self::SPAM) {
        foreach ($missingTokens as $token => $count) {
          $insert[$token] = array('ham' => 0, 'spam' => $count);
        }
      }
    }
    
    // Now commit $insert und $update to the database …
    // Insert new tokens
    foreach ($insert as $token => $data) {
      $this->db->execute(sprintf('INSERT INTO %s (token, lang, ham, spam)
                                  VALUES (\'%s\', \'%s\', \'%d\', \'%d\');',
                           self::$TABLE_TOKENS,
                           $token, $lang, $data['ham'], $data['spam']));
    }
    
    // Update known tokens
    // Tokens with both values zero are not needed any longer so we delete them
    $remove = array();
    foreach ($update as $token => $key) {
      if ($key['ham'] == 0 && $key['spam'] == 0) {
        $remove[] = $token;
      } else {
        $this->db->execute(sprintf('UPDATE %s
                                    SET `ham`=\'%d\', `spam`=\'%d\'
                                    WHERE `token`=\'%s\' AND `lang`=\'%s\'',
                             self::$TABLE_TOKENS,
                             $key['ham'], $key['spam'], $token, $lang));
      }
    }
    $this->db->execute(sprintf('DELETE FROM %s WHERE token in (\'%s\')',
                         self::$TABLE_TOKENS,
                         implode('\', \'', $remove)));
    
    // Update category data
    $categoryData = $this->_getCategories($lang);
    if ($learnOrUnlearn == self::LEARN) {
      $categoryData[$category]++;
    } elseif ($learnOrUnlearn == self::UNLEARN) {
      $categoryData[$category] = max($categoryData[$category] - 1, 0);
    }
    if ($categoryData['insert'] == FALSE) {
      $this->db->execute(sprintf('UPDATE %s SET `ham`=\'%d\', `spam`=\'%d\'
                                  WHERE `lang`=\'%s\'',
                             self::$TABLE_CATEGORIES,
                             $categoryData[self::HAM], $categoryData[self::SPAM], $lang));
    } else {
      $this->db->execute(sprintf('INSERT INTO %s (ham, spam, lang) VALUES (\'%d\', \'%d\', \'%s\');',
                             self::$TABLE_CATEGORIES,
                             $categoryData[self::HAM], $categoryData[self::SPAM], $lang));
    }
    return TRUE;
  }
  
  /**
   * Loads stopwords of a given language into cache
   *
   * @access protected
   * @param string $lang
   * @return void
   */
  protected function _loadStopwords($lang, $blnSkipCache = FALSE) {
    if (!$blnSkipCache && is_array($this->arrCache['stopwords'][$lang])) {
      return $this->arrCache['stopwords'][$lang];
    }
    $stopwords = array();
    $result = $this->db->execute(sprintf('SELECT token FROM %s WHERE `lang`=\'%s\' and `type`=\'%s\'',
                               self::$TABLE_SPECIALTOKENS,
                               $lang, self::STOPWORD))->fetchAllAssoc();
    foreach($result as $row) {
      $stopwords[] = $row['token'];
    }
    $this->arrCache['stopwords'][$lang] = $stopwords;
    return $stopwords;
  }
  
  /**
   * Loads Ignorewords of a given language into cache
   *
   * @access protected
   * @param string $lang
   * @return void
   */
  protected function _loadIgnorewords($lang, $blnSkipCache = FALSE) {
    if (!$blnSkipCache && is_array($this->arrCache['ignorewords'][$lang])) {
      return $this->arrCache['ignorewords'][$lang];
    }
    $ignorewords = array();
    $result = $this->db->execute(sprintf('SELECT token FROM %s WHERE `lang`=\'%s\' and `type`=\'%s\'',
                               self::$TABLE_SPECIALTOKENS,
                               $lang, self::IGNOREWORD))->fetchAllAssoc();
    foreach($result as $row) {
      $ignorewords[] = $row['token'];
    }
    $this->arrCache['ignorewords'][$lang] = $ignorewords;
    return $ignorewords;
  }
  
  /**
   * Determines if a token is a stopword of the current language
   *
   * @access protected
   * @param string $token
   * @param string $lang
   * @return boolena
   */
  protected function _isStopword($token, $lang) {
    $stopwords = $this->_loadStopwords($lang);
    return (in_array($token, $stopwords));
  }
  
  /**
   * Determines if a token is a ignoreword of the current language
   *
   * @access protected
   * @param string $token
   * @param string $lang
   * @return boolean
   */
  protected function _isIgnoreword($token, $lang) {
    $ignorewords = $this->_loadIgnorewords($lang);
    return (in_array($token, $ignorewords));
  }
  
  /**
   * Counts all stopwords
   *
   * @access protected
   * @param array $tokens
   * @param string $lang
   * @return integer
   */
  protected function _countStopwords($tokens, $lang) {
    $i = 0;
    foreach($tokens as $token => $count) {
      if($this->_isStopword($token, $lang)) {
        $i += $count;
      }
    }
    return $i;
  }
  
  /**
   * Counts all ignorewords
   *
   * @access protected
   * @param array $tokens
   * @param string $lang
   * @return integer
   */
  protected function _countIgnorewords($tokens, $lang) {
    $i = 0;
    foreach($tokens as $token => $count) {
      if($this->_isIgnoreword($token, $lang)) {
        $i += $count;
      }
    }
    return $i;
  }
  
  /**
   *
   * @access protected
   * @param string $lang
   * @return array
   */
  protected function _getCategories($lang) {
    $result = $this->db->execute(sprintf('SELECT ham, spam FROM %s WHERE `lang`=\'%s\'',
                                         self::$TABLE_CATEGORIES,
                                         $lang))->fetchAssoc();
    if($result == FALSE) {
      $data = array(self::HAM => 0, self::SPAM => 0, 'insert' => TRUE);
    } else {
      $data = array(self::HAM => $result['ham'], self::SPAM => $result['spam'], 'insert' => FALSE);
    }
    return $data;
  }
  
  /**
   * Determines if this data belongs to a spam text
   *
   * @param array $data
   * @return boolean
   */
  protected function _isSpam($data) {
    if($data['probability'] >= $this->prefs['pivotPoint']) {
      return TRUE;
    } elseif ($this->prefs['stopwordsEnabled'] && $data['stopwordscount'] > $this->prefs['stopwordsMax']) {
      return TRUE;
    } else {
      return FALSE;
    }
    return FALSE;
  }
  
  /**
   * Get the Spam-Probability of a single token
   *
   * @access private
   * @param string $token
   * @param array $tData
   * @param array $dData
   * @param integer $textsHam
   * @param integer $textsSpam
   * @return float
   */
  private function __getTokenProbability($token, $tData, $dData, $textsHam, $textsSpam) {
     // Let's see what we have
     if(isset($tData) === TRUE) {
       // The token was found in the database - we can calculate the spamminess
       // of this token directly
       return $this->__calcProbability($tData, $textsHam, $textsSpam);
     }
     
     // The token was not found - let's look for similar ones
     if(isset($dData) === TRUE) {
       //We found similar words and take the most important one of those
       $rating = 0.5;
       foreach($dData as $degenerate => $data) {
         $ratingTmp = $this->__calcProbability($data, $textsHam, $textsSpam);
         // Is it more important than the previous rating?
         if(abs(0.5 - $ratingTmp) > abs(0.5 - $rating)) {
           $rating = $ratingTmp;
         }
       }
       // We return the most important value of all degenerates
       return $rating;
     }
     // We dont't have similar tokens
     // We take the default value for unknown tokens
     else {
       return $this->config['probUnknown'];
     }
  }
  
  /**
   * Calculate probability out of the given data
   *
   * @access private
   * @param array $data
   * @param integer $textsHam
   * @param integer $textsSpam
   * @return float
   */
  private function __calcProbability($data, $textsHam, $textsSpam) {
    // We consider the number of ham and spam texts instead of the number
    // of entries where the token appeared to calculate a relative spamminess
    // because we count tokens that appear more than once not only one time
    // but as often as they appear in the learned texts.
    
    // Basic Probability
    $relHam = $data[self::HAM];
    $relSpam = $data[self::SPAM];
    
    if($textsHam > 0) {
      $relHam = $relHam / $textsHam;
    }
    if($textsSpam > 0) {
      $relSpam = $relSpam / $textsSpam;
    }
    
    $rating = $relSpam / ($relHam + $relSpam);
    
    // Better Probability proposed by Mr. Robinson
    $all = $data[self::HAM] + $data[self::SPAM];
    return (($this->config['robS'] * $this->config['probUnknown']) + ($all * $rating)) / ($this->config['robS'] + $all);
  }
  
  private function __valid() {
    if (is_object($this->db)) {
      $db = TRUE;
    } else {
      throw new Exception('Could not load database object.');
      $db = FALSE;
    }
    if (is_object($this->lexer)) {
      $lexer = TRUE;
    } else {
      throw new Exception('Could not load lexer object.');
      $lexer = FALSE;
    }
    return ($db && $lexer);
  }
  
  /**
   * Gets data from the database. Gets numbers of SPAM and HAM counts
   *
   * @param array $tokens
   * @return mixed array of FALSE
   */
  private function __getTokenData($tokens, $lang) {
    $tokenData = array();
    $result = $this->db->execute(sprintf('SELECT token, ham, spam FROM %s WHERE `lang`=\'%s\' AND %s',
                               self::$TABLE_TOKENS,
                               $lang,
                               'token IN (\''.implode('\', \'', $tokens).'\')'))->fetchAllAssoc();
    foreach($result as $row) {
      $tokenData[$row['token']] = array(self::HAM => $row['ham'],
                                        self::SPAM => $row['spam']);
    }
    return $tokenData;
  }
}

?>