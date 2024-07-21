<?php

return [

  // default homepage. change the controller@action to customize homepage
  '/' => [
    'GET' => 'application@home',
  ],

  /***
   * format:
   * '/url' => [
   * 'HTTP_METHOD' => 'controller@action',
   * ] 
   ***/

  '/login' => [
    'GET' => 'sessions@new',
    'POST' => 'sessions@create',
  ],
  '/logout' => [
    'GET' => 'sessions@destroy',
  ],

  // '/users' => [
  //   'POST' => 'users@create',
  // ],

  '/dashboard' => [
    'GET' => 'application@dashboard',
  ],

  // Pages
  // '/dashboard/pages' => [
  //   'GET' => 'pages@index',
  //   'POST' => 'pages@create',
  // ],
  // '/dashboard/pages/:id' => [
  //   'POST' => 'pages@update',
  // ],
  // '/dashboard/pages/new' => [
  //   'GET' => 'pages@new',
  // ],
  // '/dashboard/pages/:id/edit' => [
  //   'GET' => 'pages@edit',
  // ],
  // '/dashboard/pages/:id/delete' => [
  //   'POST' => 'pages@delete',
  // ],

  // Posts
  // '/dashboard/posts' => [
  //   'GET' => 'posts@index',
  //   'POST' => 'posts@create',
  // ],
  // '/dashboard/posts/new' => [
  //   'GET' => 'posts@new',
  // ],
  // '/dashboard/posts/:id' => [
  //   'POST' => 'posts@update',
  // ],
  // '/dashboard/posts/:id/edit' => [
  //   'GET' => 'posts@edit',
  // ],
  // '/dashboard/posts/:id/delete' => [
  //   'POST' => 'posts@delete',
  // ],
  // '/post/:title' => [
  //   'GET' => 'posts@show',
  // ],

  // Cities
  '/dashboard/cities' => [
    'GET' => 'cities@index',
    'POST' => 'cities@create',
  ],
  '/dashboard/cities/:id' => [
    'POST' => 'cities@update',
  ],
  '/dashboard/cities/:id/edit' => [
    'GET' => 'cities@edit',
  ],
  '/dashboard/cities/:id/delete' => [
    'POST' => 'cities@delete',
  ],

  // Leads
  '/dashboard/leads' => [
    'GET' => 'leads@index',
  ],
  '/dashboard/leads/batch/add' => [
    'POST' => 'leads@batch_add',
  ],
  '/dashboard/leads/assign' => [
    'GET' => 'leads@assign',
  ],
  '/dashboard/leads/:category' => [
    'GET' => 'leads@categorize',
  ],
  '/dashboard/leads/:category/:sub_category' => [
    'GET' => 'leads@categorize',
  ],
  '/dashboard/leads/toggle/:property/:id' => [
    'POST' => 'leads@toggle',
  ],
  '/dashboard/leads/export/:area/:category' => [
    'POST' => 'leads@export',
  ],

  // keyword "no_match" sets catch-all 
  'no_match' => [
    'GET' => 'application@not_found', // default value 'GET' => 'application@not_found'
  ],
];
