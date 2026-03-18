<?php

namespace Metafori\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedSetPassword extends SetPassword implements ShouldQueue
{
    use Queueable;
}
