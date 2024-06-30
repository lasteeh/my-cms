<?php

namespace Core\Components;

class QueryBuilder
{
  public static function build_select_clause(array $return_columns, object $active_record_model): string
  {
    if (empty($return_columns)) {
      // return all columns
      return "*";
    }

    $valid_columns = array_filter($return_columns, function ($return_column) use ($active_record_model) {
      // validate if the column exists in the model and is a string
      return property_exists($active_record_model, $return_column) && is_string($return_column);
    });

    // if no valid columns found, return an appropriate default value
    if (empty($valid_columns)) {
      return "";
    }

    // construct the SELECT clause
    return implode(", ", $valid_columns);
  }

  public static function build_where_clause(string $sql, array $conditions): array
  {
    $placeholders = [];

    foreach ($conditions as $column => $value) {
      $placeholder = ":{$column}";

      $sql .= "{$column} = {$placeholder} AND ";
      $placeholders[$placeholder] = $value;
    }

    if (empty($conditions)) {
      $sql = rtrim($sql, " WHERE ");
    }

    return [rtrim($sql, " AND "), $placeholders];
  }

  public static function build_in_clause(string $sql, array $placeholders, string $column, array $values): array
  {
    $in_placeholders = [];

    foreach ($values as $index => $value) {
      $placeholder = ":{$column}_{$value}";
      $in_placeholders[] = $placeholder;
      $placeholders[$placeholder] = $value;
    }

    $in_clause = implode(",", $in_placeholders);
    $sql .= " AND {$column} IN ({$in_clause})";

    return [$sql, $placeholders];
  }
}
