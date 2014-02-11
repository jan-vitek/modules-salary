<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

/* This file will write a php config file to be included during execution of
* all Project designer files which require the configuration options. */
global $m;

// Deny all but system admins
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$utypes = w2PgetSysVal('UserType');

$CONFIG_FILE = W2P_BASE_DIR . '/modules/salary/config.php';

$AppUI->savePlace();

//if this is a submitted page, overwrite the config file.
if (w2PgetParam($_POST, 'Save', '') != '') {

	if (is_writable($CONFIG_FILE)) {
		if (!$handle = fopen($CONFIG_FILE, 'w')) {
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('cannot be opened'), UI_MSG_ERROR);
			exit;
		}

		if (fwrite($handle, "<?php //Do not edit this file by hand, it will be overwritten by the configuration utility. \n") === false) {
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('cannot be written to'), UI_MSG_ERROR);
			exit;
		} else {
                        if(isset($_POST['new_user'])) {
                          global $AppUI;
                          $q = new w2p_Database_Query();
                          $q->addTable('users');
                          $where = 'user_username = "'. $_POST['new_user'] . '"';
                          $q->addWhere( "(" . $where . ")" );
                          $res = $q->loadColumn();
                          foreach ( $res as $user_id ){
                            fwrite($handle, "\$SALARY_ACCOUNTING_USERS['" . $user_id . "'] = '" . "1" . "';\n");
                          }
                          
                        }
                        foreach ($_POST["user"] as $key => $value) {
                          fwrite($handle, "\$SALARY_ACCOUNTING_USERS['" . $key . "'] = '" . $value . "';\n");
                        }

			fwrite($handle, "?>\n");
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('has been successfully updated'), UI_MSG_OK);
			fclose($handle);
			require ($CONFIG_FILE);
		}
	} else {
		$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('is not writable'), UI_MSG_ERROR);
	}
} elseif (w2PgetParam($_POST, $AppUI->_('Cancel'), '') != '') {
	$AppUI->redirect('m=system&a=viewmods');
}

//$PROJDESIGN_CONFIG = array();
include ($CONFIG_FILE);
$SALARY_ACCOUNTING_USERS[40] = true;
//Read the current config values from the config file and update the array.

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Salary Module Configuration', 'icon.png', $m,  $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'Modules');
$titleBlock->show();

?>

<form method="post" accept-charset="utf-8">
    <table class="std">
        <tr>
            <td align="right"><b>Accounting users:</b></td>
            <td></td>
        </tr>
        <tr>
            <td><i>add by username:</i>
            <td><input type="text" name="new_user" /></td>
        </tr>
        <?php
          foreach($SALARY_ACCOUNTING_USERS as $key => $value){
            global $AppUI;
            $q = new w2p_Database_Query();
            $q->addTable('users');
            $where = 'user_id = '. $key;
            $q->addWhere( "(" . $where . ")" );
            $res = $q->exec();
            while($row = db_fetch_assoc($res)){
            ?>
              <tr>
              <td align="right"><input type="checkbox" name="user[<?=$key?>]" value=1 <?php echo $value?"checked=\"checked\"":""?> />
              <?php
              echo "<td>" . $row[user_username] . "</td></tr>";
            }
          }
        ?>
        <tr>
            <td colspan="2" align="right"><input type="Submit" name="Cancel" value="<?php echo $AppUI->_('back')?>" /><input type="Submit" name="Save" value="<?php echo $AppUI->_('save')?>" /></td>
        </tr>
    </table>
</form>