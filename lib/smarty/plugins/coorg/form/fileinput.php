<?php

class FileInput extends TextInput
{
	private $_preview = '';
	private $_previewClass;
	
	public function __construct()
	{
		parent::__construct('file');
	}
	
	public function render()
	{
		return $this->renderLabel() . $this->renderPreview() . $this->renderInput();
	}
	
	public function setSpecificParameters(&$params)
	{
		parent::setSpecificParameters($params);
		if ($preview = self::getParameter($params, 'preview'))
		{
			$this->_preview = $preview;
			if ($previewClass = self::getParameter($params, 'previewClass'))
			{
				$this->_previewClass = $previewClass;
			}
		}
	}
	
	protected function renderPreview()
	{
		switch ($this->_preview)
		{
			case 'image':
				if ($this->_previewClass)
				{
					$p = '<div class="'.$this->_previewClass.'"><img src="'.$this->_value.'" /></div>';
				}
				else
				{
					$p = '<img src="'.$this->_value.'" />';
				}
				return $p;
			case '': break;
			default:
				throw new Exception('Unknown preview class');
		}
	}
}

?>
