<?php
/**
 * TrashBouncer - Configuration File
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
 *  @copyright Holger Teichert 2010-2013
 *  @author Holger Teichert <post@complanar.de> 
 *  @package TrashBouncer 0.1.2 Stable 
 *  @link http://www.complanar.de/trashbouncer.html 
 *  @license GNU/LGPL
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

//// Version numbers
define('TRASHBOUNCER_VERSION', 0);
define('TRASHBOUNCER_MINOR_VERSION', 1);
define('TRASHBOUNCER_MAINTENANCE_VERSION', 2);
define('TRASHBOUNCER_STATUS', 'Stable');

//// Database
  // Driver (MySQL|MySQLi|MSSQL|Oracle|PostgreSQL|Sybase)
define('TRASHBOUNCER_DATABASE_driver', 'MySQL');
  // Host (string)
  // Most servers use 'localhost' as database host.
define('TRASHBOUNCER_DATABASE_host', 'localhost');
  // Port (int)
  // Port 3306 is standard on most servers, please edit if your server uses
  // another port.
define('TRASHBOUNCER_DATABASE_port', 3306);
  // Username (string)
  // Please set your username.
define('TRASHBOUNCER_DATABASE_user', '+++USER+++');
  // Password (string)
  // Please set your password.
define('TRASHBOUNCER_DATABASE_pass', '+++PASSWORD+++');
  // Database (string)
  // Please set the database you want to use.
define('TRASHBOUNCER_DATABASE_database', '+++DATABASE+++');
  // Persistent Connection (TRUE|FALSE)
define('TRASHBOUNCER_DATABASE_pconnect', FALSE);
  // Charset (string)
  // If your database understands Unicode let it be UTF8.
define('TRASHBOUNCER_DATABASE_charset', 'UTF8');
  // Collation (string)
  // Must be set corresponding to the charset.
define('TRASHBOUNCER_DATABASE_collation', 'utf8_general_ci');

//// TRASHBOUNCER TABLES
  // Table names (string)
  // Please edit this lines only if you want to use other table names.
define('TRASHBOUNCER_TABLE_tokens', 'trashbouncer_tokens');
define('TRASHBOUNCER_TABLE_categories', 'trashbouncer_categories');
define('TRASHBOUNCER_TABLE_specialtokens', 'trashbouncer_specialtokens');
define('TRASHBOUNCER_TABLE_log', 'trashbouncer_log');

//// TRASHBOUNCER MAIN CONFIGURATION
  // Don't edit these lines if you don't know exactly what you are doing.
  // These settings influence the way of rating texts. Editing these lines may
  // have a huge effect on the outcoming probabilities.
  
  // Name of the lexer class (which splits the text into several tokens. There
  // must be a corresponding file with a corresponding class in the lexer
  // directory.
define('TRASHBOUNCER_CONFIG_lexer', 'default');
  // Name of the degenerator class (which tries to find alternative forms of
  // unknown tokens in the database). There must be a corresponding file with a
  // corresponding class in the degenerator directory.
define('TRASHBOUNCER_CONFIG_degenerator', 'default');
  // Number of relvant tokens to use. The more tokens are evaluated the more
  // exact your results will be. BUT: If you choose this number to high there
  // will be a rounding error in the PHP memory (because the numbers get to
  // small) and your results will become nonsense.
define('TRASHBOUNCER_CONFIG_useRelevant', 15);
  // Minimal relevance a token has to have to be evaluated. If you mostly do
  // have very short texts, it can be a good idea to choose this number a little
  // smaller. BUT: The smaller this value is, the faster you can suffer from
  // rounding errors.
define('TRASHBOUNCER_CONFIG_minRelevance', 0.2);
  // This is the S-constant of Mr. Robinson
define('TRASHBOUNCER_CONFIG_robS', 0.3);
  // The default probability for unknown tokens. 0.5 is the middle between 0 and
  // 1, so our chance is 50/50. If you find out (by examining the tokens table 
  // in your database after learning a lot of texts) that you have mostly ham 
  // tokens or mostly spam tokens, you can set this value more towards 0 
  // (hammier) or more towards 1 (spammier). This is a kind of 
  // a-priori-assumption a token is ham or spam. I try to be neutral, so I 
  // take 0.5.
define('TRASHBOUNCER_CONFIG_probUnknown', 0.5);

//// TRASHBOUNCER PERFERENCES
  // Logging (TRUE|FALSE)
define('TRASHBOUNCER_PREFS_logEnabled', TRUE);
  // Autolearning
  // Autolarning enabled? (TRUE|FALSE)
define('TRASHBOUNCER_PREFS_autolearnEnabled', TRUE);
  // Autolearn ham texts? (TRUE|FALSE)
define('TRASHBOUNCER_PREFS_autolearnOnHam', FALSE);
  // Autolearn spam texts? (TRUE|FALSE)
define('TRASHBOUNCER_PREFS_autolearnOnSpam', TRUE);
  // Autolearn texts with to much stopwords as spam? (TRUE|FALSE)
define('TRASHBOUNCER_PREFS_autolearnOnStopwords', TRUE);
  // Upper border. Spam texts with probabilities below this value are learned
  // automatically (if enabled). If you get a probability of 1.0 or even above,
  // there went something wrong quite surely. So please choose this value a
  // little bit below 1.0 to prevent miscalculated texts of getting into your
  // database.
define('TRASHBOUNCER_PREFS_autolearnMaxSpamProbability', 0.999);
  // Lower border. Spam texts with probabilities above this value are learned
  // automatically (if enabled).
define('TRASHBOUNCER_PREFS_autolearnMinSpamProbability', 0.9);
  // Upper border. Ham texts with probabilities below this value are learned
  // automatically (if enabled).
define('TRASHBOUNCER_PREFS_autolearnMaxHamProbability', 0.2);
  // Lower border. Spam texts with probabilities above this value are learned
  // automatically (if enabled). If you get a probability of 0 or even below,
  // there went something wrong quite surely. So please choose this value a
  // little bit above 0.
define('TRASHBOUNCER_PREFS_autolearnMinHamProbability', 0.01);
  // Specialtokens
  // Enable counting of stopwords (A kind of blacklist).
define('TRASHBOUNCER_PREFS_stopwordsEnabled', TRUE);
  // If a text contains more than this number of stopwords it is categorized as
  // Spam, no matter what probability it has.
define('TRASHBOUNCER_PREFS_stopwordsMax', 20);
  // Enable ignoring words (whitelist).
define('TRASHBOUNCER_PREFS_ignorewordsEnabled', TRUE);
  // Spam probability pivot point
  // If a texts gets a probability above it is catagorized as spam. Please choose
  // this value very careful, because to high values might let through a number
  // of evil texts and values to low might block innocent texts.
  // My expierience: a value of 0.8 might be a good choice.
define('TRASHBOUNCER_PREFS_pivotPoint', 0.8);

?>