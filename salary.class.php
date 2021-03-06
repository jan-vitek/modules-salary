<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR'))
{
  die('You should not access this file directly.');
}
##
## Salary Class
##

//the following code cannot be inside a class due to w2p autoloader


class CSalary extends w2p_Core_BaseObject
{
    public $salary_id = NULL;
    public $salary_title = NULL;
    public $user_id = NULL;
    public $amount = NULL;
    public $tax = NULL;
    public $payment_type_id = NULL;
    public $created_at = NULL;
    public $paid_at = NULL;
    public $salary_note = NULL;

    public $_tbl = 'salaries';
    public $_tbl_key = 'salary_id';


	public function __construct()
	{
		parent::__construct('salaries', 'salary_id');
	}

	public function check()
	{
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        return $errorArray;
	}

/*
    public function delete(w2p_Core_CAppUI $AppUI)
    {
        $this->load();
        return $this->store($AppUI);
    }
*/
/*
    public function store(w2p_Core_CAppUI $AppUI)
    {
        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();
        if (count($errorMsgArray) > 0) {
          return $errorMsgArray;
        }
        $q = new w2p_Database_Query;
		$this->w2PTrimAll();

        if ($this->salary_id && $perms->checkModuleItem('salary', 'edit', $this->todo_id)) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->salary_id && $perms->checkModuleItem('salary', 'add')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
    }
*/

    public function get_salary_sum ()
    {
      global $AppUI;
      $q = new w2p_Database_Query();
      $query_string = 'SUM(u.perc_assignment * t.task_target_budget / 100)';
      $query_string = 'ROUND(' . $query_string . ', 2)';
      $q->addQuery($query_string);
      $q->addTable('salaries_tasks', 'st');
      $q->addJoin('custom_fields_values', 'v', '(v.value_object_id = st.task_id) and v.value_field_id = 3', 'left');
      $q->addJoin('tasks', 't',  'st.task_id = t.task_id', 'inner');
      $q->addJoin('user_tasks', 'u',  't.task_id = u.task_id', 'inner');
      $q->addWhere("st.salary_id = " . $this->salary_id . " AND u.user_id = " . $this->user_id );
      $res = $q->exec();
      while($row = db_fetch_assoc($res)){ 
        return $row[0];
      }
    }    

    public function show_salary( ) 
    {
        global $AppUI ;
        include ('config.php');
          if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
            $enable_delete = true;
          }
        echo "<tr>\n<td valign=\"top\">";
        $s = "";
        $enable_delete ? $s .= '<a style="color:red" href="?m=salary&a=delete&salary_id=' . $this->salary_id . '">' . "delete " . "</a>" : NULL ;
        $s .= "<a href=\"?m=salary&a=view&salary_id=" . $this->salary_id ;
        $s .= "\" >" . $this->salary_title . "</a></td>" ;
        echo $s ;
        #echo "<td valign=\"top\">" . $this->amount . "</td>" ;
        echo "<td valign=\"top\">" . $this->get_salary_sum() . "</td>" ;
        echo "<td valign=\"top\">" . $this->tax . "</td>" ;
        $paid = $this->paid_at ? $this->paid_at : "not paid yet";
        echo "<td valign=\"top\">" . $paid . "</td>" ;
        
