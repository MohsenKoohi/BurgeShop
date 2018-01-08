<?php
class Coupon_manager_model extends CI_Model {
	private $coupon_table_name="coupon";
	private $coupon_payment_table_name="coupon_payment";

	public function __construct()
	{
		parent::__construct();
	}

	public function install()
	{
		$table=$this->db->dbprefix($this->coupon_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`coupon_id` int AUTO_INCREMENT 
				,`coupon_name` char(63) DEFAULT NULL
				,`coupon_code` char(63) DEFAULT NULL
				,`coupon_acitve` BIT(1) DEFAULT 0
				,`coupon_min_price` INT DEFAULT 0
				,`coupon_expiration_date` char(20) DEFAULT NULL
				,`coupon_value` INT DEFAULT 0
				,`coupon_value_type` enum('percent','currency') DEFAULT 'currency'
				,`coupon_customers` varchar(1024) DEFAULT NULL
				,`coupon_usage_number` INT DEFAULT 0
				,PRIMARY KEY (coupon_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$table=$this->db->dbprefix($this->coupon_payment_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`cp_coupon_id` INT
				,`cp_order_id` INT
				,`cp_value` INT DEFAULT 0
				,PRIMARY KEY (cp_coupon_id,cp_order_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");
		$this->module_manager_model->add_module("coupon","coupon_manager");
		$this->module_manager_model->add_module_names_from_lang_file("coupon");
		
		return;
	
	}

	public function uninstall()
	{

		return;
	}	

	public function get_all()
	{
		return $this->db
			->select($this->coupon_table_name.".*")
			->from($this->coupon_table_name)
			->get()
			->result_array();
	}

	public function get_coupon($coupon_id)
	{
		$info=$this->db
			->select($this->coupon_table_name.".*")
			->from($this->coupon_table_name)
			->where("coupon_id",$coupon_id)
			->get()
			->row_array();

		$customers=-1;
		if($info && $info['coupon_customers'] && ($info['coupon_customers']!=-1))
		{		
			$this->load->model('customer_manager_model');
			$customers=$this->customer_manager_model->get_customers(array(
				"id"=>explode(",",$info['coupon_customers'])
			));
		}

		return array($info, $customers);
	}

	public function add_new()
	{
		$props=array(
			"coupon_name"						=> ""
			,"coupon_expiration_date"		=> get_current_time()
		);

		$this->db->insert($this->coupon_table_name,$props);

		$coupon_id=$this->db->insert_id();
		$props['coupon_id']=$coupon_id;

		$this->log_manager_model->info("COUPON_ADD",$props);

		return $coupon_id;
	}

	public function set_response($message_id,$response)
	{
		$props=array(
			'cu_response'=>$response
			,'cu_response_time'=>get_current_time()
			,'cu_response_user_id'=>$this->user_manager_model->get_user_info()->get_id()
		);

		$this->db->set($props);
		$this->db->where("cu_id",$message_id);
		$this->db->limit(1);
		$this->db->update($this->contact_us_table_name);

		$props['cu_id']=$message_id;

		$this->log_manager_model->info("CONTACT_US_REPLY",$props);
		
		return ;
	}

	public function delete($message_id)
	{
		//return FALSE;

		$this->db
			->where("cu_id",$message_id)
			->delete($this->contact_us_table_name);

		$this->log_manager_model->info("CONTACT_US_DELETE",array("cu_id"=>$message_id));

		return TRUE;
	}
}