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
     $file_handle = fopen("/var/www/zlobr.cz/web/w2p/modules/salary/csvtasks.csv", 'r');
     echo "test" . $file_handle;

     while (!feof($file_handle) ) {
       $line_of_text[] = fgetcsv($file_handle, 0, ';');
     }
     fclose($file_handle);
     

    foreach($line_of_text as $row){
       $q1 = new w2p_Database_Query();
       $q1->addTable('salaries','s');
       $q1->addwhere('s.user_id = ' . $row[0] . ' AND s.salary_note = "' . $row[12] . '"' );
       $salary_id = $q1->loadColumn()['0'];
       $salary = new CSalary();
       $salary->load($salary_id);

       if ($salary->salary_id == NULL){
         $salary->salary_title = $row[6] . "-" . $row[12];
         $salary->user_id = $row[0];
         $salary->created_at = date("Y-m-d H:i:s");
         $salary->salary_note = $row[12];
         $salary->store();
       }       

       $salary->add_task($row[2]);
     }

