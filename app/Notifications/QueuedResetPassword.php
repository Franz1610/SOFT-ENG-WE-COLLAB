<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Optionally customize the queue or connection.
     */
    public $connection;
    public $queue;

    public function __construct(string $token)
    {
        parent::__construct($token);
        // Use default queue connection; set a specific queue name if desired
        $this->queue = 'default';
    }
}
