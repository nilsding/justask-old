<?php 
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */
include('fixDir.php');
session_start();
if (isset($_GET['p'])) {
  $page = $_GET['p'];
} else {
  $page = "start";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>justask Installer</title>
</head>
<body>
<h1>justask Installer</h1>
<?php switch($page) { 
case "start":
?>
<form action="install.php">
<h2>Welcome to the justask installer!</h2>
<p>By following the next steps, you will get a generated config.php file which you have to create yourself.</p>
<input type="hidden" name="p" value="justask_values">
<button>Next step</button>
</form>
<?php break;
case "justask_values":
?>
<form action="install.php">
<h2>justask values</h2>
<p>Please provide a few values for justask.</p>
<table>
<tr>
<td><label for="jak_name">Name:</label></td>
<td><input type="text" name="jak_name" value="just ask me"></td>
<td class="info">This is the name used along the site.</td>
</tr>
<tr>
<td><label for="jak_entriesperpage">Max. entries per page:</label></td>
<td><input type="number" name="jak_entriesperpage" value="10"></td>
<td class="info">How many questions/answers will be shown on each page?</td>
</tr>
<tr>
<td><label for="jak_gravatar">Enable Gravatar:</label></td>
<td><input type="checkbox" name="jak_gravatar" checked></td>
<td class="info">Allow people to use their Gravatar email address as a profile picture.</td>
</tr>
<tr>
<td><label for="jak_twitter_on">Enable Twitter:</label></td>
<td><input type="checkbox" name="jak_twitter_on" checked></td>
<td class="info">Tweets a tweet automatically to Twitter. Configuring happens later in the UCP.</td>
</tr>
<tr>
<td><label for="jak_anonymous_questions">Allow anonymous questions?</label></td>
<td><input type="checkbox" name="jak_anonymous_questions" checked></td>
<td class="info">Allow people to ask you anonymous questions</td>
</tr>
</table>
<input type="hidden" name="p" value="mysql">
<button>Next step</button>
</form>
<?php break;
case "mysql": 
if (!isset($_GET['jak_gravatar'])) {
  $_SESSION['jak_gravatar'] = false;
} else {
  $_SESSION['jak_gravatar'] = true;
}
if (!isset($_GET['jak_anonymous_questions'])) {
  $_SESSION['jak_anonymous_questions'] = false;
} else {
  $_SESSION['jak_anonymous_questions'] = true;
}
if (!isset($_GET['jak_twitter_on'])) {
  $_SESSION['jak_twitter_on'] = false;
} else {
  $_SESSION['jak_twitter_on'] = true;
}
if (!isset($_GET['jak_name'])) { /* who would do this? */
  $_SESSION['jak_name'] = 'An instance of justask without a name';
} else {
  if ($_GET['jak_name'] === '') {
    $_SESSION['jak_name'] = 'An instance of justask without a name';
  } else {
    $_SESSION['jak_name'] = $_GET['jak_name'];
  }
}
if (!isset($_GET['jak_entriesperpage'])) {
  $_SESSION['jak_entriesperpage'] = 10;
} else {
  $_SESSION['jak_entriesperpage'] = $_GET['jak_entriesperpage'];
  if (!is_numeric($_SESSION['jak_entriesperpage'])) { /* ... */
    $_SESSION['jak_entriesperpage'] = 10;
  }
  if ($_SESSION['jak_entriesperpage'] < 1) { /* ............ */
    $_SESSION['jak_entriesperpage'] = 10;
  }
}
?>
<form action="install.php">
<h2>MySQL database connection</h2>
<p>You probably should already know this step from other installations.</p>
<table>
<tr>
<td><label for="mysql_server">Server:</label></td>
<td><input type="text" name="mysql_server" value="localhost"></td>
</tr>
<tr>
<td><label for="mysql_user">User name:</label></td>
<td><input type="text" name="mysql_user" value="user"></td>
</tr>
<tr>
<td><label for="mysql_pass">Password:</label></td>
<td><input type="text" name="mysql_pass" value="password"></td>
</tr>
<tr>
<td><label for="mysql_database">Database name:</label></td>
<td><input type="text" name="mysql_database" value="database"></td>
</tr>
<tr>
<td><label for="mysql_prefix">Table prefix:</label></td>
<td><input type="text" name="mysql_prefix" value="jak_"></td>
</tr>
</table>
<input type="hidden" name="p" value="finish_1">
<button>Next step</button>
</form>
<?php break;
case "finish_1": ?>
<h2>Finalizing (part 1)</h2>
<?php
if (!isset($_SESSION['jak_name']) || !isset($_SESSION['jak_gravatar']) || !isset($_SESSION['jak_anonymous_questions']) || !isset($_SESSION['jak_entriesperpage'])) {
  ?>
<form action="install.php">
<p>Illegal action. Go back to start, do not pass go, do not collect 200$.</p>
<input type="hidden" name="p" value="start">
<button>Okay ._.</button>
</form>
  <?php
}
else if (!isset($_GET['mysql_database']) || !isset($_GET['mysql_pass']) || !isset($_GET['mysql_prefix']) || 
  !isset($_GET['mysql_server']) || !isset($_GET['mysql_user'])) {
  ?>
<form action="install.php">
<p>Some VIVs (Very Important Variables™) are missing.</p>
<input type="hidden" name="p" value="mysql">
<button>Go back</button>
</form>
  <?php
} else if ($_GET['mysql_database'] === '' || $_GET['mysql_pass'] === '' || $_GET['mysql_prefix'] === '' ||
  $_GET['mysql_server'] === '' || $_GET['mysql_user'] === '') {
  ?>
<form action="install.php">
<p>Some VIVs (Very Important Variables™) are empty!</p>
<input type="hidden" name="p" value="mysql">
<button>Go back</button>
</form>
  <?php } else { ?>
<form action="install.php">
<p>Generating config.php file...</p>
<?php 
date_default_timezone_set('UTC');
$content = "<?php\n# justask configfile\n# generated by install.php\n# " . date("D M j G:i:s T Y") . "\n\n";
// $content .= "\$JAK_NAME = \"" . $_SESSION['jak_name'] . "\";\n\n"; // stored in database 
$content .= "\$MYSQL_SERVER = \"" . $_GET['mysql_server'] . "\";\n";
$content .= "\$MYSQL_USER = \"" . $_GET['mysql_user'] . "\";\n";
$content .= "\$MYSQL_PASS = \"" . $_GET['mysql_pass'] . "\";\n";
$content .= "\$MYSQL_DATABASE = \"" . $_GET['mysql_database'] . "\";\n";
$content .= "\$MYSQL_TABLE_PREFIX = \"" . $_GET['mysql_prefix'] . "\";\n";

if(file_put_contents('./config.php', $content)) {
?>
<p>We've created your config file. You are almost ready to go.</p>
<?php
} else {
?>
<textarea rows="10" cols="60">
<?php echo $content; ?>
</textarea>
<p>Please copy and paste the contents of the above box into a new file which is named config.php.<br />
If your config-file would've been writable, we'd done this for you.</p>
<?php
}
?>
<input type="hidden" name="p" value="finish_2">
<button>Next step</button>
</form>
<?php }
break;
case "finish_2": ?>
<h2>Finalizing (part 2)</h2>
<?php if (!file_exists('config.php')) {
  ?>
<p>Where is the config.php file? I can't see one!</p>
  <?php
} else if (!isset($_SESSION['jak_name']) || !isset($_SESSION['jak_gravatar']) || !isset($_SESSION['jak_anonymous_questions'])) {
  ?>
<form action="install.php">
<p>Illegal action. Go back to start, do not pass go, do not collect 200$.</p>
<input type="hidden" name="p" value="start">
<button>Okay ._.</button>
</form>
  <?php
} else {
  include_once 'config.php';
  ?>
  <p>I will now test the database connection.</p>
  <?php
  $sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
  if ($sql->connect_errno) {
    echo "<p>Failed to connect to MySQL: (" . $sql->connect_errno . ") " . $sql->connect_error . "</p>";
    echo "<p>Please check your MySQL user/pass/server/whatever.</p>";
    echo "<p>Oh and please ignore the following errors, if any. Thanks! :3</p>";
  }

  //TODO: change this ALWAYS to the latest version. and don't forget to change the other code.
  $JUSTASK_CONFIG_VERSION = 8;
  
  /* default twitter consumer keys */


  $JUSTASK_TWITTER_CK = "ABr5S6jAB4RQYFYWm5Sq";
  $JUSTASK_TWITTER_CS = "ICM7eKAlu6PSPysQr7Sim0uFT4HoqK7d5asEpW1Qd6";
  $JUSTASK_TWITTER_CALLBACK = "http://" . $_SERVER['HTTP_HOST'] . fixDir() . "/callback.php";
  
  echo "<p>Creating config table...</p>";
  
  $sql_str = 'CREATE TABLE IF NOT EXISTS `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id` varchar(20) COLLATE utf8_unicode_ci ' . 
  'NOT NULL, `config_value` text COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (`config_id`))';
  
  if (!$sql->query($sql_str)) {
    echo "<p>The query <code>$sql_str</code> failed! :(</p>";
    echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
  }
  
  /* store database config version which will be used for a possible upgrade script */
  $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'version\', \'' . $JUSTASK_CONFIG_VERSION . '\');';
  if (!$sql->query($sql_str)) {
    if ($sql->errno != 1062) { // errno 1062 = Duplicate entry 'blah' for key 'PRIMARY'
      echo "<p>The query <code>$sql_str</code> failed! :(</p>";
      echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
    } else { 
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
    }
  }
  
  /* actual config things now */
  $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_sitename\', \'' . $sql->real_escape_string($_SESSION['jak_name']) . '\'), (\'cfg_gravatar\', \'' . ($_SESSION['jak_gravatar'] ? "true" : "false") . 
    '\'), (\'cfg_anon_questions\', \'' . ($_SESSION['jak_anonymous_questions'] ? "true" : "false") . '\'), (\'cfg_max_entries\', \'' . $_SESSION['jak_entriesperpage'] . '\'), (\'cfg_twitter\', \'' . ($_SESSION['jak_twitter_on'] ? "true" : "false") . 
    '\'), (\'cfg_twitter_ck\', \'' . strrev($JUSTASK_TWITTER_CK) . '\'), (\'cfg_twitter_cs\', \'' . strrev($JUSTASK_TWITTER_CS) . '\'), (\'cfg_twitter_at\', \'\'), (\'cfg_twitter_ats\', \'\'), (\'cfg_twitter_callbk\', \'' . $sql->real_escape_string($JUSTASK_TWITTER_CALLBACK) . 
    '\'), (\'cfg_currtheme\', \'classic\'), (\'cfg_twitter_chk\', \'true\');';
  
  if (!$sql->query($sql_str)) {
    if ($sql->errno != 1062) { // errno 1062 = Duplicate entry 'blah' for key 'PRIMARY'
      echo "<p>The query <code>$sql_str</code> failed! :(</p>";
      echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
    } else { 
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($_SESSION['jak_name']) . '\' WHERE `config_id`=\'cfg_sitename\'; ';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($_SESSION['jak_gravatar'] ? "true" : "false") . '\' WHERE `config_id`=\'cfg_gravatar\'; ';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($_SESSION['jak_anonymous_questions'] ? "true" : "false") . '\' WHERE `config_id`=\'cfg_anon_questions\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $_SESSION['jak_entriesperpage'] . '\' WHERE `config_id`=\'cfg_max_entries\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . ($_SESSION['jak_twitter_on'] ? "true" : "false") . '\' WHERE `config_id`=\'cfg_twitter\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . strrev($JUSTASK_TWITTER_CK) . '\' WHERE `config_id`=\'cfg_twitter_ck\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . strrev($JUSTASK_TWITTER_CS) . '\' WHERE `config_id`=\'cfg_twitter_cs\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
      
      $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $sql->real_escape_string($JUSTASK_TWITTER_CALLBACK) . '\' WHERE `config_id`=\'cfg_twitter_callbk\';';
      if (!$sql->query($sql_str)) {
        echo "<p>The query <code>$sql_str</code> failed! :(</p>";
        echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
      }
    } 
  }
  
  echo "<p>Creating default user [username: &quot;user&quot;, password: &quot;password&quot;]...</p>";
  
  $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_username\', \'user\'), (\'cfg_password\', \'' . crypt('password', '$2a$07$ifthisstringhasmorecharactersdoesitmakeitmoresecurequestionmark666$') . '\'), (\'cfg_user_gravatar\', \'noemail@example.com\');'; 

  if (!$sql->query($sql_str)) {
    if ($sql->errno != 1062) { // errno 1062 = Duplicate entry 'blah' for key 'PRIMARY'
      echo "<p>The query <code>$sql_str</code> failed! :(</p>";
      echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
    } else { 
      echo "<p>An user already exists, skipping...</p>";
    }
  }
  
  echo "<p>Creating question inbox table...</p>";
  
  $sql_str = 'CREATE TABLE IF NOT EXISTS `' . $MYSQL_TABLE_PREFIX . 'inbox` (`question_id` int(11) NOT NULL AUTO_INCREMENT, ' . 
    '`question_content` text COLLATE utf8_unicode_ci NOT NULL, `asker_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL, ' .
    '`asker_gravatar` varchar(100) COLLATE utf8_unicode_ci NOT NULL, `asker_private` tinyint(1) NOT NULL, ' .
    '`question_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `asker_id` text COLLATE utf8_unicode_ci NOT NULL, ' .
    'PRIMARY KEY (`question_id`)) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
  
  if (!$sql->query($sql_str)) {
    echo "<p>The query <code>$sql_str</code> failed! :(</p>";
    echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
  }
  
  echo "<p>Creating answered questions table...</p>";
  
  $sql_str = 'CREATE TABLE IF NOT EXISTS `' . $MYSQL_TABLE_PREFIX . 'answers` (`answer_id` int(11) NOT NULL AUTO_INCREMENT, ' . 
    '`question_content` text COLLATE utf8_unicode_ci NOT NULL, `asker_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL, ' . 
    '`asker_gravatar` varchar(100) COLLATE utf8_unicode_ci NOT NULL, `asker_private` tinyint(1) NOT NULL, `question_timestamp` ' . 
    'datetime NOT NULL, `answer_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`answer_text` text COLLATE utf8_unicode_ci NOT NULL, ' . 
    '`asker_id` text COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (`answer_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ' .
    'AUTO_INCREMENT=1 ;';
    
  if (!$sql->query($sql_str)) {
    echo "<p>The query <code>$sql_str</code> failed! :(</p>";
    echo "<p>The error was: (" . $sql->errno . ") " . $sql->error . "</p>";
  }
  
  ?>
  <p>If no errors occurred, installation is almost complete! You may now want to delete the install.php file, as it's not needed anymore, and head over to <a href="ucp.php">ucp</a>, the main control panel (and for now your inbox).</p>
  <?php
}
?>
<?php  
break;
case 'config_already_exists': 
include_once 'config.php';
$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
?>
<p>The <code>config.php</code> file already exists.</p> 
<?php
if ($sql->connect_errno) {
  echo "<p>However, the connection to the database could not be made because of reasons.</p>";
  echo "<p>The error was <strong>". $sql->connect_error ."</strong> (" . $sql->connect_errno . ") </p>";
  echo "<p>Please edit the config.php file again or delete it and run the installer again.</p>";
} else {
  echo "<p>The database connection seems to work, too.</p>";
}
?>
<?php
break; } ?>
<hr />
<div class="footer">
<p style="font-size: small;"><?php echo (isset($_SESSION['jak_name']) ? $_SESSION['jak_name'] : "This page"); ?> is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html>
