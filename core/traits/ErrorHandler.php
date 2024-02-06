<?php

namespace Core\Traits;

trait ErrorHandler
{
  public function run_error_check()
  {
    if (!empty($this->ERRORS)) {
      // throw an exception to halt the execution
      throw new \Exception();
    }
  }

  public function exception_handler($exception)
  {
    $number_of_errors = count($this->ERRORS) > 1 ? 'Errors' : 'Error';

    $html = '<div style="width: min(900px, 100% - 8em); padding: 2em; margin-inline: auto; margin-block-start: 2em; background-color: hsl(0,60%,96%,1); border-radius: 0.5em 0.5em 0em 0em; border-top: 4px solid maroon; box-shadow: 1px 1px 1px 1px hsl(0,0%,0%,0.1);">';
    $html .= "<h2>{$number_of_errors} found:</h2>";
    $html .= '<ul>';

    foreach ($this->ERRORS as $error) {
      $html .= '<li>' . htmlspecialchars($error) . '</li>';
    }

    $html .= '</ul>';
    $html .= '<hr style="margin-block: 2em; border: none; border-top: 2px dotted gray;">';
    $html .= '<p>' . "{$number_of_errors} occurred during execution. Halting further execution." . '</p>';
    $html .= $exception->getMessage();
    $html .= '</div>';

    // display the HTML or log it, based on your requirements
    echo $html;
  }
}
