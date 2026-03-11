<?php

namespace App\Exceptions;

use Exception;

class CommentCannotBeDeletedException extends Exception
{
    protected $message = 'Cannot delete comment from a finalized incidence';
}