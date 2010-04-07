<?php

class PropertyEmail extends Property implements IProperty
{

	public function get()
	{
		$value = trim($this->_value);
		if ($value == '')
		{
			return null;
		}
		else
		{
			return $value;
		}
	}

	public function validate($for)
	{
		$email = trim($this->_value);
		if ($email == '' && $this->isRequired($for))
		{
			$this->error('%n is required');
			return false;
		}
		else if ($email == '')
		{
			return true;
		}
		else
		{
			$isValid = true;
			$atIndex = strrpos($email, "@");
			if (is_bool($atIndex) && !$atIndex)
			{
				$isValid = false;
			}
			else
			{
				$domain = substr($email, $atIndex+1);
				$local = substr($email, 0, $atIndex);
				$localLen = strlen($local);
				$domainLen = strlen($domain);
				if ($localLen < 1 || $localLen > 64)
				{
					// local part length exceeded
					$isValid = false;
				}
				else if ($domainLen < 1 || $domainLen > 255)
				{
					// domain part length exceeded
					$isValid = false;
				}
				else if ($local[0] == '.' || $local[$localLen-1] == '.')
				{
					// local part starts or ends with '.'
					$isValid = false;
				}
				else if (preg_match('/\\.\\./', $local))
				{
					// local part has two consecutive dots
					$isValid = false;
				}
				else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
				{
					// character not valid in domain part
					$isValid = false;
				}
				else if (preg_match('/\\.\\./', $domain))
				{
					// domain part has two consecutive dots
					$isValid = false;
				}
				else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
				{
					// character not valid in local part unless 
					// local part is quoted
					if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
					{
						$isValid = false;
					}
				}
				if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
				{
					// domain not found in DNS
					$isValid = false;
				}
			}
			if (!$isValid)
			{
				$this->error('%n is not a valid emailadress');
			}
			return $isValid;
		}
	}

	protected function toDB($value)
	{
		$value = trim($value);
		if ($value == '')
		{
			return null;
		}
		else
		{
			return $value;
		}
	}
}

?>
