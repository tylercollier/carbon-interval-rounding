# CarbonInterval Rounding for Humans

## Update

From my comment [here](https://github.com/briannesbitt/Carbon/issues/559#issuecomment-557104313):

The ability to round is now available as part of release 2.27 and has multiple rounding options like ROUND, CEIL, FLOOR. See https://github.com/briannesbitt/Carbon/releases/tag/2.27.0; it pulls in PR #1930. Thanks @kylekatarnls!

## Original README follows

See https://github.com/briannesbitt/Carbon/issues/559

If you do `$now = Carbon\Carbon::now()->addDays(13); $now->diffForHumans(['parts' => 1])`, it says `1 week`. Wouldn't it make more sense to be 2 weeks? With this class, it will round the values so you'll get e.g. 2 weeks. See the test file for some more examples.

## How does it work?

I took the original CarbonInterval class's `forHumans` method and sprinkled in a small section that rounds the smallest "part" based on the next interval value. You can compare it with a diff tool to https://github.com/briannesbitt/Carbon/blob/738fbd8d80b2c5e158fda76c29c2de432fcc6f7e/src/Carbon/CarbonInterval.php#L1133-L1281.

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
