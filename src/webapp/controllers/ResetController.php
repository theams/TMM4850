<?php
namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;

class ResetController extends Controller
{
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
        $pass = $request->post('pass');
        $user = User::findByUser($username);
        
        $hashed = Hash::make($pass);
        

        
        if($user == null){
            $this->app->redirect('/login');
        }else{
            $user->setHash($hashed);
            $user->resetPassword();
            $this->app->redirect('/login');
        }
    }
}
