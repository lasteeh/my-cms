<?php

return [
  /***
   * format:
   * 'url' => [
   * 'http_method' => 'controller@action',
   * ] 
   ***/

  // default routes. delete or edit depending on your needs
  '/' => [
    'GET' => 'application@index',
  ],

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
