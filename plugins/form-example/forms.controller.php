<?php

/**
 * @property someValue String('The String');
*/
class MockInstance extends Model
{
	public function __construct()
	{
		parent::__construct();
		$this->someValue = 'The Current Value';
		$this->someValue_error = 'Hi I\'m an error message';
	}
}

class FormsController extends Controller
{
	public function index()
	{
		$myInstance = new MockInstance;
		$this->myInstance = $myInstance;
		$this->genders = array('M' => 'Male', 'F' => 'Female');
		$this->myIntrests = array('opensource', 'computers', 'tennis');
		$this->allIntrests = array('sports' => 'Sports', 'tennis' => 'Tennis', 'IT' => 'IT', 'opensource' => 'Open Source');
		$this->countries = array('fr' => 'France', 'nl' => 'The Netherlands', 'lu' => 'Luxembourg', 'be' => 'Belgium', 'de' => 'Germany');
		$this->render('formview');
	}
}

?>
