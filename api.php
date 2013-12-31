<?php
/* 
 * justask
 * © 2013 nilsding
 * License: AGPLv3, read the LICENSE file for the license text.
 */
 
$API_VERSION = 2;
 
$response = array('version' => $API_VERSION, 'success' => false, 'code' => 0, 'message' => '', 'action' => '', 'data' => null);

if (file_exists('config.php')) {
  require_once('config.php');
} else {
  $response['code'] = 501;
  $response['message'] = 'The \'config.php\' file is missing. Please run the installer.';
  echo json_encode($response);
  exit();
}

require_once 'gravatar.php';
require_once 'generic_functions.php';

/* important variables (can be sent through a GET or POST request, whatever you like best)
 * user_name = username
 * api_key = api key
 * action = the action to be done
 *
 * 
 */
if (!isset($_REQUEST['user_name'])) {
  $response['code'] = 401;
  $response['message'] = 'Parameter `user_name` is missing.';
  echo json_encode($response);
  exit();
}
if (!isset($_REQUEST['api_key'])) {
  $response['code'] = 402;
  $response['message'] = 'Parameter `api_key` is missing.';
  echo json_encode($response);
  exit();
}
if (!isset($_REQUEST['action'])) {
  $response['code'] = 403;
  $response['message'] = 'Parameter `action` is missing.';
  echo json_encode($response);
  exit();
}
$response['action'] = $_REQUEST['action'];

$sql = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE);
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_username\'');
$res = $res->fetch_assoc();
$user_name = $res['config_value'];
$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_api_key\'');
$res = $res->fetch_assoc();
$api_key = $res['config_value'];

if ($_REQUEST['user_name'] !== $user_name || $_REQUEST['api_key'] !== $api_key) {
  $response['code'] = 405;
  $response['message'] = 'Wrong user name or API key.';
  echo json_encode($response);
  exit();
}

$res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter\'');
$res = $res->fetch_assoc();
$twitter_on = ($res['config_value'] === "true" ? true : false);

if ($twitter_on) {
  include_once 'include/oauth/twitteroauth.php';
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_ck\'');
  $res = $res->fetch_assoc();
  $twitter_ck = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_cs\'');
  $res = $res->fetch_assoc();
  $twitter_cs = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_at\'');
  $res = $res->fetch_assoc();
  $twitter_at = $res['config_value'];
  $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_ats\'');
  $res = $res->fetch_assoc();
  $twitter_ats = $res['config_value'];
}

$get_all_pages = false;
if (!isset($_REQUEST['page'])) {
  $pagenum = 1;
} else {
  $pagenum = (int) $_REQUEST['page'];
}
if ($pagenum < 1) {
  $pagenum = 10;
  $get_all_pages = true;
}

if (!isset($_REQUEST['max_entries_per_page'])) {
  $max_entries_per_page = 10;
} else {
  $max_entries_per_page = (int) $_REQUEST['max_entries_per_page'];
}
if ($max_entries_per_page < 1) {
  $max_entries_per_page = 10;
}

$since_id = 0;
if (isset($_REQUEST['since_id'])) {
  if (is_numeric($_REQUEST['since_id'])) {
    $since_id = (int) $_REQUEST['since_id'];
  }
}

switch(trim(strtolower($_REQUEST['action']))) {
  case 'info':
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox`');
    $question_count = $res->num_rows;
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers`');
    $answer_count = $res->num_rows;
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_sitename\'');
    $res = $res->fetch_assoc();
    $site_name = $res['config_value'];
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_currtheme\'');
    $res = $res->fetch_assoc();
    $current_theme = $res['config_value'];
