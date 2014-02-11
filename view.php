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

$salary = new CSalary();

$salary->load($_GET['salary_id']);

if (w2PgetParam($_POST, 'paid', '') != '') {
  $salary->paid_at = date("Y-m-d H:i:s");
  $salary->store();
}



$titleBlock = new w2p_Theme_TitleBlock( 'Salary ' . $salary->salar_title, 'colored_folder.png', $m, "$m.$a" );
$titleBlock->show() ;

?>
<table border="0" width="100%" cellspacing="1" cellpadding="2" class="tbl">
<tr>
        <th width="1%"></th>
        <th width="39%">Task name</th>
        <th width="15%">Assigned percent</th>
        <th width="15%">Target Budget</th>
        <th width="15%">Salary</th>
        <th width="15%">Worker FA</th>

</tr> 

<?php

$salary->show_salary_tasks();

?>

<tr><td></td><td align="right"><b>Note</b></td><td colspan=4><?php echo $salary->salary_note; ?></td></tr>

</table>

