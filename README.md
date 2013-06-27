# TrashBouncer
Copyright Holger Teichert 2010–2013

Thanks very much to Tobias Leupold <tobias.leupold@web.de> and his
b8 spamfilter (version 0.5 from the public svn) from which I borrowed a
lot of code.
I took his algorithm of calculating the spamminess of the text and added
new features of defining ignore words that are excluded from calculating
and/or stopwords that block a text no matter what probability it has.
I didn't like the b8 way of handling the database (because calculating
spamminess and database actions are mixed up) so I changed the overall
design of the spamfilter a little bit.

Please visit http://www.complanar.de/trashbouncer.html for help and more
information.

### Contents
1.  System Requirements
2.  Installation
3.  Usage
4.  Troubleshooting

## 1. System Requirements

-   PHP >= 5
-   Database MySQL, MSSQL, MySQLi, Oracle, PostgreSQL or Sybase 
    and corresponding PHP extensions

## 2. Installation

1.  Please copy all files to where you think suits best.
2.  Please open the file config/config.php in a text editor and edit the
    lines with the database connection information. Please input your user
    name, password and database.
    If you want to change the name of the tables, edit the lines of the 
    TRASHBOUNCER_TABLES section as well.
3.  Create the database and the tables. You can use the file 
    database/tabledef.sql as a reference.
    Please don't forget to change the names of the tables here as well if you
    did change them in the previous step.


## 3. Usage

### 3.1. Basic Usage

#### First Step: Create a new Instance

Please include the file TrashBouncer.php in your code and create an Instance 
of the TrashBouncer Filter:
    
    require_once(TrashBouncer.php);
    $filter = new TrashBouncer();

#### Classify a text

You can classify a text with the help of the method `classify()`. Arguments 
are the text which you want to classify as a string and the language of the 
text as two letter ISO-Code for example "en" or "de".

    $filter->classify($text, $lang);

This method will return an array of the following form:
    
    array {
        ["isSpam"]           => bool  # Guess if this text is spam or not
        ["probability"]      => float # Probability between 0 and 1. 
                                      # The closer to 1 the more sure you 
                                      # can be it is a spam text
        ["stopwords"]        => array # list of stopwords in $text
        ["stopwordscount"]   => int   # number of stopwords in $text
        ["ignorewords"]      => array # list of ignored words in $text
        ["ignorewordscount"] => int   # number of ignored words in $text
        ["tokens"]           => array # list of tokens in $text
        ["tokenscount"]      => int   # number of tokens in $text
        ["smalltokenscount"] => int   # number of very short tokens in $text
        ["largetokenscount"] => int   # number of very long tokens in $text
        ["fataltokenscount"] => int   # number of extremely long tokens in 
                                      # $text
    }

#### Check a text, store it in the log table and learn it
    
If you use the method `check()` you get the results from `classify()` and 
some more action is taken: The text is logged into a log table, from where 
you can use it it later as ham or spam training data and maybe it is learned 
automatically to save you some work. You can turn off the logging and 
autolearning functions in `config/config.php`. To do so please set the 
constants `TRASHBOUNCER_PREFS_logEnabled` and 
`TRASHBOUNCER_PREFS_autolearnEnabled` to `FALSE`.
If you use that way of classifying a text you can give the log entry a 
caption. If you don't do that the caption is set automatically to 
'Spam Log'.

    $filter->check($text, $lang [, $infotext]);

#### Quick check on spam
    
If you only want to know if this text is spam or not you can take the method 
`isSpam()`. It returns a boolean value.

    $filter->isSpam($text, $lang);

It does the same as the `classify()` method but returns only the first value 
of the array.

#### Training the filter
    
The filter can only work at a good rate if you train it first. You have to 
give it a number of spam and ham texts for every language you want to check. 
And on this basis it will guess unknown texts. The more texts you give it to 
eat, the better it will become at guessing. To learn texts use

    $filter->learn($text, $lang, $category);
`$text` and `$lang` are old friends and for `$category` please use the class 
constants `TrashBouncer::HAM` or `TrashBouncer::SPAM`. For example to learn 
a text as spam type:

    $filter->learn('This is SPAM', 'en', TrashBouncer::SPAM);

Of course you can remove texts from the trained filter as well, for example 
if saved it in the wrong category accidently:

    $filter->unlearn('This is SPAM', 'en', TrashBouncer::SPAM);
    
Please be very careful with this option as removing texts which you didn't
learn exactly the same before will break the filter after a while. Then you
will have to start a new training from scratch.

### 3.2. Administrative Tasks

For the following tasks you need the admin class which is a child of our basic
class. Please include TrashBouncerAdmin.php and use the TrashBouncerAdmin class:

    require_once(TrashBouncerAdmin.php);
    $adminFilter = new TrashBouncerAdmin();
  
#### Managing log entries

As said above, you can learn from logEntries and categorize them as ham, spam or
unknown. Because log entries are saved with their language you don't need to
repeat this definition. `$id` can be an integer or an array of integers, 
depending if you want to learn only one or mor entries. For `$category` please 
use `TrashBouncer::HAM`, `TrashBouncer::SPAM` or `TrashBouncer::UNKNOWN`.
  
    $adminFilter->learnLogEntry($id, $category [, $delete]);

The last argument is optional and tells the filter if you want to delete the 
entry from the log table after learning it.

You get the entries with the method getLogEntries() wich will return you an 
array of the last inserted log Entries. You can filter the result in category 
and search for text in the log caption and the saved text.
  
    $adminFilter->getLogEntries($lang [, $offset, $limit, $filterCat, $filter]);
  
