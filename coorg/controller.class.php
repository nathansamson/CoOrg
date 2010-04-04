<?php

class Controller {

	protected $post = array();
	
	public function isPost($name)
	{
		return in_array($name, $this->post);
	}

}

?>
