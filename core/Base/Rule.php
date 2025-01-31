<?php
namespace Core\Base;

class Rule
{
    public static $user_input_data = [];
    public static $rules           = [];
    public static $errors          = [];

    public static function validate($user_input_data, $rules)
    {
        self::$user_input_data = $user_input_data;
        self::$rules           = $rules;

        foreach (self::$rules as $field => $rule) {
            // Break the rule string into individual rules
            $rule_array = explode('|', $rule);

            foreach ($rule_array as $rule) {
                $rule = trim($rule);

                if ($rule == 'required') {
                    self::required($field);
                }
                if (strpos($rule, 'min:') === 0) {
                    self::min($field, $rule);
                }
                if (strpos($rule, 'max:') === 0) {
                    self::max($field, $rule);
                }
                if ($rule == 'email') {
                    self::email($field);
                }
                if ($rule == 'url') {
                    self::url($field);
                }
            }
        }

        return self::$errors;
    }

    public static function required($field)
    {
        if (empty(self::$user_input_data[$field])) {
            self::$errors[$field][] = 'The ' . $field . ' field is required';
        }
    }

    public static function email($field)
    {
        if (! filter_var(self::$user_input_data[$field], FILTER_VALIDATE_EMAIL)) {
            self::$errors[$field][] = 'The ' . $field . ' field must be a valid email address';
        }
    }

    public static function url($field)
    {
        if (! filter_var(self::$user_input_data[$field], FILTER_VALIDATE_URL)) {
            self::$errors[$field][] = 'The ' . $field . ' field must be a valid URL';
        }
    }

    public static function min($field, $rule)
    {
        $rule_array = explode(':', $rule);
        $min_length = $rule_array[1];

        if (strlen(self::$user_input_data[$field]) < $min_length) {
            self::$errors[$field][] = 'The ' . $field . ' field must be at least ' . $min_length . ' characters';
        }
    }

    public static function max($field, $rule)
    {
        $rule_array = explode(':', $rule);
        $max_length = $rule_array[1];

        if (strlen(self::$user_input_data[$field]) > $max_length) {
            self::$errors[$field][] = 'The ' . $field . ' field must be at most ' . $max_length . ' characters';
        }
    }
}
