# Laravel Dummy Observer

A purpose-built model observer that can be registered with an Eloquent model to intercept all attempted saves and perform assertions on the data. The data being saved never reaches the database.

![Tests](https://github.com/bogdanghervan/laravel-dummy-observer/workflows/Tests/badge.svg)

## Installation

### Requirements

* PHP ≥ 7.3
* PHPUnit ≥ 9.0
* Laravel Eloquent ≥ 5.3

### Installation

Install it via Composer:
```
composer require --dev bogdanghervan/laravel-dummy-observer
```

## Usage

Let's assume we'd like to test a method named `landed` on a model called `Flight`. This method would update the flight's status by invoking `save` internally. 

```PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'departure',
        'destination',
        'status'
    ];

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

    public function testLanded(): void
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

## Available assertions

### assertSavedAttributes($attributes)

Verify that expected attributes are saved.
```PHP
Flight::observe(DummyObserver::class);

$flight = Flight::create([
    'passenger' => 'John Smith'
]);

DummyObserver::assertSavedAttributes([
    'passenger' => 'John Smith'
]);
```

It's possible to verify that only a subset of the attributes was saved.
```PHP
Flight::observe(DummyObserver::class);

$flight = Flight::create([
    'passenger' => 'John Smith',
    'departure' => 'Bucharest',
    'destination' => 'New York',
    'status' => 'boarded'
]);

DummyObserver::assertSavedAttributes([
    'passenger' => 'John Smith',
    'status' => 'boarded'
]);
```

We can make multiple assertions if consecutive saves are being made in the code being tested. Just make sure to specify them in the same order.
```PHP
Flight::observe(DummyObserver::class);

$flight = Flight::create([
    'departure' => 'Bucharest',
    'destination' => 'New York'
]);

$flight->update([
    'status' => 'boarded',
    'gate' => 'A1'
]);

DummyObserver::assertSavedAttributes([
    'destination' => 'New York'
]);
DummyObserver::assertSavedAttributes([
    'status' => 'boarded',
    'gate' => 'A1'
]);
```

### assertSavedTimes($times = 1)

Make an assertion on the number of times a model has been saved.
```PHP
Flight::observe(DummyObserver::class);

$flight = Flight::create([
    'departure' => 'Bucharest',
    'destination' => 'New York'
]);

$flight->update([
    'status' => 'boarded',
    'gate' => 'A1'
]);

DummyObserver::assertSavedTimes(2);
```

### assertNothingSaved()

Make an assertion the model hasn't been saved.
```PHP
Flight::observe(DummyObserver::class);

$flight = new Flight();

DummyObserver::assertNothingSaved();
```

### clear()

Make sure to clear any captured data after every test. A good place to do this from is in the `tearDown` method:
```PHP
protected function tearDown(): void
{
    DummyObserver::clear();
}
```

## Limitations

When working with multiple models, it is not possible to assert a save against the model where the save originated. See issue [#1](https://github.com/bogdanghervan/laravel-dummy-observer/issues/1) for more details.

## Support

Has this just helped you in a pinch when you tried to mock the Eloquent save method and nothing was working? Consider leaving me a note and buying me a coffee by clicking the button below.

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/B0B325116)

Have you found a problem? [Submit an issue](https://github.com/bogdanghervan/laravel-dummy-observer/issues)

I myself have been inspired by the work done by [@timacdonald](https://github.com/timacdonald) on [timacdonald/log-fake](https://github.com/timacdonald/log-fake) whom I'd like to thank!

## Contributing

Pull requests are welcome. All contributions should follow the PSR-2 coding standard and should be accompanied by passing tests.

## License

This package is available under the [MIT License](https://github.com/bogdanghervan/laravel-dummy-observer/blob/main/LICENSE).
