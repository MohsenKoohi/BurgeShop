<?php

// Order manager is responsible for shipping and billing addresses, 
// coupons, and shipping method
// In this version, we don't have any of these and thus we just submit order
// and transfer to payment manger

class Order_manager_model extends CI_Model
{
	private $order_table_name="order";
	private $order_history_table_name="order_history";
	
	public function __construct()
	{
		parent::__construct();

      return;
   }

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->order_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`order_id` INT AUTO_INCREMENT
				,`order_customer_id` INT NOT NULL
				,`order_date`	CHAR(19)
				,`order_total` DOUBLE 
				,`order_status` VARCHAR(63)
				,PRIMARY KEY (order_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->order_history_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`oh_id` INT AUTO_INCREMENT
				,`oh_order_id` INT NOT NULL
				,`oh_date`	CHAR(19)
				,`oh_status` VARCHAR(63)
				,`oh_comment` VARCHAR(511) NOT NULL
				,PRIMARY KEY (oh_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("order","order_manager");
		$this->module_manager_model->add_module_names_from_lang_file("order");
		
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

	public function add_history($order_id, $status, $comment='')
	{
		$this->db
			->set("order_status", $status)
			->where("order_id", $order_id)
			->update($this->order_table_name);

		$props=array(
			"oh_order_id"	=> $order_id
			,"oh_status"	=> $status
			,"oh_date"		=> get_current_time()
			,"oh_comment"	=> $comment
		);
		$this->db->insert($this->order_history_table_name,$props);
		$oh_id=$this->db->insert_id();
		$props['oh_id']=$oh_id;

		$this->log_manager_model->info("ORDER_ADD_HISTORY",$props);	

		return;
	}

	public function get_orders($filter)
	{
		$this->db
			->select("*")
			->from($this->order_table_name);

		$this->set_query_filters($filter);
			
		return $this->db
			->get()
			->result_array();
	}

	private function set_query_filters($filter)
	{
		if(isset($filter['order_id']))
			$this->db->where("order_id",(int)$filter['order_id']);

		if(isset($filter['customer_id']))
			$this->db->where("order_customer_id",(int)$filter['customer_id']);

		if(isset($filter['status']))
			$this->db->where("order_status",$filter['status']);

		return;
	}
	
}