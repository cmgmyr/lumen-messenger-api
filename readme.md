
[![Get help on Codementor](https://cdn.codementor.io/badges/get_help_github.svg)](https://www.codementor.io/cmgmyr)

# Lumen Messenger API Example

This is a simple (WIP) example of how you can use [Laravel Messenger](https://github.com/cmgmyr/laravel-messenger) and [Lumen](http://lumen.laravel.com/) to build an API.

Stay tuned for more info...

## Notes
1. The `irazasyed/larasupport` package was installed in order to use the `config_path()` helper function, and the `vendor:publish` artisan command.
2. The following was added to `bootstrap/app.php`:

		$app->configure('messenger');
		$app->register('Cmgmyr\Messenger\MessengerServiceProvider');
		$app->register('Irazasyed\Larasupport\Providers\ArtisanServiceProvider');
		
	and uncomment: `$app->withFacades();`, `$app->withEloquent();` and `'Illuminate\Session\Middleware\StartSession',` (within Middleware)
