<?php

namespace App\Widgets;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Facades\Auth;

class WeekActivityWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $now = Carbon::now();

        // Calculate the date one week ago from now
        $oneWeekAgo = $now->subWeek();

        // Query to get the number of users who logged in within the last week
        $userCount = User::where('last_login_at', '>=', $oneWeekAgo)->count();

        $string = "Number of active users for the last week: ". $userCount;

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-activity',
            'title'  => "User Activity",
            'text'   => $string,
            'button' => [
                'text' => "Check users",
                'link' => route('voyager.users.index'),
            ],
            'image' => asset('storage/widgets/stats.jpg'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Voyager::model('User'));
    }
}
