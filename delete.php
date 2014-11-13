<?php  /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

require_once ( $AppUI->getModuleClass( 'salary'));


// check permission
$perms =& $AppUI->acl();
if ( ! $perms->checkModule( 'Salary', 'view', $user_id ) ) {
        $AppUI->redirect( "m=public&a=access_denied" );
}

 global $AppUI;
include ('config.php');
if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
  $salary = new CSalary();
  $salary->load($_GET['salary_id']);
  $salary->delete();
}
$AppUI->redirect(parse_url($_SERVER["HTTP_REFERER"], PHP_URL_QUERY));
