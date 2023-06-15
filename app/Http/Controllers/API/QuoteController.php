<?php

namespace App\Http\Controllers\API;

use App\Events\CommentQuote;
use App\Events\LikeQuote;
use App\Events\RecieveNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Movie;
use App\Models\Notification;
use App\Models\Quote;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function create(CreateQuoteRequest $request): JsonResponse
    {
        $attributes['movie_id'] = $request->movie_id;
        $user = User::where('token', $request->user_token)->first();
        $attributes['user_id'] = $user->id;
        $attributes['text'] = [
            'en' => $request->quote_en,
            'ka' => $request->quote_ka
        ];

        $image = $request->image;
        $extension = explode(';', explode('/', $image)[1])[0];
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(30) . '.' . $extension;

        Storage::put('quoteImages/' . $imageName, base64_decode($image));

        $attributes['image'] = 'quoteImages/' .  $imageName;

        $quote = Quote::create($attributes);

        if($quote) {
            return response()->json(['quote' => $quote->getFullData()]);
        }

        return response()->json(['message', 'Something went wrong, please check provided details and try again']);
    }

    public function update(int $id, UpdateQuoteRequest $request): JsonResponse
    {
        $quote = Quote::where('id', $id)->first();
        $quoteUser = User::where('id', $quote->user_id)->first();
        $user = User::where('token', $request->user_token)->first();

        if($quote && $user) {
            if($user->id === $quote->user_id) {
                if($request->quote_en && $request->quote_ka) {
                    $text = [
                        'en' => $request->quote_en ?? $quote->toArray()['text']['en'],
                        'ka' => $request->quote_ka ?? $quote->toArray()['text']['ka'],
                    ];

                    $quote->text = $text;
                };

                if($request->image) {
                    $image = $request->image;
                    $extension = explode(';', explode('/', $image)[1])[0];
                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(30) . '.' . $extension;


                    Storage::delete($quote->image);
                    Storage::put('quoteImages/' . $imageName, base64_decode($image));

                    $quote->image = 'quoteImages/' .  $imageName;
                }

                $quote->save();
            }


            $likes = Like::where('quote_id', $quote->id)->get()->toArray();
            $likesSum = count($likes);
            $liked = array_filter($likes, function ($like) use ($user) {
                return $like['user_id'] === $user->id;
            });

            $quote['liked'] = count($liked) ? true : false;

            if($request->liked !== null) {
                if($request->liked === true) {
                    Like::create([
                        'user_id' => $user->id,
                        'quote_id' => $quote->id
                    ]);

                    $likesSum = $likesSum + 1;
                    $quote['liked'] = true;
                }

                if($request->liked === false) {
                    $likeId = Like::where([
                        ['user_id', '=', $user->id],
                        ['quote_id', '=', $quote->id]
                    ])->first()->id;

                    Like::destroy($likeId);

                    $likesSum = $likesSum - 1;
                    $quote['liked'] = false;
                }

                if($user->id !== $quoteUser->id) {
                    UserNotification::create(['from_user_id' => $user->id, 'to_user_id' => $quote->user_id]);
                    $notification = Notification::create(['user_id' => $user->id,'quote_id' => $quote->id, 'type' => 'like']);
                    $notificationFullData = [...$notification->toArray()];
                    $notificationFullData['user'] = $user;
                    event(new RecieveNotification($quoteUser->token, $notificationFullData));
                }

                $isOwnQuote = $user->id === $quote->id;
                event(new LikeQuote($quote->id, $likesSum, $isOwnQuote));
            }

            $quote['likes'] = $likesSum;


            if($request->comment) {
                $comment = Comment::create([
                    'text' => $request->comment,
                    'quote_id' => $quote->id,
                    'user_id' => $user->id
                ]);

                $comment->user;

                if($user->id !== $quoteUser->id) {
                    UserNotification::create(['from_user_id' => $user->id, 'to_user_id' => $quote->user_id]);
                    $notification = Notification::create(['user_id' => $user->id,'quote_id' => $quote->id, 'type' => 'comment']);
                    $notificationFullData = [...$notification->toArray()];
                    $notificationFullData['user'] = $user;
                    event(new RecieveNotification($quoteUser->token, $notificationFullData));
                }

                $isOwnQuote = $user->id === $quote->id;
                event(new CommentQuote($quote->id, $comment, $isOwnQuote));
            }

            $quote['movie'] = $quote->movie;
            $quote['author'] = $quoteUser;
            $comments = Comment::where('quote_id', $quote->id)->get()->toArray();

            $commentsWithUsers = [];

            if(count($comments)) {
                $commentsWithUsers = array_map(function ($comment) {
                    $comment['user'] = User::where('id', $comment['user_id'])->first();
                    return $comment;
                }, $comments);
            }

            $quote['comments'] = $commentsWithUsers;

            return response()->json(['quote' => $quote]);
        }

        return response()->json(['message' => 'Wrong id, no quote found'], 404);
    }

    public function remove(int $id, Request $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();

        if($user) {
            $quote = Quote::where('id', $id)->where('user_id', $user->id)->first();
            if($quote) {
                $quote->delete();
                return response()->json(['message' => 'Quote deleted successfully']);
            }

            return response()->json(['message' => 'Wrong id, no quote found'], 404);
        }

        return response()->json(['message' => 'You are not able to remove quote'], 404);
    }

    public function getAllQuotes(string $userToken): JsonResponse
    {
        $user = User::where('token', $userToken)->first();
        if($user) {
            $quotes = $user->quotes()->orderBy('created_at', 'desc')->get()->toArray();
            $quotesFullData = array_map(function ($quote) {
                $quoteModel = Quote::find($quote['id']);

                return $quoteModel->getFullData();
            }, $quotes);

            return response()->json(['quotes' => $quotesFullData]);
        };

        return response()->json(['message' => 'You are not able to get quotes'], 401);
    }

    public function getQuote(string $userToken, int $quoteId): JsonResponse
    {
        $user = User::where('token', $userToken)->first();
        if($user) {
            $quote = Quote::where('user_id', $user->id)->where('id', $quoteId)->first();
            if($quote) {
                return response()->json(['quote' => $quote->getFullData()]);
            }

            return response()->json(['message' => 'Quote not found'], 404);
        };

        return response()->json(['message' => 'You are not able to get movie'], 401);
    }

    public function filterQuotes(Request $request): JsonResponse
    {
        $user = User::where('token', $request->user_token)->first();
        if($user) {
            $search = $request->searchBy;
            if($search[0] === '#') {
                $search = ltrim($search, '#');
                $quotes = Quote::whereRaw('LOWER(JSON_EXTRACT(text, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(text, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->orderBy('created_at', 'desc')
                ->get()->toArray();

                $updatedQuotes = [];
                foreach ($quotes as $quote) {
                    $quoteModel = Quote::find($quote['id']);
                    array_push($updatedQuotes, $quoteModel->getFullData());
                };

                return response()->json(['quotes' => $updatedQuotes]);
            }

            if($search[0] === '@') {
                $search = ltrim($search, '@');
                $movies = Movie::whereRaw('LOWER(JSON_EXTRACT(name, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->get()->toArray();

                $updatedQuotes = [];
                foreach ($movies as $movie) {
                    $movieQuotes = Movie::where('id', $movie['id'])->first()->quotes->toArray();

                    foreach ($movieQuotes as $quote) {
                        $quoteModel = Quote::find($quote['id']);
                        array_push($updatedQuotes, $quoteModel->getFullData());
                    };
                };

                return response()->json(['quotes' => $updatedQuotes]);
            }

            return response()->json(['message' => 'Use # or @ at the beginning', 'quotes' => []], 204);
        }


        return response()->json(['message' => 'You are not able to search for quotes'], 401);
    }
}
