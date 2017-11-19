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
				,`payment_ops_number` INT DEFAULT 1
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


	public function get_order_payment_sections_for_customer($order_id)
	{
		return $this->db
			->select("*")
			->from("order_payment_section")
			->where("ops_order_id", $order_id)
			->order_by("ops_number DESC")
			->get()
			->result_array();
	}
	
	public function get_order_payment_sections($order_id)
	{
		$result=$this->db
			->select("*")
			->from("order_payment_section")
			->join($this->payment_table_name, "payment_order_id = $order_id AND payment_ops_number = ops_number", "LEFT")
			->join($this->payment_history_table_name,"payment_id = ph_payment_id","LEFT")
			->where("ops_order_id", $order_id)
			->order_by("ops_number DESC, payment_id DESC, ph_id DESC")
			->get()
			->result_array();

		$last_ops_number=-1;
		$last_payment_id=0;
		$sections=array();

		foreach($result as $r)
		{
			if($r['ops_number'] != $last_ops_number)
			{
				$last_ops_number= $r['ops_number'];
				$sections[]=array(
					"ops_number"	=> $r['ops_number']
					,"ops_total"	=> $r['ops_total']
					,"ops_status"	=> $r['ops_status']
					,"payments"		=> array()
				);
			}

			if(!$r['payment_id'])
				continue;
			
			if($r['payment_id'] != $last_payment_id)
			{
				$last_payment_id=$r['payment_id'];
				$sections[sizeof($sections)-1]['payments'][]=array(
					"payment_id"				=> $r['payment_id']
					,"payment_method"			=> $r['payment_method']
					,"payment_total"			=> $r['payment_total']
					,"payment_date"			=> $r['payment_date']
					,"payment_status"			=> $r['payment_status']
					,"payment_reference"		=> $r['payment_reference']
					,'payment_history'		=> array()
				);
			}

			$sections[sizeof($sections)-1]['payments'][sizeof($sections[sizeof($sections)-1]['payments'])-1]['payment_history'][]=array(
				"id"			=> $r['ph_id']
				,"date"		=> $r['ph_date']
				,"status"	=> $r['ph_status']
				,"comment"	=> json_decode($r['ph_comment'],TRUE)
			);
		}

		return $sections;
	}
	
	public function add_payment($order_id, $ops_number, $total, $method)
	{
		$props=array(
			"payment_order_id"		=> $order_id
			,"payment_ops_number"	=> $ops_number
			,"payment_total"			=> $total
			,"payment_method"			=> $method
			,"payment_date"			=> get_current_time()
		);

		$this->db->insert($this->payment_table_name,$props);
		$payment_id=$this->db->insert_id();

		$props['payment_id']=$payment_id;
		
		$this->log_manager_model->info("PAYMENT_ADD",$props);	

		$this->load->model("customer_manager_model");
		$customer_id= $this->get_customer_of_payment($payment_id);
		$this->customer_manager_model->add_customer_log($customer_id,'PAYMENT_ADD',$props);

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
			"ph_payment_id"		=> $payment_id
			,"ph_status"			=> $status
			,"ph_date"				=> get_current_time()
			,"ph_comment"			=> json_encode($comment)
		);

		$this->db->insert($this->payment_history_table_name,$props);
		
		$oh_id=$this->db->insert_id();
		$props['ph_id']=$oh_id;
		if(!is_string($comment))
		{
			unset($props['ph_comment']);
			foreach($comment as $index => $value)
				$props['ph_comment_'.$index]=$value;
		}
		$props['reference_code']=$reference_code;
		$this->log_manager_model->info("PAYMENT_ADD_HISTORY",$props);	

		$this->load->model("customer_manager_model");
		$customer_id= $this->get_customer_of_payment($payment_id);
		$this->customer_manager_model->add_customer_log($customer_id, 'PAYMENT_ADD_HISTORY', $props);

		return;
	}

	private function get_customer_of_payment($payment_id)
	{
		$payment=$this->db
			->from($this->payment_table_name)
			->join("order","payment_order_id = order_id","LEFT")
			->where("payment_id", $payment_id)
			->get()
			->row_array();
			
		if($payment)
			return $payment['order_customer_id'];

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