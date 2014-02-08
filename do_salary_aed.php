<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI;

$user_id = $AppUI->user_id;

if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] != '1') {
  if (w2PgetParam($_GET, 'user_id', '') != '') {
    $user_id = $_GET['user_id'];
  }
}


$salary = new CSalary();
$salary->bind( $_POST );

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


$success = 'm=salary';
$AppUI->redirect($success);
