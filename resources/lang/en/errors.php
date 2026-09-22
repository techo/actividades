<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Error screens
    |--------------------------------------------------------------------------
    | TECHO tone: human, reassuring and with a clear way out.
    */

    'back_home' => 'Back to home',
    'code'      => 'Error :n',

    'e500' => [
        'title'       => 'Something went wrong',
        'message'     => "We had a problem on our end and couldn't finish what you were doing. It's already logged and we're looking into it. Please try again in a moment.",
        'report'      => 'Report this problem',
        'report_hint' => 'As a coordinator/administrator, you can report it so the team can review it.',
    ],

    'e404' => [
        'title'   => "We couldn't find this page",
        'message' => "The page you're looking for doesn't exist or has moved. Check the address or go back home.",
    ],

    'e403' => [
        'title'   => "You don't have access to this",
        'message' => "This section requires permissions your account doesn't have. If you think this is a mistake, reach out to your team's coordinator.",
    ],

    'e503' => [
        'title'   => "We're doing maintenance",
        'message' => "We're making a quick update to the system. Please try again in a few minutes. Thanks for your patience!",
    ],

    'e419' => [
        'title'     => 'The page timed out',
        'message'   => "Your session expired because the page was open too long. You didn't lose anything — we'll take you back so you can continue.",
        'retry'     => 'CONTINUE',
        'countdown' => 'Redirecting you in :seconds…',
    ],

];
