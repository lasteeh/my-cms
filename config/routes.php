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

  '/login' => [
    'GET' => 'sessions@new',
    'POST' => 'sessions@create',
  ],

  '/users' => [
    'POST' => 'users@create',
  ],
];
