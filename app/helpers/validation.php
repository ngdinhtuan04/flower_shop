<?php
/**
 * Helper functions cho validation
 */

class Validator
{
    private $errors = [];
    private $data = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' không được để trống';
        }
        return $this;
    }

    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Email không hợp lệ';
        }
        return $this;
    }

    public function minLength($field, $length, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? ucfirst($field) . " phải có ít nhất {$length} ký tự";
        }
        return $this;
    }

    public function maxLength($field, $length, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? ucfirst($field) . " không được vượt quá {$length} ký tự";
        }
        return $this;
    }

    public function match($field, $matchField, $message = null)
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) && 
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' không khớp';
        }
        return $this;
    }

    public function phone($field, $message = null)
    {
        if (isset($this->data[$field])) {
            $pattern = '/^(0|\+84)[1-9][0-9]{8}$/';
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field] = $message ?? 'Số điện thoại không hợp lệ';
            }
        }
        return $this;
    }

    public function unique($field, $table, $column, $exceptId = null, $message = null)
    {
        if (isset($this->data[$field])) {
            $db = DB::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $params = [$this->data[$field]];

            if ($exceptId) {
                $sql .= " AND id != ?";
                $params[] = $exceptId;
            }

            $result = $db->fetchOne($sql, $params);
            if ($result && $result['count'] > 0) {
                $this->errors[$field] = $message ?? ucfirst($field) . ' đã tồn tại';
            }
        }
        return $this;
    }

    public function custom($field, $callback, $message = null)
    {
        if (isset($this->data[$field])) {
            if (!call_user_func($callback, $this->data[$field])) {
                $this->errors[$field] = $message ?? ucfirst($field) . ' không hợp lệ';
            }
        }
        return $this;
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function passes()
    {
        return empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function error($field)
    {
        return $this->errors[$field] ?? null;
    }

    public function firstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}

function validate($data)
{
    return new Validator($data);
}

function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateOTP($length = 6)
{
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}