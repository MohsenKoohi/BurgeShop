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
			,"ph_comment"		=> $comment
		);
		$this->db->insert($this->payment_history_table_name,$props);
		$oh_id=$this->db->insert_id();
		$props['ph_id']=$oh_id;

		$this->log_manager_model->info("PAYMENT_ADD_HISTORY",$props);	

		return;
	}

	public function get_payment_methods()
	{
		return $this->payment_methods;
	}	
}