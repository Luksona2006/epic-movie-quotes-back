<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentQuote implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $quoteId;
    public $comment;
    public $isOwnQuote;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($quoteId, $comment, $isOwnQuote)
    {
        $this->quoteId = $quoteId;
        $this->comment = $comment;
        $this->isOwnQuote = $isOwnQuote;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('comments');
    }
}
