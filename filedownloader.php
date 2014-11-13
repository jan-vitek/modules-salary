<?php



if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

require_once ( $AppUI->getModuleClass( 'salary'));
include ('config.php');

// check permission
$perms =& $AppUI->acl();
if ( ! $perms->checkModule( 'Salary', 'view', $user_id ) ) {
        $AppUI->redirect( "m=public&a=access_denied" );
}

$fileclass = new CFile;

$q = new w2p_Database_Query();
$q->addTable('salaries_files');
$q->addWhere( "salary_file_id = " . $_GET["file"] );
$res = $q->exec();
if($row = db_fetch_assoc($res)){
ob_start();  
  $file=W2P_BASE_DIR . '/modules/salary/attachments/' . $row[2];
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
ob_end_flush();
}
?>
