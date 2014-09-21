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
    ob_end_clean();
    header('MIME-Version: 1.0');
    header('Pragma: ');
    header('Cache-Control: public');
    header('Content-length: ' . $row[3]);
    header('Content-type: ' . $row[4]);
    header('Content-transfer-encoding: 8bit');
    header('Content-disposition: attachment; filename="' . $row[2] . '"');

    $fileclass->getFileSystem()->read(W2P_BASE_DIR . '/modules/salary/attachments/' . $row[2]);

    flush();
}
?>
