<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Cart extends Burge_CMF_Controller {
	protected $hit_level=1;

	function __construct()
	{
		parent::__construct();

		$this->load->model("cart_manager_model");
	}

	public function index()
	{	
		if($this->input->post("post_type")=='remove_item')
			return $this->remove_item();

		$this->data['message']=get_message();
		
		$cart=$this->cart_manager_model->get_cart($this->selected_lang);
		$this->data['products']=$cart['products'];
		$this->data['total_price']=$cart['total_price'];

		$this->data['lang_pages']=get_lang_pages(get_link("customer_cart",TRUE));
		
		$this->data['header_title']=$this->lang->line("cart").$this->lang->line("header_separator").$this->data['header_title'];
		
		$this->send_customer_output("cart");

		return;
	}

	private function remove_item()
	{
		$index=(int)$this->input->post("item_index");

		$this->cart_manager_model->remove_item($index);

		set_message($this->lang->line("item_removed_successfully"));

		return redirect(get_link('customer_cart'));
	}
}