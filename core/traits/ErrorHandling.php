<?php

namespace Core\Traits;

use Throwable;

trait ErrorHandling
{
  protected array $ERRORS = [];
  protected static $ENVIRONMENT = 'CLI';


  public function all_errors(): array
  {
    return $this->ERRORS;
  }

  public function clear_errors()
  {
    $this->ERRORS = [];
  }

  public function has_errors(): bool
  {
    return count($this->ERRORS) > 0;
  }

  public function set_errors(array $errors)
  {
    $this->ERRORS = $errors;
  }

  public function add_error(string $error)
  {
    $this->ERRORS[] = $error;
  }

  public function handle_errors($message = '')
  {
    $number_of_errors = count($this->ERRORS) > 1 ? 'Errors' : 'Error';

    // Get the backtrace information
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $calling_file = $trace[0]['file'] ?? 'Fatal';
    $calling_line = $trace[0]['line'] ?? 'Error';

    if (self::$ENVIRONMENT === 'HTML') {
      $this->show_html_errors($number_of_errors, $message, $calling_file, $calling_line);
    } else {
      $this->show_cli_errors($number_of_errors, $message, $calling_file, $calling_line);
    }
  }

  private function show_cli_errors(string $number_of_errors, string $message, string $calling_file, string $calling_line)
  {
    echo "\n";
    echo "{$number_of_errors} found!\n";

    if (!empty($this->ERRORS)) {
      foreach ($this->ERRORS as $error) {
        echo htmlspecialchars($error) . "\n";
      }
    }

    echo "{$number_of_errors} occurred during execution. Halting further execution.\n";

    if ($message && $message instanceof Throwable) {
      echo $message->getMessage() . "\n";
    } elseif ($message && is_string($message)) {
      echo   $message . "\n";
    }

    echo "{$calling_file}:{$calling_line}\n";
    die;
  }

  private function show_html_errors(string $number_of_errors, string $message, string $calling_file, string $calling_line)
  {
    $html = '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .= "<title>{$number_of_errors} Found!</title>";
    $html .= '</head>';
    $html .= '<body style="font-family: sans-serif;">';

    $html .= '<div style="width: min(900px, 100% - 8em); padding: 2em; margin-inline: auto; margin-block-start: 2em; background-color: hsl(0,60%,96%,1); border-radius: 0.5em 0.5em 0em 0em; border-top: 4px solid maroon; box-shadow: 1px 1px 1px 1px hsl(0,0%,0%,0.1);">';
    $html .= "<h2>{$number_of_errors} found:</h2>";

    if (!empty($this->ERRORS)) {
      $html .= '<ul>';
      foreach ($this->ERRORS as $error) {
        $html .= '<li>' . htmlspecialchars($error) . '</li>';
      }
      $html .= '</ul>';
    }

    $html .= '<hr style="margin-block: 2em; border: none; border-top: 2px dotted gray;">';
    $html .= '<p>' . "{$number_of_errors} occurred during execution. Halting further execution." . '</p>';

    if ($message && $message instanceof Throwable) {
      $html .= "<p style=\"word-break: break-all;\">" . $message->getMessage() . "</p>";
    } elseif ($message && is_string($message)) {
      $html .= "<p style=\"word-break: break-all;\">" . $message . "</p>";
    }
    $html .= "<p style=\"font-size: 0.75rem; text-align: right; margin-block-start: 4em;\">" . $calling_file . ":" . $calling_line . "</p>";

    $html .= '</div>';

    $html .= '</body>';
    $html .= '</html>';


    // display
    echo $html;
    die;
  }
}