        echo "</tr>\n" ;
    }

   public function select_salaries ($filter_user_id = NULL)
   { 
     global $AppUI;
     include ('config.php');
     $q = new w2p_Database_Query();
     $q->addTable('salaries');
     if($filter_user_id != NULL) {
       $where = "user_id = " . $filter_user_id ;
       $q->addWhere( "(" . $where . ")" );
     }
     if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] != '1') {
       $where = "user_id = " . $AppUI->user_id ;
       $q->addWhere( "(" . $where . ")" );
     }
     return $q->loadColumn();
   }

   public function select_user_tasks()
   {
     global $AppUI;
     $q = new w2p_Database_Query();
     $q->addTable('tasks', 't');
     $q->addJoin('users', 'u',  't.task_id = u.task_id', 'inner');
     $q->addWhere('u.user_id = ' . $AppUI->user_id);
     return $q->loadColumn();
   }

   public function show_user_tasks($user_id, $checked_FA)
   {
     global $AppUI;
     include ('config.php'); 
     $paid_query = new w2p_Database_Query();
     $paid_query->addQuery('st1.task_id');
     $paid_query->addTable('salaries', 's1');
     $paid_query->addJoin('salaries_tasks', 'st1', 's1.salary_id = st1.salary_id', 'inner');
     $paid_query->addWhere('(s1.user_id = ' . $user_id . ')');


     $q = new w2p_Database_Query();
     $q->addTable('tasks', 't');
     $q->addJoin('custom_fields_values', 'v', '(v.value_object_id = t.task_id) and v.value_field_id = 3', 'left');
     $q->addJoin('salaries_tasks', 'st', 't.task_id = st.task_id', 'left');
     $q->addJoin('salaries', 's', 'st.salary_id = s.salary_id', 'left');
     $q->addJoin('user_tasks', 'u',  't.task_id = u.task_id', 'inner');
     $where = '(u.user_id = ' . $user_id . ') AND ';
     $where .= '(t.task_target_budget != 0) AND ';
     $where .= '(t.task_percent_complete >= ' . $PERCENT_DONE . ') AND';
     //$where .= '(v.value_charvalue IS NULL OR v.value_charvalue = "")';
     //$where .= '(s.user_id != ' . $AppUI->user_id . ')';
     $where .= '(t.task_id NOT IN (' . $paid_query->prepareSelect() . '))';
     $where .= ' GROUP BY t.task_id';


     $q->addWhere($where);
     $res = $q->exec();
             if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }
     $this->build_tasks_table($res, true, $checked_FA);
   }

   public function show_salary_tasks()
   {
      global $AppUI;
      $q = new w2p_Database_Query();
      $q->addTable('salaries_tasks', 'st');
      $q->addJoin('custom_fields_values', 'v', '(v.value_object_id = st.task_id) and v.value_field_id = 3', 'left');
      $q->addJoin('tasks', 't',  'st.task_id = t.task_id', 'inner');
      $q->addJoin('user_tasks', 'u',  't.task_id = u.task_id', 'inner');
      $q->addWhere("st.salary_id = " . $this->salary_id . " AND u.user_id = " . $this->user_id );
      $res = $q->exec();
      if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }
     $this->build_tasks_table($res);
   }

   public function show_salary_files()
   {
     global $AppUI;
     $q = new w2p_Database_Query();
     $q->addTable('salaries_files', 'sf');
     $q->addWhere("sf.salary_id = " . $this->salary_id);
     $res = $q->exec();
     if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }
     $this->build_files_table($res);
   }


   public function build_tasks_table ($res, $show_checkboxes = false, $checked_FA = false)
   {
       global $AppUI;
       include ('config.php');
       while ($row = db_fetch_assoc($res)) {
       echo "<tr>";
       echo "<td valign=\"top\">" ;
       if ($show_checkboxes) echo '<input type="checkbox"' . (($row[value_charvalue] != "" && $checked_FA) ? '  checked="checked"' : '') . ' name=task[' . $row[task_id] . "] value=" . $row[task_id] . ' onclick="UpdateTotalSalary(this, ' . $row[perc_assignment] * $row[task_target_budget] / 100 . ')">' ;
       echo "</td>" ;
       echo "<td valign=\"top\">";
       $s = "<a href=\"?m=tasks&a=view&task_id=" . $row[task_id];
       $s .= "\" >" . $row[task_name] . "</a></td>" ;
       echo $s ;
       echo "<td valign=\"top\">" . $row[perc_assignment] . "</td>" ;
       echo "<td valign=\"top\">" . $row[task_target_budget] . "</td>" ;
       echo "<td valign=\"top\">" . $row[perc_assignment] * $row[task_target_budget] / 100 . "</td>" ;
       echo "<td valign=\"top\">" . $row[value_charvalue] . "</td></tr>" ;
       //echo "<td valign=\"top\">" . $row[salary_id] . "</td></tr>" ;

     }
     
       echo " <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><h4 id=\"salary_sum\" style=\"color: green\">" . ($show_checkboxes ? "0" : $this->get_salary_sum()) . "<h4></td>";
       
      
       if(!$show_checkboxes){
         if($this->paid_at){
           echo "<td>Paid on " . date("d.m.Y", strtotime($this->paid_at)) . "</td></tr>";
         } elseif($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
           ?>
           <form name="frmAddEdit" action="?m=salary&a=view&salary_id=<?php echo (string)$this->salary_id; ?>" method="post" >
             <td align="center"><input type="submit" value="Confirm payment" style="width:100%;" name="paid"></td></tr>
           </form>  
           <?php
         } else {
           echo "<td>Not paid yet</td></tr>";
         }
       } else {
         echo "<td></td></tr>";
       }

   }

  public function build_files_table($res){
    global $AppUI;
    while ($row = db_fetch_assoc($res)) {
      echo "<tr>";
      echo "<td></td>";
      echo "<td colspan=5>";
      echo "<a href=\"?m=salary&a=filedownloader&file=" . $row[0] . "\">" . $row[2] . "</a>";
      echo "</td>";
      echo "</tr>";
    }  
  }


  public function store()
  {
    if( $this->salary_id ) {
            $q = new w2p_Database_Query;
            $ret = $q->updateObject( 'salaries', $this, 'salary_id' );
            $q->clear();
        } else {
            $q = new w2p_Database_Query;
            $ret = $q->insertObject( 'salaries', $this, 'salary_id' );
            $q->clear();
        }
     if( !$ret ) {
         return get_class( $this )."::store failed <br />" . db_error();
     } else {
         return NULL;
     }
  }

  public function add_task($task_id)
  {
    $q = new w2p_Database_Query;
    $q->addTable('salaries_tasks');
    $q->addInsert('salary_id', $this->salary_id);
    $q->addInsert('task_id', $task_id);
    $q->exec();
  }

  public function add_file($file_name, $file_type, $file_size)
  {
    $q = new w2p_Database_Query;
    $q->addTable('salaries_files');
    $q->addInsert('salary_id', $this->salary_id);
    $q->addInsert('file_name', $file_name);
    $q->addInsert('file_type', $file_type);
    $q->addInsert('file_size', $file_size);
    $q->exec();
  }

  public function user_select($action){
    global $AppUI;
    $q = new w2p_Database_Query();
    $q->addTable('users');
    $res = $q->exec();
      if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }
    switch($action){
      case "addedit":
        $field_title = "Create salary for another user";
        break;
      case "index":
        $field_title = "Filter by user";
        break;
    }    

    echo '<select name="users" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">';
    echo '<option value="">' . $field_title . '</option>';
    while ($row = db_fetch_assoc($res)) {
      echo '<option value="?m=salary&amp;a=' . $action . '&amp;user_id=' . $row[user_id] . '">' . $row[user_username] . "</option>";
    }
    echo '</select>';
  }




  public function email_notification()
    {
        $mail = new w2p_Utilities_Mail();
        $mail->Subject("New salary: " . $this->salary_title, $this->_locale_char_set);

        $mail->Body('Your new salary is accessible here: "' . W2P_BASE_URL . "/index.php?m=salary&a=view&salary_id=" . $this->salary_id , isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

        $users = $this->user_id . ",";
        include ('config.php');
        foreach ($SALARY_ACCOUNTING_USERS as $key => $value){
          if($key != $this->user_id){
            $users .= $key . ",";
          }
        }
        $users = rtrim($users, ",");

        global $AppUI;
        $q = new w2p_Database_Query();
        $q->addTable('users');
        $q->addWhere("user_id IN (" . $users . ")" );
        $res = $q->exec();
        if (!$res) {
              $AppUI->setMsg(db_error(), UI_MSG_ERROR);
              $q->clear();
              $AppUI->redirect();
        }  
        while ($row = db_fetch_assoc($res)) {
          if ($mail->ValidEmail($row['user_username'])) {
              $mail->To($row['user_username'], true);
              $mail->Send();
          }
        }
        return '';
    }

    public function resolve_username($user_id)
      {
        global $AppUI;
        $q = new w2p_Database_Query();
        $q->addTable('users');
        $q->addWhere("user_id = " . ($user_id != NULL ? $user_id : $AppUI->user_id) );
        $res = $q->exec();
        if (!$res) {
              $AppUI->setMsg(db_error(), UI_MSG_ERROR);
              $q->clear();
              $AppUI->redirect();
        }
        while ($row = db_fetch_assoc($res)) {
          return $row['user_username'];
        }
      }


    public function delete()
      {
        $q = new w2p_Database_Query();
        $q->setDelete('salaries_tasks');
        $q->addWhere('salary_id = ' . $this->salary_id);
        $res = $q->exec();
        
        if($res){
          $q = new w2p_Database_Query();
          $q->setDelete('salaries');
          $q->addWhere('salary_id = ' . $this->salary_id);
          $res = $q->exec();
          if($res){
            $q = new w2p_Database_Query();
            $q->setDelete('salaries_files');
            $q->addWhere('salary_id = ' . $this->salary_id);
            $res = $q->exec();
            if($res){ 
              $target_path = W2P_BASE_DIR . "/modules/salary/attachments/" . $this->salary_id . "-";
              foreach (glob($target_path."*") as $filename) {
                unlink($filename);
              }
            }
          }
        }
        if(!$res) {
          $AppUI->setMsg(db_error(), UI_MSG_ERROR);
          $q->clear();
          $AppUI->redirect();
        }

      }

    public function after_paid_actions(){
      $q = new w2p_Database_Query();
      $q->addTable('salaries_tasks');
      $q->addWhere("salary_id = " . $this->salary_id);
      $res = $q->exec();
      while($row = db_fetch_assoc($res)){
        //set worker_fa field
        $q = new w2p_Database_Query();
        $q->addTable('custom_fields_values');
        $q->addUpdate('value_charvalue', 'concat(value_charvalue, "'.$this->resolve_username($this->user_id).'-'.$this->salary_id.' ")', false, true);
        $q->addWhere('value_object_id = '. $row[task_id]);
        $q->addWhere('value_field_id = 3');
        $q->exec();
        //set task %
        $q = new w2p_Database_Query();
        $q->addTable('tasks');
        $q->addUpdate('task_percent_complete', 100);
        $q->addWhere('task_id ='. $row[task_id] );
        $q->exec();
      }

    }
}
