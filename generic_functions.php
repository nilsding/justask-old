<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */

include_once 'fixDir.php';
 
/** This function generates a tweet text in the format "question — answer - url", which is less than 140 characters long.
 * @param $sql MySQLi object
 * @param $MYSQL_TABLE_PREFIX Table prefix.
 * @param $answer_id Answer ID. Expected to be numeric, otherwise null will be returned.
 */
function generate_tweet_text(MySQLi $sql, $MYSQL_TABLE_PREFIX, $answer_id) {
  if (!is_numeric($answer_id)) {
    return null;
  }
  $sql_str = "SELECT * FROM `" . $MYSQL_TABLE_PREFIX . "answers` WHERE `answer_id`=" . $answer_id . " LIMIT 1";
  $res = $sql->query($sql_str);
  $res = $res->fetch_assoc();
//   $answer_id = $res['answer_id'];
  $question_content = $res['question_content'];
  $answer = $res['answer_text'];
  $url = 'http';
  if (isset($_SERVER["HTTPS"])) { // does it work better that way?
    $url .= "s";
  }
  $url .= "://";
  if ($_SERVER["SERVER_PORT"] != "80") {
    $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } else {
    $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
#  $url = substr($url, 0, (strlen($url) - strlen($_SERVER['SCRIPT_NAME'])));
  $url = $_SERVER["SERVER_NAME"];
  $url .= fixDir();
  $url .= "/view_answer.php?id=" . $answer_id;
  if (strlen($question_content) > 56) {
    $question_content = substr($question_content, 0, 55) . '…'; 
  }
  if (strlen($answer) > 56) {
    $answer = substr($answer, 0, 55) . '…'; 
  }
  return $question_content . " — " . $answer . ' · ' . $url;
}
