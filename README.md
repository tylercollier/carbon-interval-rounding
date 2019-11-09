# CarbonInterval Rounding for Humans

See https://github.com/briannesbitt/Carbon/issues/559

If you do `$now = Carbon\Carbon::now()->addDays(13); $now->diffForHumans(['parts' => 1])`, it says `1 week`. Wouldn't it make more sense to be 2 weeks? With this class, it will round the values so you'll get e.g. 2 weeks. See the test file for some more examples. 

## Use in your project

Place the `CarbonInterval.php` file in your project. Change the namespace if you choose.

First, get a "normal" interval from Carbon. Then use it to build a rounding CarbonInterval.

```php
$future = Carbon\Carbon::now()->addDays(13);
$interval = $future->diff(Carbon\Carbon::now());
$roundingInterval = App\CarbonInterval::instance($interval);
echo $roundingInterval->forHumans(['parts' => 1]); // 2 weeks
``` 

## Run tests

```bash
$ composer install
$ composer dump-autoload
```

Then run the tests with

```bash
$ vendor/bin/phpunit tests/
```

or alternatively watch and automatically rerun them on changes with

```bash
$ vendor/bin/phpunit-watcher watch tests/
```

## TODO

* Make rounding optional?
* Do you have improvement suggestions?

## License

MIT License
