<?php

namespace Core\Components;

class CMSException extends \Exception
{
  public function __construct($message = "", $code = 0, \Throwable $previous = null)
  {
    $message .= nl2br(PHP_EOL) . nl2br(PHP_EOL . "{$this->file}:{$this->line}");
    parent::__construct($message, $code, $previous);
  }
}
