<?php

class UserController extends Controller
{
	public function create()
	{
		$this->user = new User('', '');	
		$this->render('create');
	}
	
	/**
	 * @post
	*/
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
	
	public function login()
	{
		$this->session = new UserSession('', '');
		$this->render('login');
	}
	
	/**
	 * @post
	*/
	public function executeLogin($username, $password)
	{
		$session = new UserSession($username, $password);
		
		try
		{
			$session->save();
			$this->notice('You are now logged in');
			$this->redirect('/');
		}
		catch (ValidationException $e)
		{
			$this->error('You are not logged in');
			$this->session = $session;
			$this->render('login');
		}
	}
	
	public function logout()
	{
		$session = UserSession::get();
		if ($session != null)
		{
			$session->delete();
		}
		
		$this->notice('You are now logged out');
		$this->redirect('/');
	}
}

?>
