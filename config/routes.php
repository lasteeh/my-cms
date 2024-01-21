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

  '/test' => [
    'GET' => 'application@index',
    'POST' => 'application@create',
  ],
  '/test/new' => [
    'GET' => 'application@new',
  ],
  '/test/:id' => [
    'GET' => 'application@show',
    'PATCH' => 'application@update',
    'DELETE' => 'application@delete',
  ],
  '/test/:id/edit' => [
    'GET' => 'application@index',
  ],
];
