<?php


function config(string $config_name)
{
  $config_parts = explode('.', $config_name);
  $config_file_name = $config_parts[0];
  $config_key = $config_parts[1];

  $path = __DIR__ . '/../../config/' . $config_file_name . '.php';

  // check if the file exists
  if (!file_exists($path)) throw new Exception("Configuration file '$config_file_name' not found.");

  // require the file to load its configuration array
  $config = require_once $path;
  $key = $config[$config_key] ?? null;

  if (!$key) throw new Exception("Key '$config_key' not defined.");

  // return the loaded configuration
  return $key;
}
