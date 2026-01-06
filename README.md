# TeamQ StarterKIT For Laravel

## Development

---

Use the "app" `./bin/app` command interface, where you can execute commands for the containers in your
`docker-compose.yml`.

If you need to make changes in your `docker-compose.yml`, please use `docker-compose.override.yml`.

Once you have set your `.env` environment variables, you can run the application.

### 1. Set up your environment **(Run this only the first time)**

Run the `copy` command to copy the environment variables from the example files
to the development files  (`.env`).

```shell
bin/app copy
```

Run the `install` command to install the necessary dependencies for `composer` and for `npm`.

```shell
bin/app install
```

### 2. Run application

Run and up the docker containers.

```shell
bin/app build
bin/app up -d
```

### 3. Set up application

Once your container is up you can run the artisan command `app:install`, this command will run the
`migrations` and `seeders`, as well as set the `APP_KEY` if it has not been set and create the `passport keys`,
it also compiles the assets from the `scribe documentation` of your API.

```shell
bin/app artisan app:install
```

It is not mandatory to use it, but if you don't, you will have to configure everything yourself, manually.

### 4. Stop application

```shell
bin/app stop
```

### 5. Installing packages

```shell
bin/app composer require laravel/telescope
```

```shell
bin/app npm install axios
```

### 6. Run laravel commands

```shell
bin/app artisan migrate
```

### 7. Format code

```shell
bin/app pint
```

### 8. Run tests

```shell
bin/app test
```

### 9. Command help

To see everything you can do with the app command interface, run the following in your console.

```shell
bin/app -h
```

## Writing code

### Code Style ([Laravel Pint](https://laravel.com/docs/pint))

Before writing code please read the following [guidelines](https://spatie.be/guidelines/laravel-php), this will ensure
that you write readable and easy to understand code. Configure your code editor to use the
settings [.editorconfig](.editorconfig). Whenever possible, use `bin/app pint` to format your code.

```shell
bin/app pint
```

### Tests ([Pest PHP](https://pestphp.com/))

Every time you write a feature, do it together with its respective automated tests. You can be guided by the tests
established in this starter kit, you can also consult the [Pest PHP](https://pestphp.com/) documentation to
write your tests.

Follow the following flow to ensure the integrity of the application.

1. Every time you download new changes or create a new working branch, run the `bin/app test` tests.
2. Start by writing the scenarios and possible use cases in tests.
3. Run your tests (they should fail).
4. Write the minimum code that makes your tests pass (your tests must pass).
5. Run all tests (they should all pass).
6. Refactor your code, making it readable and maintainable.
7. Run all tests (they should all pass).
8. Submit your changes.

```shell
bin/app test
```

### API Documentation ([Scribe](https://scribe.knuckles.wtf/))

Every time you write a new endpoint, divide it into segments, for example if you have an "Inventory" work area or
module, create a routes file `routes/api/_inventory.php` for this module, and include it in your `routes/api.php` file.
Now this file will only contain the controllers found within `app/Http/Controllers/Inventory`. And each controller must
have a group and a subgroup associated with it (whenever possible), also indicate whether your endpoint requires
authentication, this will help the code to be more organized and make the documentation easy to read. E.g.:

```php
#[Group('Auth')]
#[Subgroup('Email Verification')]
#[Authenticated]
#[Response(content: ['status' => 'We have sent you a new verification email.'])]
#[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
class EmailVerificationController extends Controller
{
    /**
     * Resend verification email
     *
     * Send a new email verification notification.
     */
    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [__('email-verification.already-been-verified')],
            ]);
        }

        $request->user()->notify(new VerifyEmail);

        return new JsonResponse(['status' => __('email-verification.verification-link-sent')]);
    }
}
```

```shell
bin/app artisan scribe:generate
```

Please be aware that you will not be the only one to read that code, so write your code with others in mind.

#### Happy code! 😛
