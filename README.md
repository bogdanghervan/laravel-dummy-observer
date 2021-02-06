# Laravel Dummy Observer

A purpose-built model observer that can registered with an Eloquent model to intercept all attempted saves and perform assertions on the data. The data being saved never reaches the database.

## Installation

### Requirements

PHP ≥ 7.3

PHPUnit ≥ 9.5

Laravel ≥ 5.3

### Installation

Install it via Composer
```
composer require --dev bogdanghervan/laravel-dummy-observer
```

## Usage

Let's assume we'd like to test a method called `landed` on a model called `Flight`. This method would update the flight's status by calling `save` internally. 

```PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public function landed()
    {
        $this->status = 'landed';
        $this->save();
    }
}

```

This is how a unit test could look like:
```PHP
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Flight;
use BogdanGhervan\DummyObserver;

class FlightTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent model from being saved
        Flight::observe(DummyObserver::class);
    }

    protected function tearDown(): void
    {
        DummyObserver::clear();
    }

    protected function testLanded(): void
    {
        $flight = new Flight([
            'departure' => 'Bucharest',
            'destination' => 'New York',
        ]);
    
        $flight->landed();
    
        DummyObserver::assertSavedAttributes([
            'departure' => 'Bucharest',
            'destination' => 'New York',
            'status' => 'landed'
        ]);
    }
}
```

## Support

Has this just helped you in a pinch when you tried to mock the Eloquent save method and nothing was working? Consider leaving me a note and buying me a coffee by clicking the button below.

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/B0B325116)

Have you found a problem? [Submit an issue](https://github.com/bogdanghervan/laravel-dummy-observer/issues)

## Contributing

Pull requests are welcome. All contributions should follow the PSR-2 coding standard and should be followed by passing tests.

## License

This package is available under the [MIT License](https://github.com/bogdanghervan/laravel-dummy-observer/blob/main/LICENSE).