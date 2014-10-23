<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            $this->render('login.twig', []);
        }
    }

    function login()
    {
        $request = $this->app->request;
        date_default_timezone_set('UTC');
        $token = "" + date('jnY');
        if($request->post('csrfToken') == $token){
            $user = $request->post('user');
            $user = $this->xecho($user);
            $pass = $request->post('pass');
            //$pass = $this->xecho($pass);

            if (Auth::checkCredentials($user, $pass)) {

                $_SESSION['user'] = $user;
                $isAdmin = Auth::user()->isAdmin();

                session_regenerate_id();

                //if ($isAdmin) {
                //    setcookie("isadmin", "yes");
                //} else {
                //    setcookie("isadmin", "no");
                //  }

                $this->app->flash('info', "You are now successfully logged in as $user.");
                $this->app->redirect('/');
            } else {
                $this->app->flashNow('error', 'Incorrect user/pass combination.');
                $this->render('login.twig', []);
            }
        }
        else {
            //$this->app->flash('info', 'Incorrect csrf.');
            $this->app->redirect('/login');
        }
    }

    function xssafe($data,$encoding='UTF-8')
    {
       return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,$encoding);
    }

    function xecho($data)
    {
       return $this->xssafe($data);
    }
}
