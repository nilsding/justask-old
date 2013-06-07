<?php
if (file_exists('config.php')) {
  require_once('config.php');
} else {
  header('Location: install.php');
  exit();
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
<h1>justask Updater</h1>

<?php
  $sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
  if ($sql->connect_errno) {
    echo "<p>Failed to connect to MySQL: (" . $sql->connect_errno . ") " . $sql->connect_error . "</p>";
    echo "<p>Please check your MySQL user/pass/server/whatever.</p>";
    ?> 
<hr />
<div class="footer">
<p style="font-size: small;">this site is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html> <?php
    exit();
  }
  
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_sitename\'');
$res = $res->fetch_assoc();
$site_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'version\'');
$res = $res->fetch_assoc();
$config_version = $res['config_value'];

switch ($config_version) {
  case 3:
    ?><p>Your config is already up to date.</p><?php
    break;
  default:
    ?><p>upgrading to <strong>r3</strong>...<br /><?php
    /* new config values in config r3:
     * version = config version number
     * cfg_twitter = twitter enabled? [true/false]
     * cfg_twitter_ck = twitter consumer key
     * cfg_twitter_cs = twitter consumer secret
     * cfg_twitter_at = twitter consumer access token
     * cfg_twitter_ats = twitter consumer access token secret
     * cfg_twitter_callbk = twitter callback
     */
     
    $JUSTASK_CONFIG_VERSION = 3;
    $JUSTASK_TWITTER_CK = "ABr5S6jAB4RQYFYWm5Sq";
    $JUSTASK_TWITTER_CS = "ICM7eKAlu6PSPysQr7Sim0uFT4HoqK7d5asEpW1Qd6";
    $JUSTASK_TWITTER_CALLBACK = "http://" . $_SERVER['HTTP_HOST'] . "/callback.php";
    
    echo 'storing version value... ';
    $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'version\', \'' . $JUSTASK_CONFIG_VERSION . '\');';
    if (!$sql->query($sql_str)) {
      if ($sql->errno == 1062) { 
        $sql_str = 'UPDATE `' . $MYSQL_TABLE_PREFIX . 'config` SET `config_value`=\'' . $JUSTASK_CONFIG_VERSION . '\' WHERE `config_id`=\'version\'; ';
        $sql->query($sql_str);
      }
    }
    
    echo 'done<br />upgrading database... ';
    $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'config` (`config_id`, `config_value`) VALUES (\'cfg_twitter\', \'false\'), (\'cfg_twitter_ck\', \'' . 
      strrev($JUSTASK_TWITTER_CK) . '\'), (\'cfg_twitter_cs\', \'' . strrev($JUSTASK_TWITTER_CS) . '\'), (\'cfg_twitter_at\', \'\'), (\'cfg_twitter_ats\', \'' .
      '\'), (\'cfg_twitter_callbk\', \'' . $sql->real_escape_string($JUSTASK_TWITTER_CALLBACK) . '\');';
    if (!$sql->query($sql_str)) {
      echo 'error<br />';
    } else {
      echo 'done<br />';
    }
    echo '<br />Perfect.</p>';
}
?>

<hr />
<div class="footer">
<p style="font-size: small;"><?php echo htmlspecialchars($site_name); ?> is running <a href="https://github.com/nilsding/justask">justask</a>, which is
free software licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License
version 3</a>.</p>
</div>
</body>
</html>