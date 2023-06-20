<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeQuote implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $quoteId;
    public $likes;
    public $isOwnQuote;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($quoteId, $likes, $isOwnQuote)
    {
        $this->quoteId = $quoteId;
        $this->likes = $likes;
        $this->isOwnQuote = $isOwnQuote;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('likes');
    }
}
