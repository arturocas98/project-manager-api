<?php

namespace App\Exceptions;

use Exception;

class ProjectException extends Exception
{
    protected $message;

    protected $code;

    public function __construct(string $message, int $code = 400)
    {
        $this->message = $message;
        $this->code = $code;
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'error' => true,
            'message' => $this->message,
            'code' => $this->code,
        ], $this->code);
    }
}
