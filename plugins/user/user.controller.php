<?php

class UserController extends Controller
{
	public function create()
	{
		$this->user = new User('', '');	
		$this->render('create');
	}
	
	public function save($username, $email, $password, $passwordConfirmation)
	{
		$user = new User($username, $email);
		$user->password = $password;
		$user->passwordConfirmation = $passwordConfirmation;
		
		try
		{
			$user->save();
			$this->notice('We have sent an email to confirm your registration');
			$this->redirect('/');
		}
		catch (ValidationException $e)
		{
			$this->error('We could not complete your registration');
			$this->user = $user;
			$this->render('create');
		}
	}
}

?>
