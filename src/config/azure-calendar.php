<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google App Client Id
    |--------------------------------------------------------------------------
    */
    'client_id'           => env('AZURE_CLIENT_ID'),
    'client_secret'       => env('AZURE_CLIENT_SECRET'),
    'authorize_url'       => 'https://login.microsoftonline.com/common',
    'authorize_endpoint'  => '/oauth2/v2.0/authorize',
    'token_endpoint'      => '/oauth2/v2.0/token',
    'resource_id'         => 'ttps://graph.microsoft.com',
    'scopes'              => 'profile openid email offline_access User.Read Mail.Send Calendars.ReadWrite',
    'client_redirect_url' => env('AZURE_REDIRECT_BACK'),
];