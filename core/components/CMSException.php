<?php

namespace Core\Components;

class CMSException extends \Exception
{
  public function __construct($message = "", $code = 0, \Throwable $previous = null)
  {
    $message .= "<p style=\"font-size: 0.75rem; text-align: right; margin-block-start: 4em;\">{$this->file}:{$this->line}</p>";
    parent::__construct($message, $code, $previous);
  }
}
