# Lumen Messenger API Example

This is a simple (WIP) example of how you can use [Laravel Messenger](https://github.com/cmgmyr/laravel-messenger) and [Lumen](http://lumen.laravel.com/) to build an API.

Stay tuned for more info...

## Notes
1. Lumen doesn't have an `php artisan vendor:publish` command, so `config/messenger.php` and the `migrations` have been moved over by hand.
2. `config_path` is not available in Lumen, so `src/helpers.php` has been made and added to `composer.json`
3. The following was added to `bootstrap/app.php`:

		$app->configure('messenger');
		$app->register('Cmgmyr\Messenger\MessengerServiceProvider');
		
	and uncomment: `$app->withFacades();` and `$app->withEloquent();`
4. Route prefixes aren't available in Lumen, so the full route needs to be provided in `app/Http/routes.php`