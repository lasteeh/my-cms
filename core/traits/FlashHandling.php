<?php

namespace Core\Traits;

trait FlashHandling
{
  public function has_flash(string $message_type = ''): bool
  {
    if (empty($message_type)) {
      return array_key_exists('VIEW_MESSAGES', $_SESSION) && !empty($_SESSION['VIEW_MESSAGES']);
    } else {
      return array_key_exists('VIEW_MESSAGES', $_SESSION) && array_key_exists($message_type, $_SESSION['VIEW_MESSAGES']) && !empty($_SESSION['VIEW_MESSAGES'][$message_type]);
    }
  }

  public function get_flash(string $message_type): array
  {
    return $_SESSION['VIEW_MESSAGES'][$message_type] ?? [];
  }

  protected function set_flash(string $type, array $messages_for_type_array)
  {
    // initialize VIEW_MESSAGES session variable if not set
    if (!isset($_SESSION['VIEW_MESSAGES'])) {
      $_SESSION['VIEW_MESSAGES'] = [];
    }

    $_SESSION['VIEW_MESSAGES'][$type] = $messages_for_type_array;
  }

  protected function clear_flash()
  {
    if (isset($_SESSION['VIEW_MESSAGES'])) {
      unset($_SESSION['VIEW_MESSAGES']);
    }
  }
}
