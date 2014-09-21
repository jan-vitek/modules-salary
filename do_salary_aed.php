<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI;

$user_id = $AppUI->user_id;
include ('config.php');

if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
  if (w2PgetParam($_GET, 'user_id', '') != '') {
    $user_id = $_GET['user_id'];
  }
}
$salary_id = $_GET['salary_id'];



$salary = new CSalary();
$salary->bind( $_POST );

if (!$salary_id){
  $q = new w2p_Database_Query;
  $q->addTable('users');
  $q->addWhere('user_id = ' . $user_id);
  $res = $q->exec();

  if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }

  $user_name = '';

  while ($row = db_fetch_assoc($res)) {
	$user_name = $row[user_username];
  }

  $salary->created_at = date("Y-m-d H:i:s");
  $salary->salary_title = $user_name . "-" . date("Y-m-d H:i:s");
  $salary->user_id = $user_id; 
  $salary->store();


  $checkboxes = isset($_POST['task']) ? $_POST['task'] : array();
  foreach($checkboxes as $value) {
    $salary->add_task($value);
  }

  if ($NEW_SALARY_NOTIFY){
    $salary->email_notification();
  }
}

$target_path = W2P_BASE_DIR . "/modules/salary/attachments/" . $salary->salary_id . "-";


function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}


    $file_ary = reArrayFiles($_FILES['attachment']);

    foreach ($file_ary as $file) {
    $file_name = $salary->salary_id . "-" . $file['name'];
    $target_file_path = $target_path . $file_name;
    if(move_uploaded_file($file['tmp_name'], $target_file_path)) {
        $salary->add_file($file_name, $file['type'], $file['size']);
      } else{
        error_log("There was an error uploading a file, please try again!");
      }
    }  

if ($salary_id) {
  $success = 'm=salary&a=view&salary_id=' . $salary_id;
} else {
  $success = 'm=salary';
}
$AppUI->redirect($success);
