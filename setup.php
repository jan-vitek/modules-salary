<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

/**
 * Name:			Salary
 * Directory:			salary
 * Type:			user
 * UI Name:			salary
 * UI Icon: 			?
 */

$config = array();
$config['mod_name']        = 'Salary';				// name the module
$config['mod_version']     = '1.0.0';				// add a version number
$config['mod_directory']   = 'salary';				// tell web2project where to find this module
$config['mod_setup_class'] = 'CSetupSalary';			// the name of the PHP setup class (used below)
$config['mod_type']        = 'user';				// 'core' for modules distributed with w2p by standard, 'user' for additional modules
$config['mod_ui_name']	   = $config['mod_name']; 		// the name that is shown in the main menu of the User Interface
$config['mod_ui_icon']     = '';				// name of a related icon
$config['mod_description'] = 'Salary';				// some description of the module
$config['mod_config']      = true;				// show 'configure' link in viewmods
$config['mod_main_class']  = 'CSalary';

$config['permissions_item_table'] = 'salaries';
$config['permissions_item_field'] = 'salary_id';
$config['permissions_item_label'] = 'salary_title';

class CSetupSalary
{
	public function install()
	{ 
		global $AppUI;

        $q = new w2p_Database_Query();
		$q->createTable('salaries');
		$sql = '(
			salary_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			salary_title text NOT NULL,
			user_id int(10) unsigned NOT NULL,
			amount int(10) unsigned NOT NULL,
                        tax int(10) unsigned default NULL,
                        payment_type_id int(10) default NULL,
                        created_at datetime NOT NULL,
                        paid_at datetime default NULL,                        

			PRIMARY KEY  (salary_id))
			ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $q->createDefinition($sql);
        $q->exec();
        $q->clear();

        $q = new w2p_Database_Query();
                $q->createTable('salaries_tasks');
                $sql = '(
                        salary_task_id int(10) unsigned NOT NULL AUTO_INCREMENT,
                        salary_id int(10) unsigned NOT NULL,
                        task_id int(10) unsigned NOT NULL,

                        PRIMARY KEY  (salary_task_id))
                        ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $q->createDefinition($sql);
        $q->exec();
        $q->clear();
/*
        $q->addTable('salary','sl');
        $q->addInsert('salary_URL_use','salary_base_URL');
        $q->addInsert('salary_URL','http://localhost/salary/');
        $q->exec();
*/
        $perms = $AppUI->acl();
        return $perms->registerModule('Salary', 'salary');

//        return parent::install();
	}

	public function upgrade($old_version)
	{
        switch ($old_version) {
            case '1.0.0':
            case '1.0.1':
            default:
				//do nothing
		}
		return true;
	}

	public function remove()
	{ 
		global $AppUI;

        $q = new w2p_Database_Query;
		$q->dropTable('salary');
		$q->exec();

        $perms = $AppUI->acl();
        return $perms->unregisterModule('salary');
	}


    public function configure() {
        global $AppUI;
        $AppUI->redirect('m=salary&a=configure');
        return true;
    }
}
