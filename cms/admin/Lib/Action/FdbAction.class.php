<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FdbAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 数据库维护
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 数据库维护
 *
 */
class FdbAction extends FbaseAction {
	/**
	 * @name 初始化
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		
		switch ($in['do']) {
			case 'optimize':
				$this->optimize();
				break;
			case 'repair':
				$this->repair();
				break;
		}
	}
	
	/**
	 * @name数据库备份
	 */
	public function backup() {
		$in = &$this->in;
		$DbBackup = D('DbBackup');
		header('Content-type:text/html;charset=utf-8;');
		
		//如果有数据提交
		if ($this->ispost()) {
			if (is_null($in['tableName'])) {
				exit;
			}
			$tables = $in['tableName']; //需要备份的表
			if (!is_array($tables)) {
				$tables = explode(',', $tables);
			}
			
			if(!isset($in['startpos'])) {  //记录开始位置
				$startpos = 0;
			} else $startpos = $in['startpos'];
			
			if (!isset($in['fnum'])) {  //分卷id
				$fnum = 1;
			} else $fnum = $in['fnum'];
			
			if(empty($in['nowtable'])) {  //当面表名称
				$nowtable = '';
			} else $nowtable = $in['nowtable'];
			
			$fsize = intval($in['fsize']) ? intval($in['fsize']) : 2048;  //分卷大小

			$prefix = 'BACK_' . date('Y_m_d') . '_' . substr(uniqid(), -5);
			while (file_exists(DB_BACKUP_PATH . $prefix . '_1.txt')) {  //判断是否有重名备份文件
				$prefix = 'BACK_' . date('Y_m_d') . '_ ' . substr(uniqid(), -5);
			}
			
			$backup = $DbBackup->doBackup($tables, $fsize, $fnum, $nowtable, $startpos, $prefix);
			echo '<div style="font-size:13px;">';
			while ($backup['code'] == 'incomplete') { //$backup['code'] 值表示: incomplete：还有分卷 success:完成 error:出错
				if (!is_array($backup['tableName'])) {
					$backup['tableName'] = explode(',', $backup['tableName']);
				}
				echo $backup['msg'] . '<br />';
				$backup = $DbBackup->doBackup($backup['tableName'], $backup['fsize'], $backup['fnum'], $backup['nowtable'], $backup['startpos'], $prefix);
			}
			
			echo $backup['msg'] . '<br />';
			echo '</div>';
			exit;
		}
		
		//获取系统存在的表信息
		$otherTables = Array();
		$fangfaSysTables = Array();
		
		$tableList = $DbBackup->getTables();
		$i = 0;
		$j = 0;
		foreach ($tableList AS $key=>$table) {
			if (preg_match('/^' . C('DB_PREFIX') . '.*/i', $table)) {
				$fangfaSysTables[$i]['name'] = $table;
				$fangfaSysTables[$i]['count'] = M(substr($table, strlen(C('DB_PREFIX'))))->count();
				$i++;
			} else {
				$otherTables[$j]['name'] = $table;
				$otherTables[$j]['count'] = $DbBackup->getCount($table);
				$j++;
			}
		}
		
		$this->assign('mysqlVersion', (string)substr(mysql_get_server_info ( $DbBackup->getDb ()->getNlinkID ()), 0, 3));
		$this->assign('fangfaSysTables', $fangfaSysTables);
		$this->assign('otherTables', $otherTables);
		
		$this->display();
	}
	
	/**
	 * @name数据库恢复
	 */
	public function restore() {
		$in = &$this->in;
		$DbBackup = D('DbBackup');
		
		$filelists = $DbBackup->orderBackupFiles(DB_BACKUP_PATH);
		if ($this->ispost()) {
			if (isset($filelists[$in['prefix']])) {
				echo '<div style="font-size:13px;">';
				foreach ($filelists[$in['prefix']] as $f) {
					if ($DbBackup->doRedat(DB_BACKUP_PATH . $f['name'])) {
						echo '分卷' . $f['name'] . '还原完成！<br />';
					}
				}
				echo "所有数据还原完成！<br />";
				echo '</div>';
			}
			exit;
		}
		
		$this->assign('filelists', $filelists);
		$this->display();
	}
	
	/**
	 * @name删除备份
	 */
	public function del()
	{
		$in = &$this->in;
		$DbBackup = D('DbBackup');
		
		$filelists = $DbBackup->orderBackupFiles(DB_BACKUP_PATH);
		
		if (isset($filelists[$in['prefix']])) {
			foreach ($filelists[$in['prefix']] AS $f) {
				if (!@unlink(trim(DB_BACKUP_PATH, '/\\') . '/' . $f['name'])) {
					$this->error('删除备份文件失败！');
				}
			}
		}
		
		redirect(U('fdb/restore'));
	}
	
	/**
	 * @name数据库修复
	 */
	public function repair() {
		$in = & $this->in;
		$DbBackup = D('DbBackup');
		
		$table_list = explode(',', $in['table_list']);
		echo '<div style="font-size:13px;">';
		if ($table_list) {
			foreach ($table_list AS $t) {
				if ($DbBackup->doRepair($t)) {
					echo "修复表： " . $t . "  OK！<br />";
				}else {
					echo "修复表： " . $t . "  失败，原因是：" . $DbBackup->getDb()->getError() . '。 <br />';
				}
			}
		} else {
			echo "您没有选择需要操作的表！";
		}
		echo "修复完成！";
		echo '</div>';
		exit;
	}
	
	/**
	 * @name数据表优化
	 */
	public function optimize() {
		$in = & $this->in;
		$DbBackup = D('DbBackup');		
		$table_list = explode(',', $in['table_list']);
		header('Content-type:text/html;charset=utf-8;');
		echo '<div style="font-size:13px;">';		
		if ($table_list) {
			foreach ($table_list AS $t) {
				if ($DbBackup->doOpimize($t)) {
					echo "执行优化表： " . $t . "  OK！<br />";
				}else {
					echo "执行优化表： " . $t . "  失败，原因是：" . $DbBackup->getDb()->getError() . '。 <br />';
				}
			}
		} else {
			echo "您没有选择需要操作的表！";
		}
		echo "优化完成！";	
		echo '</div>';	
		exit;
	}
	
	/**
	 * @name查看数据表结构
	 */
	public function viewinfo() {
		$in = & $this->in;
		$DbBackup = D('DbBackup');
		header('Content-type:text/html;charset=utf-8;');
		echo '<div style="font-size:13px;width:98%;height:98%;">';
		echo "<pre>" . $DbBackup->doViewinfo($in['tablename']) . '</pre>';
		echo '</div>';
	}
	
}

?>