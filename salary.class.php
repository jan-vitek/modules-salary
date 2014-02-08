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

    public function delete(w2p_Core_CAppUI $AppUI)
    {
        $this->load();
        return $this->store($AppUI);
    }
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
        echo "<tr>\n<td valign=\"top\">";
        $s = "<a href=\"?m=salary&a=view&salary_id=" . $this->salary_id ;
        $s .= "\" >" . $this->salary_title . "</a></td>" ;
        echo $s ;
        #echo "<td valign=\"top\">" . $this->amount . "</td>" ;
        echo "<td valign=\"top\">" . $this->get_salary_sum() . "</td>" ;
        echo "<td valign=\"top\">" . $this->tax . "</td>" ;
        $paid = $this->paid_at ? $this->paid_at : "not paid yet";
        echo "<td valign=\"top\">" . $paid . "</td>" ;
        
        echo "</tr>\n" ;
    }

   public function select_salaries ()
   { 
     global $AppUI;
     include ('config.php');
     $q = new w2p_Database_Query();
     $q->addTable('salaries');
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
     $where .= '(t.task_target_budget > 0) AND ';
     //$where .= '(v.value_charvalue IS NULL OR v.value_charvalue = "")';
     //$where .= '(s.user_id != ' . $AppUI->user_id . ')';
     $where .= '(t.task_id NOT IN (' . $paid_query->prepareSelect() . '))';


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


   public function build_tasks_table ($res, $show_checkboxes = false, $checked_FA = false)
   {
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
         } elseif($SALARY_ACCOUNTING_USERS[$AppUI->user_id] != '1') {
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

  public function user_select(){
    global $AppUI;
    $q = new w2p_Database_Query();
    $q->addTable('users');
    $res = $q->exec();
      if (!$res) {
                $AppUI->setMsg(db_error(), UI_MSG_ERROR);
                $q->clear();
                $AppUI->redirect();
     }

    echo '<select name="users" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">';
    echo '<option value="">Create salary for other user</option>';
    while ($row = db_fetch_assoc($res)) {
      echo '<option value="?m=salary&amp;a=addedit&amp;user_id=' . $row[user_id] . '">' . $row[user_username] . "</option>";
    }
    echo '</select>';
  }


}
