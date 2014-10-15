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
        
        if($pass === null){
            if ($user === null){
                $this->app->flashNow('error','invalid username');
                $this->render('resetPassword.twig',['username' => $username]);
                return;
            }
            $question = $user->getSecurityQuestion();
            $this->render('resetPassword.twig',['username' => $username,'question'=>$question,'user' => $user]);
            return;
        }
        $question = $user->getSecurityQuestion();
        if($this->checkIfAnwserIsCorrect($answer,$user)){
            $error = User::validatePass($pass, []);
            if(sizeof($error) <1){
                $this->doTheReset($pass,$user);
            }else{
                $errors = join("", $error);
                $this->app->flashNow('error',$errors);
                $this->render('resetPassword.twig',['username'=> $username,'question'=>$question,'user' => $user]);
            }
        }else{
            $this->app->flashNow('error','wrong answer');
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
