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
     $q = new w2p_Database_Query();
     $q->addTable('tasks', 't');
     $q->addJoin('custom_fields_values', 'v', '(v.value_object_id = t.task_id) and v.value_field_id = 3', 'left');
     $q->addJoin('user_tasks', 'u',  't.task_id = u.task_id', 'inner');
     $q->addJoin('users', 'uu', 'uu.user_id = u.user_id', 'inner'); 
     $where .= '(v.value_charvalue IS NOT NULL AND v.value_charvalue != "")';
     $q->addWhere($where);
     $res = $q->exec();
             if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }
     while ($row = db_fetch_assoc($res)) {
       $q1 = new w2p_Database_Query();
       $q1->addTable('salaries','s');
       $q1->addwhere('s.user_id = ' . $row['user_id']);
       $salary_id = $q1->loadColumn()['0'];
       $salary = new CSalary();
       $salary->load($salary_id);

       if ($salary->salary_id == NULL){
         $salary->salary_title = $row['user_username'] . "-init";
         $salary->user_id = $row['user_id'];
         $salary->created_at = date("Y-m-d H:i:s");
         $salary->store();
       }       

       $salary->add_task($row['task_id']);
     }

