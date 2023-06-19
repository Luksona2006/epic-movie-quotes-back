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
        $user = auth()->user();
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

        return response()->json(['message', __('messages.invalid_credentials')], 401);
    }

    public function update(int $id, UpdateQuoteRequest $request): JsonResponse
    {
        $quote = Quote::find($id);
        $quoteUser = User::find($quote->user_id);
        $user = auth()->user();

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
                    event(new RecieveNotification($quoteUser->id, $notificationFullData));
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
                    event(new RecieveNotification($quoteUser->id, $notificationFullData));
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
                    $comment['user'] = User::find($comment['user_id']);
                    return $comment;
                }, $comments);
            }

            $quote['comments'] = $commentsWithUsers;

            return response()->json(['quote' => $quote]);
        }

        return response()->json(['message' => __('messages.wrong_id')], 404);
    }

    public function remove(int $id): JsonResponse
    {
        $user = auth()->user();

        if($user) {
            $quote = Quote::find($id);
            if($quote) {
                $quote->delete();
                return response()->json(['message' => __('messages.deleted_successfully', ['deleted' => __('messages.quote')])]);
            }

            return response()->json(['message' => __('messages.wrong_id')], 404);
        }

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.remove_quote')])], 404);
    }

    public function paginateQuotes(int $pageNum): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $quotesPaginate = Quote::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $pageNum)->toArray();
            $quotes = $quotesPaginate['data'];
            $quotesFullData = array_map(function ($quote) {
                $quoteModel = Quote::find($quote['id']);

                $quoteFullData = $quoteModel->getFullData();
                $quotesFullData['commentsTotal'] = count($quoteModel->comments->toArray());
                $quoteFullData['comments'] = array_slice($quoteModel->comments->toArray(), -2);

                return [...$quoteFullData, 'commentsTotal' => count($quoteModel->comments->toArray())];
            }, $quotes);




            return response()->json(['quotes' => $quotesFullData, 'isLastPage' => $quotesPaginate['last_page'] === $pageNum]);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_quotes')])], 401);
    }

    public function getAllComments(int $quoteId)
    {
        $user = auth()->user();
        if($user) {
            $quote = Quote::find($quoteId);
            $quoteFullData = $quote->getFullData();

            return response()->json(['comments' => $quoteFullData['comments']]);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_comments')])], 401);
    }

    public function getQuote(int $quoteId): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $quote = Quote::find($quoteId);
            if($quote) {
                return response()->json(['quote' => $quote->getFullData()]);
            }

            return response()->json(['message' => __('messages.not_found', ['notFound' => __('messages.quote')])], 404);
        };

        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.get_quote')])], 401);
    }

    public function filterQuotes(Request $request): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            $search = $request->searchBy;
            if($search[0] === '#') {
                $search = ltrim($search, '#');
                $quotesPaginate = Quote::whereRaw('LOWER(JSON_EXTRACT(text, "$.en")) like ?', '%'.strtolower($search).'%')
                ->orWhereRaw('LOWER(JSON_EXTRACT(text, "$.ka")) like ?', '%'.strtolower($search).'%')
                ->orderBy('created_at', 'desc')->paginate(10, ['*'], 'quotes-per-page', $request->pageNum)->toArray();

                $quotes = $quotesPaginate['data'];

                $updatedQuotes = [];
                foreach ($quotes as $quote) {
                    $quoteModel = Quote::find($quote['id']);
                    array_push($updatedQuotes, $quoteModel->getFullData());
                };

                return response()->json(['quotes' => $updatedQuotes, 'isLastPage' => $quotesPaginate['last_page'] === $request->pageNum]);
            }


            return response()->json(['quotes' => []], 204);
        }


        return response()->json(['message' => __('messages.you_are_not_able_to', ['notAbleTo' => __('messages.search_for_quotes')])], 401);
    }
}
