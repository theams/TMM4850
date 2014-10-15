<?php
namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;

class ResetController extends Controller
{
    protected $user;
            
    function __construct()
    {
        parent::__construct();
    }
    
    function index()
    {
        $this->render('resetPassword.twig', []);
    }
    
    function resetPassword(){
        $request = $this->app->request;
        $username = $request->post('user');
        $answer = $request->post('answer');
        $pass = $request->post('pass');

        $user = User::findByUser($username);
        $question = $user->getSecurityQuestion();
        
        if($pass === null){
            $this->render('resetPassword.twig',['username' => $username,'question'=>$question,'user' => $user]);
            return;
        }
        if($this->checkIfAnwserIsCorrect($answer,$user)){
            $this->doTheReset($pass,$user);
        }else{
            $this->render('resetPassword.twig',['username' => $username,'question'=>$question,'user' => $user]);
        }
    }
    
    function doTheReset($pass,$user){
        $hashed = Hash::make($pass);
        $user->setHash($hashed);
        $user->resetPassword();
        $this->app->redirect('/login');
    }
    
    function checkIfAnwserIsCorrect($answer,$user){
        return Hash::check($answer, $user->getSecurityAnswer());
    }
}
