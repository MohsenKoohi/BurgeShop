<?php

class Payment_manager_model extends CI_Model
{
	private $payment_table_name="payment";
	private $payment_history_table_name="payment_history";
	
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
				,PRIMARY KEY (oh_id)	
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
	
	public function submit_order()
	{
		$this->load->model(array(
			"cart_manager_model"
			,"customer_manager_model"
		));
		$cart=$this->cart_manager_model->get_cart($this->selected_lang);
	
		$props=array(
			"order_customer_id"	=> $this->customer_manager_model->get_logged_customer_id()
			,"order_date"			=> get_current_time()
			,"order_total"			=> $cart['total_price']
		);

		$this->db->insert($this->order_table_name,$props);
		$order_id=$this->db->insert_id();
		$props['order_id']=$order_id;
		$this->log_manager_model->info("ORDER_SUBMIT",$props);	

		$this->cart_manager_model->save_order_cart($order_id);

		$this->add_history($order_id,"submitted");

		return $order_id;
	}

	public function add_history($payment_id, $status, $comment='')
	{
		$this->db
			->set("paytment_status", $status)
			->where("payment_id", $payment_id)
			->update($this->payment_table_name);

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




	
}