Only the first argument is nessecary the others default to:

    $offset = 0:        # How many entries to skip at beginning.
    $limit = 25;        # Limit the result to $limit entries.
    $filterCat = NULL;  # Only return entries of a category. Possible values are the
                        # same as above (TrashBouncer::HAM, ...).
    $filter = NULL;     # Filter entries with a string, which has to be found in at
                        # least one of the cols info_text or text.
                      
If you know the id and you need details of only just one log entry use
   
   $adminFilter->getLogEntry($id);
                      
Of course you can delete log entries, too:

    $adminFilter->delLogEntry($id);

`$id` can be a integer or an array of integers. The second way deletes all log
entries wich fit to the given numbers.


#### Managing Stop- and Ignorewords

You can add, delete and update Stop and Ignorewords for different languages:
  
    $adminFilter->addStopword($word, $lang);
    $adminFilter->delStopword($word, $lang);
    $adminFilter->updateStopword($oldword, $newword, $lang [, $newlang]);

For updating Stopwords the last argument defaults to `NULL`. If you don't want 
to change the language of a word, you can leave it out.
Ignorewords use similar methods called `addIgnoreword()`, `delIgnoreword()` and 
`updateIgnorword()`.


#### Managing training data and backups

Sometimes it can be useful to export training data or save it to a file as 
backup or for later use with another installation of the filter. Therefore you
can use five methods:

- `exportTrainingData($filename [, $commentString, $overwrite, $selectedLang])`
- `importTrainingData($filename [, $overwrite, $selectedLang]);`
- `getExportFileInfos($filename);`
- `resetTrainedLang($lang [, $createBackup, $filename]);`
- `deleteLang($lang [, $createBackup, $filename]);`

I will tell you some details about this methods. `$selectedLang` can be a string
of a two letter ISO language code or an array of such strings, if you want more 
then one language treated.

-   `exportTrainingData($filename [, $commentString, $overwrite, $selectedLang])`
    
    This function saves the content of the categories, tokens, and specialtokens 
    tables to a file and adds some information for later use.
    -   `$filename` has to be a writeable path-/filename
    -   `$commentstring` defaults to '' and can be used to tag the file
    -   `$overwrite` defaults to `FALSE` and tells the method wether to 
        overwrite a file that maybe already exists. If this argument is `FALSE` 
        and the file `$filename` already exists, nothing happens.
    -   `$selectedLang` defaults to `NULL`, which means that all languages are 
        exported. To export only some languages, please set this argument as 
        explained above.

-   `importTrainingData($filename [, $overwrite, $selectedLang])`

    You can import training data, you did export before. If you set $overwrite 
    to `TRUE` all existing data of the database which corresponds to the 
    languages in the export file is deleted and replaced by the data of the 
    export file. Otherwise the data of the file is added to the database. 
    `$overwrite` defaults to `FALSE`.

-   `getExportFileInfos($filename)`
    
    Get a short overview of contained lanugages of an exported file and it's
    author, date and so on. Returns an array of the following form:
  
        array {
            ["description"] => string # description of this file
            ["author"]      => string # author of export
            ["date"]        => string # date of export
            ["file"]        => string # original filename
            ["lang"]        => string # contained languages, useful for later 
                                      # filtering
        }

-   `resetTrainingLang($lang [, $createBackup, $filename])`
-   `deleteLang($lang [, $createBackup, $filename])`
    
    Both methods are provided to reset training data of a specified language.
    `$lang` can be a two letter ISO language code or an array of such. All 
    learned data of these langauges is removed from the database. By default, a 
    backup is created. You don't need to specify a filename, as it is created 
    automatically in the form "TrashBouncer Backup ##date## ##lang###". If you 
    don't want a backup set the second argument to `FALSE`.
    The difference between the two methods is, that the second one removes log
    entries, stopwords and ignorewords, too. No backup is created of these 
    tables.

## 4. Troubleshooting

### I get strange error messages

If you get strange error messages please check your PHP version. This 
software requires PHP 5 or above. It *will not work* with PHP 4!
To find out your PHP version use `phpinfo()` or `phpversion()`.

If the error messages are about not included files, please check the
rights of these files. The folder for your export files has to be writeable,
all other files have to be readable. If you set everything to 0775 you
should not suffer from any problems with that.
**CAUTION:** Some webspace providers allow only 0755 settings for scripts that 
may be called directly (not via including). Please keep this in your mind,
if 0775 does not work.

### Could not load lexer object. File *** not found.

Please check the config file. If you use a custom lexer class you should
make sure the file is at the right location in the "lexer" directory and is 
readable.
The if your lexer is called "mylexer" the file must be named "mylexer.php".

### Could not load lexer object. Class lexer_*** not found.
Please check the name of the lexer class defined in "lexer/***.php"
It must not have the same name as the file. If your lexer file is called 
"mylexer.php" the class should have the name "lexer_mylexer".
    
### Could not load database driver "***".
TrashBouncer brings database drivers for many database systems. But maybe 
you use a system that is not supported. Please check the system requirements
at the beginning of this file.

You have to set the right driver in `config/config.php`. If you have the
driver alright, maybe there is a php extension missing. Please use `phpinfo()` 
or `extension_loaded()` to check for errors.

### Could not connect to database (***).
Obviously your database access data is not correct or the user you use has
not enough rights. Please check your database settings in `config/config.php`.
