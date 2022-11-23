# laravel-azure-calendar

Laravel handler to add events to Azure Calendars.

## Installation

Require this package with composer.

```bash
$ composer require ansjabar/laravel-azure-calendar
```

## Integration

```bash
$ php artisan vendor:publish --provider="AnsJabar\LaravelAzureCalendar\CalendarServiceProvider"
```

Add `AZURE_CLIENT_ID`, `AZURE_CLIENT_SECRET` and '`AZURE_REDIRECT_BACK` to your `.env` file.

Add result of following code to Redirect URL

```php
config('app.url').'/azure-calendar/callback' // http://localhost:8000/azure-calendar/callback
```

## Usasge
```php
(new \AnsJabar\LaravelAzureCalendar\Calendar(
    $from, \\ Must be instance of Carbon
    $to, \\ Must be instance of Carbon
    'Summary of the event'
))->createEvent();
```
## License

This laravel-teams-logger package is available under the MIT license. See the LICENSE file for more info.
