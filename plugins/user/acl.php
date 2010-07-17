<?php

class MockModelCheck
{
	public function owns($username, $object)
	{
		return $object->username == $username;
	}
}


Acl::registerOwnsClass('MySpecificModel', new MockModelCheck);

?>
