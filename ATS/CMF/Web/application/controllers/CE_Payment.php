<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Payment extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"customer_manager_model"
			,"order_manager_model"
			,"payment_manager_model"
		));
	}

	public function pay($order_id)
	{	
		if(!$this->customer_manager_model->has_customer_logged_in())
		{
			redirect(get_link("home_url"));
			return;
		}

		$order_id=(int)$order_id;
		$orders=$this->order_manager_model->get_orders(array(
			"order_id" 			=> $order_id
			,"customer_id"		=> $this->customer_manager_model->get_logged_customer_id()
			,"status"			=> 'submitted'
		));

		if(!$orders)
			return redirect(get_link("home_url"));
		
		$order=$orders[0];
		$this->data['order_id']=$order_id;

		$this->data['message']=get_message();
		$this->data['total']=$order['order_total'];
		$this->data['lang_pages']=get_lang_pages(get_customer_payment_order_link($order_id,TRUE));
		$this->data['header_title']=
			$this->lang->line("payment").$this->lang->line("header_separator")
			.$this->lang->line("order")." ".$order_id.$this->lang->line("header_separator")
			.$this->data['header_title'];
		$this->send_customer_output("payment_pay");
		
		return;
	}

	public function orders()
	{
		
	
	}
}