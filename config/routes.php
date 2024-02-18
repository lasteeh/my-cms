<?php

return [

  // default homepage. change the controller@action to customize homepage
  '/' => [
    'GET' => 'application@index',
  ],

  /***
   * format:
   * 'url' => [
   * 'http_method' => 'controller@action',
   * ] 
   ***/

  '/register' => [
    'GET' => 'users@new',
  ],

  '/login' => [
    'GET' => 'sessions@new',
  ],

  '/users' => [
    'GET' => 'users@index',
    'POST' => 'users@create',
  ],
];
