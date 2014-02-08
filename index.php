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

$titleBlock = new w2p_Theme_TitleBlock( 'Salaries', 'colored_folder.png', $m, "$m.$a" );

include ('config.php');
if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
  $titleBlock->addCell( $salary->user_select() );
}

$titleBlock->addCell(
                	'<form action="?m=salary&amp;a=addedit" method="post">
                        	<input type="submit" class="button" value="'.$AppUI->_('new salary').'" />
                        </form>', '',   '', '');
$titleBlock->show() ;

?>
<table border="0" width="100%" cellspacing="1" cellpadding="2" class="tbl">
<tr>
        <th width="25%">Salary name</th>
        <th width="25%">Amount</th>
	<th width="25%">Tax</th>
	<th width="25%">Paid on</th> 
</tr> 

<?php
$all_salaries = $salary->select_salaries();
if ( count($all_salaries)) 
{
	foreach ( $all_salaries as $sal_id ){
        	$sal = new CSalary();
                $sal->load($sal_id);
        	$sal->show_salary();
    	}
}
?>


</table>

