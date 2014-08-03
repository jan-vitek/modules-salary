<?php
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

include ('config.php');

$user_id = $AppUI->user_id;

if($SALARY_ACCOUNTING_USERS[$AppUI->user_id] == '1') {
  if (w2PgetParam($_GET, 'user_id', '') != '') {
    $user_id = $_GET['user_id'];
  }
}

?>




<script type="text/javascript">
        
        function UpdateTotalSalary (target, value) {
            var sum_field = document.getElementById("salary_sum")
            var curr_val = parseInt(sum_field.innerHTML)
            var operation_val = parseInt(value)
            if (target.checked) {
                curr_val += operation_val
            }
            else {
                curr_val -= operation_val
            }
            sum_field.innerHTML = curr_val
        }
        
        function addFileRow () {
          var table = document.getElementById("salary_table");

          var rowCount = table.rows.length;
          var row = table.insertRow(rowCount - 3);

          row.insertCell(0);
          var cell1 = row.insertCell(1);
          cell1.setAttribute("colspan", 5);
          var element1 = document.createElement("input");
          element1.type = "file";
          element1.name="attachment[]";
          cell1.appendChild(element1);
        }
</script>

<?php
$salary_id = w2PgetParam($_REQUEST, 'salary_id', 0);

$salary = new CSalary();

if ( $salary_id && !$salary->load( $salary_id )) {
        $AppUI->setMsg( 'Salary' );
        $AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
        $AppUI->redirect();
}

if ( $salary_id ) {
	$titleBlock = new w2p_Theme_TitleBlock( 'Editing salary - ' . $salary.salary_title, 'payment-icon.png', $m, "$m.$a" );
} else {
        $titleBlock = new w2p_Theme_TitleBlock( 'New salary - ' . $salary->resolve_username($user_id) , 'payment-icon.png', $m, "$m.$a" );
}

$titleBlock->show();

?>


<form name="frmAddEdit" action="?m=salary&a=do_salary_aed&user_id=<?php echo $user_id; ?>" method="post" enctype="multipart/form-data">
<table border="0" width="100%" cellspacing="1" cellpadding="2" class="tbl" id="salary_table">
<tr>
        <th width="1%"></th>
        <th width="39%">Task name</th>
        <th width="15%">Assigned percent</th>
        <th width="15%">Target Budget</th>
        <th width="15%">Salary</th>
        <th width="15%">Worker FA</th>
</tr>


<?php


$salary->show_user_tasks($user_id, $_GET['checked_FA']);

?>
<tr><td><td colspan=5><INPUT type="button" value="Add File" onclick="addFileRow()" /></td></tr>
<tr><td><td align="right"><b>Note:</b></td><td colspan=4 align="center"><input type="text" name=salary_note style="width:99%"></td></tr>
<tr><td colspan=6 align="right"><input type="submit" value="Submit" style="width:10%;"></td>

</table>

</form>
