<?php
namespace johnnynotsolucky\RegexHandler;

use Monolog\Handler\AbstractHandler;

class Handler extends AbstractHandler
{
    private $rules;

    public function __construct($rules)
    {
        $this->rules = $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        foreach ($this->rules as $item) {
            if (!is_array($item) || count($item) === 1) {
                $item = ['message', is_array($item) ? $item[0] : $item];
            }

            [$key, $pattern] = $item;

            if (is_array($key)) {
                $value = array_reduce(
                    $key,
                    function ($arr, $key) {
                        return $arr[$key] ?? null;
                    },
                    $record
                );
            } else {
                $value = $record[$key] ?? null;
            }

            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false === $this->bubble;
    }
}
