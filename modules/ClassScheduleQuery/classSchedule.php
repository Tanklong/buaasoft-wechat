<?php 

// $wxdb = null;
// class_schedule_test();

function class_schedule_test() {
	global $wxdb;
	require_once("../../includes/wxdb.php");
	$wxdb = new wxdb("root", "root", "weixin", "localhost");

	$_1221 = new ClassSchedule("class_schedule", 1221);
	$_1221->setWeekday(1)->add_class("英语口语", "s1e2k3j4g5");
	$_1221->save();
}

/**
 * Class ClaaSchedule
 *
 * the class schedule database model, for query and other data operation
 *
 * Database Scheme:class_schedule
 * 1. id (int PK, A_I)
 * 2. weekday (int 星期几 e.g. 1)
 * 3. classification (int 年纪 e.g. 1221)
 * 4. class_1 (string 第一节课信息 e.g. 英语口语#s1e2k3j4g5)
 * ......
 *
 * @todo changing according to wxdb.php
 */
class ClassSchedule {
	
	// store info. the sub_index is weekday
	private $schedule_days;
	// database table name
	private $table_name;
	// current weekday
	private $cur_weekday;
	// classification
	private $classification;

	// max class num
	const NUM_MAX_CLASS = 12;
	const SEPARATOR = '#';

	function __construct($table_name, $classification) {
		$this->table_name = $table_name;
		$this->create_table($table_name);
		$this->classification = $classification;
		$this->schedule_days = array();
	}

	/**
	 * add a class which classification is $classification and weekday is $cur_weekday
	 *
	 * @param string $classStr the information of class, e.g. 英语口语 J1-101
	 * @param string $infoStr the information of class time, e.g. s1e2k3j4g5
	 * @return the instance of ClassSchedule
	 * @todo 尝试根据数组中已有的内容来调整冲突内容？
	 */
	public function add_class($classStr, $infoStr) {
		$count = count($this->schedule_days[$this->cur_weekday]);
		$this->schedule_days[$this->cur_weekday]["class_".++$count] = $classStr.self::SEPARATOR.$infoStr;
		return $this;
	}

	/**
	 * @todo 修改创建表的SQL语句
	 */
	public function create_table($table_name) {
		global $wxdb;

		$wxdb->query("CREATE TABLE IF NOT EXISTS `$table_name` (id int NOT NULL AUTO_INCREMENT, weekday int NOT NULL, classification int NOT NULL, class_1 varchar(100), class_2 varchar(100), class_3 varchar(100), class_4 varchar(100), class_5 varchar(100), class_6 varchar(100), class_7 varchar(100), class_8 varchar(100), class_9 varchar(100), class_10 varchar(100), class_11 varchar(100), class_12 varchar(100), PRIMARY KEY (`id`))");
	}

	/**
	 * delete all database
	 * @return bool
	 */
	public function clear() {
		global $wxdb;
		$wxdb->delete($table_name);
		
		return true;
	}

	/**
	 * set the weekday
	 * 
	 * @see $cur_weekday
	 * @param int $day 
	 * @return object the instance of classSchedule
	 */
	public function setWeekday($weekday) {
		$this->cur_weekday = $weekday;
		// prepare the array
		if (!isset($this->schedule_days[$weekday]) || !is_array($this->schedule_days[$weekday])) {
			$this->schedule_days[$weekday] = array();
		}

		return $this;
	}

	/**
	 * query according to the $weekday and $classification
	 * $classification is specified in construct function
	 * 
	 * @param int $weekday the weekday number
	 * @return array the array contain class information
	 * @todo need to know more about the $result
	 */
	public function query($weekday) {
		global $wxdb;
		$result = null;
		$results = $wxdb->get_results("SELECT * FROM class_schedule WHERE weekday=$weekday");
		if ($wxdb->num_rows == 1) {
			$result = $results[0];

		} else {
			// error
		}
		return $result;
	}

	/**
	 * save to database
	 */
	public function save() {

		global $wxdb;
		$weekdays = array_keys($this->schedule_days);

		foreach ($weekdays as $weekday) {
			$wxdb->query("SELECT * FROM $this->table_name WHERE classification=$this->classification AND weekday=$weekday");
			$class_count = count($this->schedule_days[$weekday]);
			$where = array(
				"classification"=>$this->classification, 
				"weekday"=>$this->cur_weekday
				);

			$result = false;

			// clear first, according to the $classification and $weekday to update database content
			if ($wxdb->num_rows == 1) {
				// need update
				$result = $wxdb->update($this->table_name, $this->schedule_days[$weekday], $where);
			} else {
				// need insert
				$result = $wxdb->insert($this->table_name, array_merge($this->schedule_days[$weekday], $where));
			}
		}
		
		
	}
}

?>