<?php

class Payment_manager_model extends CI_Model
{
	private $payment_table_name="payment";
	private $payment_history_table_name="payment_history";
	private $payment_methods=array(
		"bank_transfer"
	);
	
	public function __construct()
	{
		parent::__construct();

      return;
   }

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->payment_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`payment_id` INT AUTO_INCREMENT
				,`payment_order_id` INT NOT NULL
				,`payment_total` DOUBLE 
				,`payment_method` VARCHAR(63)
				,`payment_date`	CHAR(19)
				,`payment_status` VARCHAR(63)
				,`payment_reference` VARCHAR(63)
				,PRIMARY KEY (payment_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->payment_history_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`ph_id` INT AUTO_INCREMENT
				,`ph_payment_id` INT NOT NULL
				,`ph_date`	CHAR(19)
				,`ph_status` VARCHAR(63)
				,`ph_comment` VARCHAR(511) NOT NULL
				,PRIMARY KEY (ph_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("payment","payment_manager");
		$this->module_manager_model->add_module_names_from_lang_file("payment");
		
		return;
	}

	public function uninstall()
	{
		return;
	}

	public function get_payment_methods()
	{
		return $this->payment_methods;
	}	

	public function get_order_payments($order_id)
	{
		$result=$this->db
			->select("*")
			->from($this->payment_table_name)
			->join($this->payment_history_table_name,"payment_id = ph_payment_id","LEFT")
			->where("payment_order_id", $order_id)
			->order_by("payment_id DESC, ph_id ASC")
			->get()
			->result_array();

		$last_payment_id=0;
		$payments=array();

		foreach($result as $r)
		{
			if($r['payment_id'] != $last_payment_id)
			{
				$last_payment_id=$r['payment_id'];
				$payments[]=array(
					"id"				=> $r['payment_id']
					,"method"		=> $r['payment_method']
					,"total"			=> $r['payment_total']
					,"date"			=> $r['payment_date']
					,"status"		=> $r['payment_status']
					,"reference"	=> $r['payment_reference']
					,'history'		=> array()
				);
			}

			$payments[sizeof($payments)-1]['history'][]=array(
				"id"			=> $r['ph_id']
				,"date"		=> $r['ph_date']
				,"status"	=> $r['ph_status']
				,"comment"	=> json_decode($r['ph_comment'],TRUE)
			);
		}

		return $payments;
	}
	
	public function add_payment($order_id, $total, $method)
	{
		$props=array(
			"payment_order_id"	=> $order_id
			,"payment_total"		=> $total
			,"payment_method"		=> $method
			,"payment_date"		=> get_current_time()
		);

		$this->db->insert($this->payment_table_name,$props);
		$payment_id=$this->db->insert_id();
		$props['payment_id']=$payment_id;
		$this->log_manager_model->info("PAYMENT_ADD",$props);	

		$this->add_history($payment_id,"start_payment");

		return $payment_id;
	}

	//comment can be a string or an array
	public function add_history($payment_id, $status, $comment='',$reference_code='')
	{
		$this->db
			->where("payment_id", $payment_id)
			->set("payment_status", $status);
		
		if($reference_code)
			$this->db->set("payment_reference",$reference_code);

		$this->db->update($this->payment_table_name);

		$props=array(
			"ph_payment_id"	=> $payment_id
			,"ph_status"		=> $status
			,"ph_date"			=> get_current_time()
			,"ph_comment"		=> json_encode($comment)
		);

		$this->db->insert($this->payment_history_table_name,$props);
		$oh_id=$this->db->insert_id();
		$props['ph_id']=$oh_id;

		$this->log_manager_model->info("PAYMENT_ADD_HISTORY",$props);	

		return;
	}

	public function get_payments($filter)
	{
		$this->db
			->select("o.*,  customer_id, customer_name, customer_email")
			->from($this->payment_table_name." o")
			->join("order","payment_order_id = order_id","left")
			->join("customer","order_customer_id = customer_id","left");

		$this->set_query_filters($filter);
			
		return $this->db
			->get()
			->result_array();
	}

	public function get_total_payments($filter)
	{
		$this->db
			->select("COUNT(*) as count")
			->from($this->payment_table_name)
			->join("order","payment_order_id = order_id","left")
			->join("customer","order_customer_id = customer_id","left");

		
		$this->set_query_filters($filter);

		$row=$this->db
			->get()
			->row_array();

		return $row['count'];
	}

	private function set_query_filters($filter)
	{
		if(isset($filter['payment_id']))
		{
			$ids=explode(" ",preg_replace("/\s+/"," ", $filter['payment_id']));
			if(sizeof($ids)>1)
				$this->db->where_in("payment_id",$ids);
			else
				$this->db->where("payment_id",$filter['payment_id']);
		}

		if(isset($filter['order_id']))
		{
			$ids=explode(" ",preg_replace("/\s+/"," ", $filter['order_id']));
			if(sizeof($ids)>1)
				$this->db->where_in("payment_order_id",$ids);
			else
				$this->db->where("payment_order_id",$filter['order_id']);
		}

		if(isset($filter['method']))
			$this->db->where("payment_method",$filter['method']);

		if(isset($filter['start_date']))
			$this->db->where("payment_date >=",$filter['start_date']." 00:00:00");

		if(isset($filter['end_date']))
			$this->db->where("payment_date <=",$filter['end_date']." 23:59:59");

		if(isset($filter['customer_id']))
			$this->db->where("order_customer_id",(int)$filter['customer_id']);

		if(isset($filter['name']))
			$this->db->where("customer_name LIKE '%".str_replace(' ', '%', $filter['name'])."%'");

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);

		return;
	}	
}