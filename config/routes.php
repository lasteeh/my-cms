<?php

return [

  // default homepage. change the controller@action to customize homepage
  '/' => [
    'GET' => 'application@home',
  ],

  /***
   * format:
   * 'url' => [
   * 'http_method' => 'controller@action',
   * ] 
   ***/

  '/login' => [
    'GET' => 'sessions@new',
    'POST' => 'sessions@create',
  ],
  '/logout' => [
    'GET' => 'sessions@destroy',
  ],

  '/users' => [
    'POST' => 'users@create',
  ],

  '/pages' => [
    'GET' => 'pages@new',
  ],

  '/dashboard' => [
    'GET' => 'application@dashboard',
  ],
];
