<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentObserver
{

    public function created(FilamentComment $comment)
    {
        $user = User::findOrFail($comment->user->id);
        $order = Order::findOrFail($comment->subject->id);

        Log::debug('CommentObserver started on ' . $order->id . ' by ' . $user->name . $user->isAdmin());

        if(!$user->isAdmin()) {
            Log::debug('New comment by Partner User on ' . $order->id);
        }
    }
}
