<?php

namespace App\Livewire\Components\Tweets;

use App\Events\RetweetCountUpdated;
use App\Events\TweetWasCreated;
use App\Events\TweetWasDeleted;
use App\Notifications\RetweetNotification;
use App\TweetType;
use Illuminate\Support\Number;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Tweet as TweetModel;
class RetweetAction extends Component
{
    public TweetModel $tweet;

    public function retweet(): void
    {
        $tweet = $this->tweet;
        if ($this->tweet->original_tweet_id) {
            $tweet = $this->tweet->originalTweet;

            if (auth()->user()->hasRetweeted($tweet)) {
                return;
            }
        }

        $tweet->createRetweet();

        $this->dispatch(event: 'addTweet', tweetId: $tweet->id);
    }

    public function undoRetweet(): void
    {
        $retweet = $this->tweet;

        broadcast(new RetweetCountUpdated($retweet->originalTweet))->toOthers();
        broadcast(new TweetWasDeleted($retweet))->toOthers();

        $this->dispatch(event: 'deleteTweet', tweetId: $retweet->id);

        $retweet->deleteRetweet($retweet);
    }

    #[On('echo:tweets,RetweetCountUpdated')]
    public function getRetweetCount(): string|int
    {
        return Number::abbreviate($this->tweet->retweets->count());
    }

    public function render(): View
    {
        return view('livewire.components.tweets.retweet-action');
    }
}