//     $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter\'');
//     $res = $res->fetch_assoc();
//     $twitter_on = ($res['config_value'] === "true" ? true : false);
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_gravatar\'');
    $res = $res->fetch_assoc();
    $gravatar = ($res['config_value'] === 'true' ? true : false);
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_anon_questions\'');
    $res = $res->fetch_assoc();
    $anon_questions = ($res['config_value'] === 'true' ? true : false);
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_twitter_chk\'');
    $res = $res->fetch_assoc();
    $twitter_check = ($res['config_value'] === 'true' ? true : false);
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_user_gravatar\'');
    $res = $res->fetch_assoc();
    $user_gravatar_email = $res['config_value'];
    $res = $sql->query('SELECT `config_value` FROM `' . $MYSQL_TABLE_PREFIX . 'config` WHERE `config_id`=\'cfg_show_user_id\'');
    $res = $res->fetch_assoc();
    $show_user_id = ($res['config_value'] === "true" ? true : false);
    
    $response['code'] = 200;
    $response['data'] = array('question_count' => $question_count, 'answer_count' => $answer_count, 'site_name' => $site_name,
                              'current_theme' => $current_theme, 'gravatar' => $gravatar, 'anon_questions' => $anon_questions,
                              'twitter_on' => $twitter_on, 'twitter_check' => $twitter_check, 'user_name' => $user_name, 
                              'user_gravatar_email' => $user_gravatar_email, 'show_user_id' => $show_user_id);
    $response['success'] = true;
    $response['message'] = 'OK';
    
    break;
  case 'get_inbox':
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox`');
    if ($res->num_rows == 0) {
      $response['code'] = 201;
      $response['success'] = true;
      $response['message'] = 'Your inbox is empty';
      echo json_encode($response);
      exit();
    }
    
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE (`question_id` > ' . $since_id . ') ORDER BY `question_timestamp` DESC');
    if ($res->num_rows == 0) {
      $response['code'] = 202;
      $response['success'] = true;
      $response['message'] = 'No new questions.';
      echo json_encode($response);
      exit();
    }
    
    if (!$get_all_pages) {
      $last_page = ceil($res->num_rows / $max_entries_per_page); 
      if ($pagenum > $last_page) {
        $pagenum = $last_page;
      }
      $max_sql = ' LIMIT ' . ($pagenum - 1) * $max_entries_per_page . ',' . $max_entries_per_page; 

      $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE (`question_id` > ' . $since_id . ') ORDER BY `question_timestamp` DESC' . $max_sql);
    }
    
    $inbox = array();
    
    while ($question = $res->fetch_assoc()) {
      $question_time_asked = strtotime($question['question_timestamp']);
      if ($question['asker_private']) {
        $question_asked_by = 'Anonymous';
      } else {
        $question_asked_by = $question['asker_name'];
      }
      array_push($inbox, array("question_asked_by" => $question_asked_by, 
                                  "asker_gravatar" => get_gravatar_url($question['asker_gravatar'], 48),
                             "question_time_asked" => $question_time_asked,
                                "question_content" => $question['question_content'],
                                        "asker_id" => strlen(trim($question['asker_id'])) == 0 ? null : $question['asker_id'],
                                     "question_id" => $question['question_id'],
                                   "asker_private" => (bool) $question['asker_private']));
    }
    
    $response['code'] = 200;
    $response['data'] = $inbox;
    $response['success'] = true;
    $response['message'] = 'OK';
      
    break;
  case 'delete_question':
    if (!isset($_REQUEST['question_id'])) {
      $response['code'] = 407;
      $response['message'] = 'Parameter `question_id` is missing.';
      echo json_encode($response);
      exit();
    }
    
    $question_id = $_REQUEST['question_id'];
    
    if (!is_numeric($question_id)) {
      $response['code'] = 408;
      $response['message'] = '`question_id` is not numeric.';
      echo json_encode($response);
      exit();
    }
    
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    if (!$sql->query($sql_str)) {
      $response['code'] = 500;
      $response['message'] = 'Error while deleting question';
      echo json_encode($response);
      exit();
    }
    
    $response['code'] = 200;
    $response['data'] = $question_id;
    $response['message'] = 'Successfully deleted question.';
    $response['success'] = true;
    
    break;
  case 'get_answers':
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers`');
    if ($res->num_rows == 0) {
      $response['code'] = 201;
      $response['success'] = true;
      $response['message'] = 'You haven\'t answered any questions yet!';
      echo json_encode($response);
      exit();
    }
    
    $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE (`answer_id` > ' . $since_id . ') ORDER BY `answer_timestamp` DESC');
    if ($res->num_rows == 0) {
      $response['code'] = 202;
      $response['success'] = true;
      $response['message'] = 'No new answers.';
      echo json_encode($response);
      exit();
    }
    
    $answers = array();
    
    if (!$get_all_pages) {
      $last_page = ceil($res->num_rows / $max_entries_per_page); 
      if ($pagenum > $last_page) {
        $pagenum = $last_page;
      }
      $max_sql_str_part_thing = ' LIMIT ' . ($pagenum - 1) * $max_entries_per_page . ',' . $max_entries_per_page; 

      $res = $sql->query('SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'answers` WHERE (`answer_id` > ' . $since_id . ') ORDER BY `answer_timestamp` DESC' . $max_sql_str_part_thing);
    }
    
    while ($question = $res->fetch_assoc()) { 
      $question_time_answered = strtotime($question['answer_timestamp']);
      $question_time_asked = strtotime($question['question_timestamp']);
      if ($question['asker_private']) {
        $question_asked_by = 'Anonymous';
      } else {
        $question_asked_by = $question['asker_name'];
      }
      array_push($answers, array("question_asked_by" => $question_asked_by, 
                                    "asker_gravatar" => get_gravatar_url($question['asker_gravatar'], 48),
                                       "answer_text" => $question['answer_text'],
                            "question_time_answered" => $question_time_answered,
                               "question_time_asked" => $question_time_asked,
                                  "question_content" => $question['question_content'],
                                          "asker_id" => strlen(trim($question['asker_id'])) == 0 ? "none" : $question['asker_id'],
                                         "answer_id" => $question['answer_id'],
                                     "asker_private" => (bool) $question['asker_private']));
    }
    
    $response['code'] = 200;
    $response['data'] = $answers;
    $response['success'] = true;
    $response['message'] = 'OK';
    
    break;
  case 'answer_question':
    if (!isset($_REQUEST['question_id'])) {
      $response['code'] = 407;
      $response['message'] = 'Parameter `question_id` is missing.';
      echo json_encode($response);
      exit();
    }
    
    $question_id = $_REQUEST['question_id'];
    
    if (!is_numeric($question_id)) {
      $response['code'] = 408;
      $response['message'] = '`question_id` is not numeric.';
      echo json_encode($response);
      exit();
    }
    
    if (!isset($_REQUEST['answer'])) {
      $response['code'] = 410;
      $response['message'] = 'Parameter `answer` is missing.';
      $response['data'] = $question_id;
      echo json_encode($response);
      exit();
    }
    if (strlen(trim($_REQUEST['answer'])) == 0) {
      $response['code'] = 410;
      $response['message'] = 'The answer is empty.';
      $response['data'] = $question_id;
      echo json_encode($response);
      exit();
    }
    
    $answer = $sql->real_escape_string($_REQUEST['answer']);
    $sql_str = 'SELECT * FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $res = $sql->query($sql_str);
    
    if ($res->num_rows == 0) {
      $response['code'] = 409;
      $response['message'] = 'The question does not exist on this server anymore???';
      echo json_encode($response);
      exit();
    }
    
    $res = $res->fetch_assoc();
    
    $question_content = $sql->real_escape_string($res['question_content']);
    $asker_name = $sql->real_escape_string($res['asker_name']);
    $asker_gravatar = $sql->real_escape_string($res['asker_gravatar']);
    $asker_id = $sql->real_escape_string($res['asker_id']);
    $asker_private = $res['asker_private'];
    $question_timestamp = $res['question_timestamp'];
    
    $sql_str = 'INSERT INTO `' . $MYSQL_TABLE_PREFIX . 'answers` (`question_content`, `asker_name`, ' .
    '`asker_gravatar`, `asker_private`, `question_timestamp`, `answer_text`, `asker_id`) VALUES (\'' . $question_content . 
    '\', \'' . $asker_name . '\', \'' . $asker_gravatar . '\', \'' . $asker_private . '\', \'' . $question_timestamp . 
    '\', \'' . $answer . '\', \'' . $asker_id . '\');';
    
    if (!$sql->query($sql_str)) {
      $response['code'] = 500;
      $response['message'] = 'Error while answering question';
      echo json_encode($response);
      exit();
    }
    $answer_id = $sql->insert_id;
    
    $sql_str = 'DELETE FROM `' . $MYSQL_TABLE_PREFIX . 'inbox` WHERE `question_id`=' . $question_id;
    $sql->query($sql_str);
    
    if ($twitter_on) {
      if (isset($_REQUEST['post_to_twitter'])) {
        $connection = new TwitterOAuth($twitter_ck, $twitter_cs, $twitter_at, $twitter_ats);
        $status = generate_tweet_text($sql, $MYSQL_TABLE_PREFIX, $answer_id);
        $connection->post('statuses/update', array('status' => $status));
      }
    }
    
    $response['code'] = 200;
    $response['success'] = true;
    $response['message'] = 'Question successfully answered.';
    $response['data'] = $question_id;
    
    break;
  default:
    $response['code'] = 406;
    $response['message'] = 'unknown action `' . trim(strtolower($_REQUEST['action'])) . '`';
}

echo json_encode($response);
exit();