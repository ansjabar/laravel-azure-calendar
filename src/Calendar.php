<?php

namespace AnsJabar\LaravelAzureCalendar;

use Carbon\Carbon;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use AnsJabar\LaravelAzureCalendar\Models\AzureCalendarToken;

class Calendar
{
    private $provider, $to, $from, $summary;
    public function __construct(Carbon $from = null, Carbon $to = null, string $summary = null)
    {
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}
        
		$this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
		    'clientId'                => config('azure-calendar.client_id'),
		    'clientSecret'            => config('azure-calendar.client_secret'),
		    'redirectUri'             => config('app.url').'/azure-calendar/callback',
		    'urlAuthorize'            => config('azure-calendar.authorize_url') . config('azure-calendar.authorize_endpoint'),
		    'urlAccessToken'          => config('azure-calendar.authorize_url') . config('azure-calendar.token_endpoint'),
		    'urlResourceOwnerDetails' => '',
		    'scopes'                  => config('azure-calendar.scopes'),
		]);
        if($this->from = $from && $this->to = $to && $this->summary = $summary)
        {
            $_SESSION['azure_calendar_event_from'] = $from->format('Y-m-d').'T'.$from->startOfMinute()->format('H:i:s');
            $_SESSION['azure_calendar_event_to'] = $to->format('Y-m-d').'T'.$to->startOfMinute()->format('H:i:s');
            $_SESSION['azure_calendar_event_summary'] = $summary;
            $_SESSION['azure_calendar_timezone'] = $from->tzName;
        }
    }
    public function createEvent()
    {
        if(!$this->from || !$this->to || !$this->summary)
            throw new \Exception("From , To and Summary properties are missing.");
        return $this->redirectToAzure();
    }
    private function redirectToAzure()
    {
        if (isset($_SESSION['azure_calendar_access_token']) && $token_details = AzureCalendarToken::whereToken( $_SESSION['azure_calendar_access_token'] )->where('expiry', '>', now()->toDateString())->first()) 
        {
            $access_token = $token_details->token;
            $timezone = $token_details->timezone;
            return self::createCalendarEvent($access_token, $timezone);
        }
        else 
        {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $_SESSION['state'] = $this->provider->getState();
            return redirect($authorizationUrl);
        }
    }

	public function createCalendarEvent($access_token) 
    {
		$url = 'https://graph.microsoft.com/v1.0/me/events';
        $headers = ['Authorization' => 'Bearer ' . $access_token];
        $request = [
            'start' => ['dateTime' => $_SESSION['azure_calendar_event_from'], 'timeZone' => $_SESSION['azure_calendar_timezone']],
            'end' => ['dateTime' => $_SESSION['azure_calendar_event_to'], 'timeZone' => $_SESSION['azure_calendar_timezone']],
            'subject' => $_SESSION['azure_calendar_event_summary'],
        ];
        $response = Http::withHeaders($headers)->post($url, $request);
        return redirect()->route(config('azure-calendar.client_redirect_url'));
	}
    public function callbackFromAzure(Request $request)
    {
        if (isset($_SESSION['azure_calendar_access_token']) && $token_details = AzureCalendarToken::whereToken( $_SESSION['azure_calendar_access_token'] )->where('expiry', '>', now()->toDateString())->first()) 
        {
            $access_token = $token_details->token;
        }
        else
        {
            $response = $this->getAccessToken($request->code);
            $access_token = $response->access_token;
            $_SESSION['azure_calendar_access_token'] = $access_token;
            AzureCalendarToken::create(['token' => $access_token, 'expiry' => now()->addSeconds($response->expires_in)->subSeconds(60)->toDateTimeString(), 'timezone' => $_SESSION['azure_calendar_timezone']]);
        }
        return Calendar::createCalendarEvent($access_token);
    }
	private function getAccessToken($code) 
    {
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code'     => $code
        ]);
        $values = $accessToken->getValues();
        $idToken = $values['id_token'];
        $decodedAccessTokenPayload = base64_decode(
            explode('.', $idToken)[1]
        );
        $jsonAccessTokenPayload = json_decode($decodedAccessTokenPayload, true);
        $token = $accessToken->getToken();
        $tokenResponse = new \StdClass();
        $tokenResponse->access_token = $token;
        $tokenResponse->expires_in = $values['ext_expires_in'];
        return $tokenResponse;
	}
}
