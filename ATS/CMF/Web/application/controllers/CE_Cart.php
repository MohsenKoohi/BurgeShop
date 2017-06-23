<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Cart extends Burge_CMF_Controller {
	protected $hit_level=1;

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{	
		$this->data['message']=get_message();
		
		$this->load->model("cart_manager_model");
		$this->data['products']=$this->cart_manager_model->get_cart($this->selected_lang);

		$this->cart_manager_model->update_cart();exit();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_cart",TRUE));
		
		$this->data['header_title']=$this->lang->line("cart").$this->lang->line("header_separator").$this->data['header_title'];
		
		$this->send_customer_output("cart");

		return;
	}
}