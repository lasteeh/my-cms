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

  '/dashboard' => [
    'GET' => 'application@dashboard',
  ],

  '/dashboard/pages' => [
    'GET' => 'pages@index',
    'POST' => 'pages@create',
  ],
  '/dashboard/pages/:id' => [
    'POST' => 'pages@update',
  ],
  '/dashboard/pages/new' => [
    'GET' => 'pages@new',
  ],
  '/dashboard/pages/:id/edit' => [
    'GET' => 'pages@edit',
  ],
];
