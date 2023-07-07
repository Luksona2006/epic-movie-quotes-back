<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Quote $quote)
    {
        return $user->id === $quote->user_id;
    }

    public function update(User $user, Quote $quote)
    {
        return $user->id === $quote->user_id;
    }

    public function delete(User $user, Quote $quote)
    {
        return $user->id === $quote->user_id;
    }

}
