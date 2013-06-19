<?php
/**
 * TrashBouncerAdmin - TrashBouncer Administration Class
 * Copyright Holger Teichert 2010–2013
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
 * @package    TrashBouncer
 * @version    0.1.2 Stable
 * @link       http://www.complanar.de/trashbouncer.html
 * @license    GNU/LGPL
 *
 */

require_once (dirname(__FILE__).'/TrashBouncer.php');

/**
 * Class TrashBouncerAdmin
 * Provides methods regarding the administration of the TrashBouncer spam filter as
 * learning and editing log entries, customize configuration and preferences,
 * manage stop- and ignorewords and …
 *
 * @copyright  © Holger Teichert 2010-2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    TrashBouncer
 */
class TrashBouncerAdmin extends TrashBouncer {

  /**
   * Add stopword to language
   *
   * @access public
   * @param string $word
   * @param string $lang
   * @return boolean
   */
  public function addStopword($word, $lang) {
    $result = $this->db->execute(sprintf('INSERT INTO %s (token, lang, type)
      VALUES (\'%s\', \'%s\', \'%s\');',
    self::$TABLE_SPECIALTOKENS, $word, $lang, self::STOPWORD));
    return TRUE;
  }

  /**
   * Delete stopword from language
   *
   * @access public
   * @param mixed $word Can be a string or an array of multiple strings
   * @param mixed $lang Can be a string or an array of multiple strings, that 
   * must correspond to the words in the array $words. This can not be an array
   * if $word is an string. 
   * @return boolean
   */
  public function delStopword($word, $lang) {
    if (is_array($word)) {
      if (is_array($lang)) {
        foreach ($word as $key => $element) {
          $result = $this->db->execute(sprintf('DELETE FROM %s
            WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
            self::$TABLE_SPECIALTOKENS, $element, $lang[$key], self::STOPWORD));
        }
        return TRUE;
      } else {
        $result = $this->db->execute(sprintf('DELETE FROM %s
            WHERE `token` in (\'%s\') AND `lang`=\'%s\' AND `type`=\'%s\'',
            self::$TABLE_SPECIALTOKENS, implode('\', \'', $word), $lang, self::STOPWORD));
        return TRUE;
      }
    } else {
      $result = $this->db->execute(sprintf('DELETE FROM %s
        WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
      self::$TABLE_SPECIALTOKENS, $word, $lang, self::STOPWORD));
      return TRUE;
    }
  }

  /**
   * Update a stopword
   *
   * @param string $oldword
   * @param string $newword
   * @param string $oldlang
   * @param string $newlang
   * @return boolean
   */
  public function updateStopword($oldword, $newword, $oldlang, $newlang = NULL) {
    if ($newlang == NULL) {
      $newlang = $oldlang;
    }
    $result = $this->db->execute(sprintf('UPDATE %s
      SET `token`=\'%s\', `lang`=\'%s\'
      WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
    self::$TABLE_SPECIALTOKENS, $newword, $newlang, $oldword, $oldlang, self::STOPWORD));
    return TRUE;
  }

  /**
   * Add an ignoreword to an language
   *
   * @access public
   * @param string $word
   * @param string $lang
   * @return boolean
   */
  public function addIgnoreword($word, $lang) {
    $result = $this->db->execute(sprintf('INSERT INTO %s (token, lang, type)
      VALUES (\'%s\', \'%s\', \'%s\');',
    self::$TABLE_SPECIALTOKENS, $word, $lang, self::IGNOREWORD));
    return TRUE;
  }

  /**
   * Delete ignoreword from language
   *
   * @access public
   * @param mixed $word Can be a string or an array of multiple strings
   * @param mixed $lang Can be a string or an array of multiple strings, that 
   * must correspond to the words in the array $words. This can not be an array
   * if $word is an string. 
   * @return boolean
   */
  public function delIgnoreword($word, $lang) {
    if (is_array($word)) {
      if (is_array($lang)) {
        foreach ($word as $key => $element) {
          $result = $this->db->execute(sprintf('DELETE FROM %s
            WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
            self::$TABLE_SPECIALTOKENS, $element, $lang[$key], self::IGNOREWORD));
        }
        return TRUE;
      } else {
        $result = $this->db->execute(sprintf('DELETE FROM %s
            WHERE `token` in (\'%s\') AND `lang`=\'%s\' AND `type`=\'%s\'',
            self::$TABLE_SPECIALTOKENS, implode('\', \'', $word), $lang, self::IGNOREWORD));
        return TRUE;
      }
    } else {
      $result = $this->db->execute(sprintf('DELETE FROM %s
        WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
      self::$TABLE_SPECIALTOKENS, $word, $lang, self::IGNOREWORD));
      return TRUE;
    }
  }

  /**
   * Update a ignoreword
   *
   * @param string $oldword
   * @param string $newword
   * @param string $oldlang
   * @param string $newlang
   * @return boolean
   */
  public function updateIgnoreword($oldword, $newword, $oldlang, $newlang = NULL) {
    if ($newlang == NULL) {
      $newlang = $oldlang;
    }
    $result = $this->db->execute(sprintf('UPDATE %s
      SET `token`=\'%s\', `lang`=\'%s\'
      WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
    self::$TABLE_SPECIALTOKENS, $newword, $newlang, $oldword, $oldlang, self::IGNOREWORD));
    return TRUE;
  }

  /**
   * Gets all log entries matching the conditions
   *
   * @access public
   * @param integer $offset
   * @param integer $limit
   * @param string $filter
   * @param string $orderby
   * @return array
   */
  public function getLogEntries($lang, $offset = 0, $limit = 25, $filterCat = NULL, $filter = NULL) {
    $query = 'SELECT * FROM '.self::$TABLE_LOG.' WHERE `lang`=\''.$lang.'\'';
    if ($filterCat) {
      $query .= ' AND `cat`=\''.$filterCat.'\'';
    }
    if ($filter) {
      $query .= ' AND (`info` LIKE "%'.$filter.'%" || `text` LIKE "%'.$filter.'%")';
    }
    $query .= 'ORDER BY created DESC LIMIT '.$offset.','.$limit.';';
    return $this->db->execute($query)->fetchAllAssoc();
  }

  /**
   * Gets all data of one or more log entries
   *
   * @param mixed $id integer or array
   * @return mixed array or FALSE
   */
  public function getLogEntry($id) {
    if (is_array($id)) {
      $where = '`id` IN (\''.implode('\', \'', $id).'\')';
      return $this->db->execute(sprintf('SELECT * FROM %s WHERE %s', self::$TABLE_LOG, $where))->fetchAllAssoc();
    } elseif (is_numeric($id)) {
      return $this->db->execute(sprintf('SELECT * FROM %s WHERE `id`=\'%s\'', self::$TABLE_LOG, $id))->fetchAssoc();
    }
    return FALSE;
  }

  /**
   * Deletes one or more log entries
   *
   * @access public
   * @param mixed $id integer or array
   * @return boolean
   */
  public function delLogEntry($id) {
    if (is_array($id)) {
      $where = '`id` IN (\''.implode('\', \'', $id).'\')';
    } elseif (is_numeric($id)) {
      $where = '`id`=\''.$id.'\'';
    } else {
      return FALSE;
    }
    $this->db->execute(sprintf('DELETE FROM %s WHERE %s', self::$TABLE_LOG, $where));
    return TRUE;
  }


  /**
   * Learn one or more log entries
   *
   * @access public
   * @param mixed $id integer or array
   * @param string $category
   * @param boolean $delete
   * @return boolean
   */
  public function learnLogEntry($id, $category, $delete = FALSE) {
    $entry = $this->getLogEntry($id);
    // Do we have more than one entry to learn?
    if (is_array($id)) {
      $learnTexts = array();
      $unlearnTexts = array();
      foreach ($entry as $value) {
        // We don't need to do anything if the category hasn't changed
        if ($category != $value['cat']) {
          // At first we remove the text from the old category
          if ($value['cat'] != self::UNKNOWN) {
            $this->unlearn($value['text'], $value['lang'], $value['cat']);
          }
          // Now we add it to the new category
          if ($category != self::UNKNOWN) {
            $this->learn($value['text'], $value['lang'], $category);
          }
        }
      }
      // Shall we delete the learned log entries?
      if ($delete === TRUE) {
        $this->delLogEntry($id);
      } else {
        $this->db->execute(sprintf('UPDATE %s SET `cat`=\'%s\' WHERE `id` in (\'%s\')', self::$TABLE_LOG, $category, implode('\', \'', $id)));
      }
    } elseif (is_numeric($id)) {
      // We don't need to do anything if the category hasn't changed
      if ($category != $entry['cat']) {
        // At first we remove the text from the old category
        if ($entry['cat'] != self::UNKNOWN) {
          $this->unlearn($entry['text'], $entry['lang'], $entry['cat']);
        }
        // Now we add it to the new category
        if ($category != self::UNKNOWN) {
          $this->learn($entry['text'], $entry['lang'], $category);
        }
      }
      // Shall we delete the learned log entries?
      if ($delete === TRUE) {
        $this->delLogEntry($id);
      } else {
        $this->db->execute(sprintf('UPDATE %s SET `cat`=\'%s\' WHERE `id`=\'%s\'', self::$TABLE_LOG, $category, $id));
      }
    } else {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Export the training data to a file so that it can be
   * (re-)imported later on. The second parameter specifies if an
   * existing file will be overwritten. Default is TRUE.
   *
   * @access public
   * @param string $file
   * @param mixed $filterLang string or array
   * @param boolean $overwrite
   * @return boolean
   */
  public function exportTraining($file, $commentString = '', $overwrite = FALSE, $selectedLang = NULL, $fieldSeparator = ',', $fieldDelimitor = '"', $lineDelimitor = "\n") {
    // Does this file already exist?
    if (strtolower(substr($file, -3)) != '.tb') {
      $file .= '.tb';
    }
    if ($overwrite == FALSE and file_exists($file)) {
      return FALSE;
    }

    $categoriesCols = 'ham,spam,lang';
    $tokensCols = 'token,ham,spam,lang';
    $specialtokensCols = 'token,type,lang';
    // Collect category data
    if ($selectedLang) {
      if (is_array($selectedLang)) {
        $where = '`lang` IN (\''.implode('\', \'', $selectedLang).'\')';
      } else {
        $where = '`lang`=\''.$selectedLang.'\'';
      }
      $categoryData = $this->db->execute(sprintf('SELECT %s FROM %s WHERE %s', $categoriesCols, self::$TABLE_CATEGORIES, $where))->fetchAllAssoc();
    } else {
      $categoryData = $this->db->execute(sprintf('SELECT %s FROM %s', $categoriesCols, self::$TABLE_CATEGORIES))->fetchAllAssoc();
    }
    $categoryString = $this->__generateCSVString($categoryData, $fieldSeparator, $fieldDelimitor, $lineDelimitor);
    ;
    // Extract found languages from category data
    $foundLangs = array();
    if (is_array($categoryData)) {
      foreach ($categoryData as $value) {
        $foundLangs[] = $value['lang'];
      }
    }
    unset($categoryData);

    // Collect token data
    if ($selectedLang) {
      if (is_array($selectedLang)) {
        $where = '`lang` IN (\''.implode('\', \'', $selectedLang).'\')';
      } else {
        $where = '`lang`=\''.$selectedLang.'\'';
      }
      $tokensData = $this->db->execute(sprintf('SELECT %s FROM %s WHERE %s', $tokensCols, self::$TABLE_TOKENS, $where))->fetchAllAssoc();
    } else {
      $tokensData = $this->db->execute(sprintf('SELECT %s FROM %s', $tokensCols, self::$TABLE_TOKENS))->fetchAllAssoc();
    }
    $tokensString = $this->__generateCSVString($tokensData, $fieldSeparator, $fieldDelimitor, $lineDelimitor);
    // Extract found languages from tokens data
    if (is_array($tokensData)) {
      foreach ($tokensData as $value) {
        $foundLangs[] = $value['lang'];
      }
    }
    unset($tokensData);

    // Collect specialtoken data
    if ($selectedLang) {
      if (is_array($selectedLang)) {
        $where = '`lang` IN (\''.implode('\', \'', $selectedLang).'\')';
      } else {
        $where = '`lang`=\''.$selectedLang.'\'';
      }
      $specialtokensData = $this->db->execute(sprintf('SELECT %s FROM %s WHERE %s', $specialtokensCols, self::$TABLE_SPECIALTOKENS, $where))->fetchAllAssoc();
    } else {
      $specialtokensData = $this->db->execute(sprintf('SELECT %s FROM %s', $specialtokensCols, self::$TABLE_SPECIALTOKENS))->fetchAllAssoc();
    }
    $specialtokensString = $this->__generateCSVString($specialtokensData, $fieldSeparator, $fieldDelimitor, $lineDelimitor);
    // Extract found languages from specialtokens data
    if (is_array($specialtokensData)) {
      foreach ($specialtokensData as $value) {
        $foundLangs[] = $value['lang'];
      }
    }
    unset($specialtokensData);

    // Flatten the languages array
    $foundLangsString = implode(',', array_unique($foundLangs));

    // Create custom comment
    if ($commentString != '') {
      $commentString = "\n".'&comment = '.$this->__generateCSVString($commentString, $fieldSeparator, $fieldDelimitor, $lineDelimitor);
    }
    // Create a new file
    if ($fp = fopen($file, 'w')) {
      flock($fp, 2);

      $description = 'This file was created by the export function of the TrashBouncer '.self::$VERSION.' spam filter.';
      $author = 'Holger Teichert <post@complanar.de>';
      // Escape Data
      $description = $this->__quoteForExport($description, $fieldDelimitor);
      $author = $this->__quoteForExport($author, $fieldDelimitor);
      $date = $this->__quoteForExport(date('Y-m-d H:i:s'), $fieldDelimitor);
      $filename = $this->__quoteForExport($file, $fieldDelimitor);
      $foundLangsString = $this->__quoteForExport($foundLangsString, $fieldDelimitor);
      $categoriesCols = $this->__quoteForExport($categoriesCols, $fieldDelimitor);
      $tokensCols = $this->__quoteForExport($tokensCols, $fieldDelimitor);
      $specialtokensCols = $this->__quoteForExport($specialtokensCols, $fieldDelimitor);

      $exportString = <<<STR
&description = $description
&author = $author
&date = $date
&file = $filename$commentString
&lang = $foundLangsString

&categoriesStart = $categoriesCols
$categoryString
&categoriesEnd

&tokensStart = $tokensCols
$tokensString
&tokensEnd

&specialtokensStart = $specialtokensCols
$specialtokensString
&specialtokensEnd
STR;
      fputs($fp, $exportString);
      flock($fp, 3);
      fclose($fp);
      chmod($file, 0775);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Import training data from a plain text file.
   * The second parameter specifies if existing data
   * will be overwritten or only added. Default is FALSE.
   *
   * @access public
   * @param string $file
   * @param boolean $overwrite
   * @param mixed $selectedLang
   * @return boolean
   */
  public function importTraining($file, $overwrite = FALSE, $selectedLang = NULL) {
    // Does this file exist?
    if (strtolower(substr($file, -3)) != '.tb') {
      $file .= '.tb';
    }
    if (!file_exists($file)) {
      return FALSE;
    }

    $fileinfos = $this->getExportFileInfos($file);

    $filecontent = implode('', file($file));
    $filelangs = (FALSE == strpos(',', $fileinfos['lang'])) ? array($fileinfos['lang']) : explode(',', $fileinfos['lang']);
    if (is_string($selectedLang)) {
      $importLangs = array_intersect($filelangs, array($selectedLang));
    } elseif (is_array($selectedLang)) {
      $importLangs = array_intersect($filelangs, $selectedLang);
    } else {
      $importLangs = $filelangs;
    }

    $updatedRows = 0;
    $insertedRows = 0;
    $notModifiedRows = 0;
    // Loop through categories, tokens and specialtokens
    $tags = array('categories', 'tokens', 'specialtokens');
    foreach ($tags as $tag) {
      $startMark = '&'.$tag.'Start = ';
      $endMark = '&'.$tag.'End';
      $startPos = strpos($filecontent, $startMark);
      $endPos = strpos($filecontent, $endMark);

      $csv = trim(substr($filecontent, $startPos + strlen($startMark), $endPos - $startPos - strlen($startMark)));
      $csv = $this->__parseCSVString($csv);

      $cols = array_shift($csv);
      $cols = explode(',', $cols[0]);
      $colnames = implode(', ', $cols);
      switch ($tag) {
        case 'tokens':
          $table = self::$TABLE_TOKENS;
          break;
        case 'categories':
          $table = self::$TABLE_CATEGORIES;
          break;
        case 'specialtokens':
          $table = self::$TABLE_SPECIALTOKENS;
          break;
          // This is unnessecary, but maybe I'll add a new fearture which
          // allows im/exporting of logfiles, too...
        case 'log':
          $table = self::$TABLE_LOG;
          break;
        default;
        break;
      }

      if ($overwrite == TRUE) {
        // First we have to delete all langauges that are already existing,
        // because we want to overwrite them.
        foreach ($importLangs as $lang) {
          $this->db->execute(sprintf('DELETE FROM %s WHERE lang=\'%s\'', $table, $lang));
        }
        foreach ($csv as $row) {
          // We do only import selected languages
          if (in_array($row['lang'], $importLangs)) {
            $values = implode('\', \'', $row);
            $statement = $this->db->prepare(sprintf('INSERT INTO %s (%s)
                               VALUES (\'%s\')', $table, $colnames, $values));
            $statement->excute();
            $insertedRows += $statement->__get('affectedRows');
          }
        }
      } else {
        foreach ($csv as $row) {
          // Look for existing rows
          switch ($tag) {
            case 'categories':
              $compare = array('lang');
              break;
            case 'tokens':
              $compare = array('token', 'lang');
              break;
            case 'specialtokens':
              $compare = array('token', 'type', 'lang');
              break;
              // This is not nessecary yet, because we don't import logfiles, yet.
            case 'log':
              $compare = array();
              break;
            default:
              $compare = array();
          }
          $where = array();
          foreach ($row as $key=>$value) {
            if (in_array($cols[$key], $compare)) {
              $where[] = $cols[$key].' = \''.$value.'\'';
            }
          }
          $where = implode(' AND ', $where);
          if ($data = $this->db->execute(sprintf('SELECT * FROM %s WHERE %s', $table, $where))->fetchAssoc()) {
            $set = array();
            foreach ($row as $key=>$value) {
              if (!in_array($cols[$key], $compare)) {
                $set[] = '`'.$cols[$key].'` = \''.($data[$cols[$key]] + $value).'\'';
              }
            }
            if (! empty($set)) {
              $set = implode(', ', $set);
              $statement = $this->db->prepare(sprintf('UPDATE %s
                                SET %s
                                WHERE %s', $table, $set, $where));
              $statement->execute();
              $updatedRows += $statement->__get('affectedRows');
            } else {
              $notModifiedRows += 1;
            }
          } else {
            $values = implode('\', \'', $row);
            $statement = $this->db->prepare(sprintf('INSERT INTO %s (%s)
                               VALUES (\'%s\')', $table, $colnames, $values));
            $statement->execute();
            $insertedRows += $statement->__get('affectedRows');
          }
        }
      }
    }
    return array('inserted'=>$insertedRows, 'updated'=>$updatedRows, 'notmodified'=>$notModifiedRows);
  }

  public function getExportFileInfos($file, $fieldDelimitor = '"') {
    // Does this file exist?
    if (strtolower(substr($file, -3)) != '.tb') {
      $file .= '.tb';
    }
    if (!file_exists($file)) {
      return FALSE;
    }

    $fp = fopen($file, 'r');
    flock($fp, 1);
    // Find Info
    $infotags = array('description', 'author', 'date', 'file', 'lang');
    $infodata = array();
    foreach ($infotags as $tag) {
      $startOfLine = '&'.$tag.' = ';
      while ($line = fgets($fp)) {
        if (0 === strpos($line, $startOfLine)) {
          // We found the line
          $value = trim(substr($line, strlen($startOfLine)));
          $infodata[$tag] = $this->__unquoteForImport($value, $fieldDelimitor);
          break;
        }
      }
      rewind($fp);
    }
    flock($fp, 3);
    fclose($fp);

    return $infodata;
  }

  /**
   * Reset the filter and remove training data of one language
   *
   * @access public
   * @param mixed $lang string or array of ISO language codes
   * @param boolean $createBackup
   * @return boolean
   */
  public function resetTrainedLang($lang, $createBackup = TRUE, $filename = NULL) {
    if (is_array($lang)) {
      $langstring = implode(',', $lang);
    } else {
      $langstring = $lang;
    }
    if ($createBackup == TRUE) {
      if (!$filename) {
        $filename = 'TrashBouncer Backup '.date('Y-m-d H-i-s').' '.$langstring;
      }
      $backup = $this->exportTraining($filename, 'AutoBackup created by TrashBouncer '.self::$VERSION, FALSE, $lang);
    }
    if (is_array($lang)) {
      // Delete Categories
      $deletedCategory = $this->db->execute(sprintf('DELETE FROM %s WHERE %s', self::$TABLE_CATEGORIES, '`lang` in (\''.implode('\', \'', $lang).'\')'));
      // Delete Tokens
      $deletedTokens = $this->db->execute(sprintf('DELETE FROM %s WHERE %s', self::$TABLE_TOKENS, '`lang` in (\''.implode('\', \'', $lang).'\')'));
      // Reset Log entries
      $updatedLogEntries = $this->db->execute(sprintf("UPDATE %s SET `cat`='%s' WHERE `cat` in ('%s', '%s') AND `lang` in ('%s')", self::$TABLE_LOG, self::UNKNOWN, self::HAM, self::SPAM, implode("', '", $lang)));
      
      $result = $this->db->execute(sprintf('UPDATE %s
      SET `token`=\'%s\', `lang`=\'%s\'
      WHERE `token`=\'%s\' AND `lang`=\'%s\' AND `type`=\'%s\'',
    self::$TABLE_SPECIALTOKENS, $newword, $newlang, $oldword, $oldlang, self::IGNOREWORD));
    } else {
      $deletedCategory = $this->db->execute(sprintf('DELETE FROM %s WHERE `lang`=\'%s\'', self::$TABLE_CATEGORIES, $lang));
      $deletedTokens = $this->db->execute(sprintf('DELETE FROM %s WHERE `lang`=\'%s\'', self::$TABLE_TOKENS, $lang));
      $updatedLogEntries = $this->db->execute(sprintf("UPDATE %s SET `cat`='%s' WHERE `cat` in ('%s', '%s') AND `lang`='%s'", self::$TABLE_LOG, self::UNKNOWN, self::HAM, self::SPAM, $lang));
    }
    if ($createBackup == TRUE) {
      return array('backup'=>$backup, 'deleted'=>($deletedCategory AND $deletedTokens AND $updatedLogEntries));
    } else {
      return ($deletedCategory AND $deletedTokens AND $updatedLogEntries);
    }
  }

  /**
   * Reset the filter and remove all data of one language including special tokens and log entries
   *
   * @access public
   * @param mixed $lang
   * @param boolean $createBackup
   * @return boolean
   */
  public function deleteLang($lang, $createBackup = TRUE, $filename = NULL) {
    $resetTrainedLang = $this->resetTrainedLang($lang, $createBackup, $filename);
    if (is_array($lang)) {
      $deletedLog = $this->db->execute(sprintf('DELETE FROM %s WHERE %s', self::$TABLE_LOG, '`lang` in (\''.implode('\', \'', $lang).'\')'));
      $deletedSpecialtokens = $this->db->execute(sprintf('DELETE FROM %s WHERE %s', self::$TABLE_SPECIALTOKENS, '`lang` in (\''.implode('\', \'', $lang).'\')'));
    } else {
      $deletedLog = $this->db->execute(sprintf('DELETE FROM %s WHERE `lang`=\'%s\'', self::$TABLE_LOG, $lang));
      $deletedSpecialtokens = $this->db->execute(sprintf('DELETE FROM %s WHERE `lang`=\'%s\'', self::$TABLE_SPECIALTOKENS, $lang));
    }
    if ($createBackup == TRUE) {
      return array('backup'=>$resetTrainedLang['backup'], 'deleted'=>($resetTrainedLang['deleted'] AND $deletedLog AND $deletedSpecialtokens));
    } else {
      return ($resetTrainedLang AND $deletedLog AND $deletedSpecialtokens);
    }
  }

  /**
   * Escape a string for use in CSV
   *
   * @param string $string
   * @param string $fieldDelimitor
   * @return string
   */
  private function __quoteForExport($string, $fieldDelimitor = '"') {
    $string = str_replace($fieldDelimitor, '\\'.$fieldDelimitor, $string);
    $string = str_replace("\n", '\n', $string);
    $string = str_replace("\t", '\t', $string);
    $string = str_replace("\l", '\l', $string);
    $string = $fieldDelimitor.$string.$fieldDelimitor;

    return $string;
  }

  /**
   * Unescape a CSV string
   *
   * @param string $value
   * @param string $fieldDelimitor
   * @return string
   */
  private function __unquoteForImport($string, $fieldDelimitor = '"') {
    trim($string);
    if ( empty($string)) {
      return $string;
    }

    // Remove Quotes at end and beginning only if the do exist
    $fieldDelimitorLength = strlen($fieldDelimitor);
    if (0 === strpos($string, $fieldDelimitor)) {
      $string = substr($string, $fieldDelimitorLength);
    }
    if (strlen($string) - $fieldDelimitorLength === strpos($string, $fieldDelimitor, strlen($string) - $fieldDelimitorLength)) {
      $string = substr($string, 0, -$fieldDelimitorLength);
    }

    $string = str_replace('\\'.$fieldDelimitor, $fieldDelimitor, $string);
    $string = str_replace('\n', "\n", $string);
    $string = str_replace('\t', "\t", $string);
    $string = str_replace('\l', "\l", $string);

    return $string;
  }

  /**
   * Generate a multiline CSV string from given array
   *
   * @access private
   * @param array $data
   * @param string $fieldSeparator
   * @param string $fieldDelimitor
   * @param string $lineDelimitor
   * @return string
   */
  private function __generateCSVString($data, $fieldSeparator = ',', $fieldDelimitor = '"', $lineDelimitor = "\n") {
    if ($data == FALSE) {
      return FALSE;
    }
    if (is_string($data)) {
      $data = array(array($data));
    }
    $string = '';
    foreach ($data as $value) {
      foreach ($value as $key=>$element) {
        $value[$key] = $this->__quoteForExport($element, $fieldDelimitor);
      }
      $string .= implode($fieldSeparator, $value).$lineDelimitor;
    }
    return trim($string);
  }

  /**
   * Parse a multiline array of a string
   *
   * @param mixed $data string or array
   * @param string $fieldSeparator
   * @param string $fieldDelimitor
   * @param string $lineDelimitor
   * @return array
   */
  private function __parseCSVString($data, $fieldSeparator = ',', $fieldDelimitor = '"', $lineDelimitor = "\n") {
    if (is_string($data)) {
      $data = explode($lineDelimitor, $data);
    }
    $result = array();
    foreach ($data as $line) {
      $fieldDelimitorLength = strlen($fieldDelimitor);
      $line = substr(trim($line), $fieldDelimitorLength, -$fieldDelimitorLength);
      $parts = explode($fieldDelimitor.$fieldSeparator.$fieldDelimitor, $line);
      foreach ($parts as $key=>$value) {
        $parts[$key] = $this->__unquoteForImport($value, $fieldDelimitor);
      }
      $result[] = $parts;
    }
    return $result;
  }

}

?>