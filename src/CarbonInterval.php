<?php

// The purpose of this subclass is to handle rounding.
// See this link for description of problem: https://github.com/briannesbitt/Carbon/issues/559
// This version works with the Carbon used by Laravel v5.8.32 (2.22.3). Not guaranteed to work with newer versions.
// Compare to the original at
// https://github.com/briannesbitt/Carbon/blob/738fbd8d80b2c5e158fda76c29c2de432fcc6f7e/src/Carbon/CarbonInterval.php#L1133-L1281

namespace App;

use Carbon\CarbonInterface;

class CarbonInterval extends \Carbon\CarbonInterval {

  /**
   * Get the current interval in a human readable format in the current locale.
   *
   * @example
   * ```
   * echo CarbonInterval::fromString('4d 3h 40m')->forHumans() . "\n";
   * echo CarbonInterval::fromString('4d 3h 40m')->forHumans(['parts' => 2]) . "\n";
   * echo CarbonInterval::fromString('4d 3h 40m')->forHumans(['parts' => 3, 'join' => true]) . "\n";
   * echo CarbonInterval::fromString('4d 3h 40m')->forHumans(['short' => true]) . "\n";
   * echo CarbonInterval::fromString('1d 24h')->forHumans(['join' => ' or ']) . "\n";
   * ```
   *
   * @param int|array $syntax  if array passed, parameters will be extracted from it, the array may contains:
   *                           - 'syntax' entry (see below)
   *                           - 'short' entry (see below)
   *                           - 'parts' entry (see below)
   *                           - 'options' entry (see below)
   *                           - 'aUnit' entry, prefer "an hour" over "1 hour" if true
   *                           - 'join' entry determines how to join multiple parts of the string
   *                           `  - if $join is a string, it's used as a joiner glue
   *                           `  - if $join is a callable/closure, it get the list of string and should return a string
   *                           `  - if $join is an array, the first item will be the default glue, and the second item
   *                           `    will be used instead of the glue for the last item
   *                           `  - if $join is true, it will be guessed from the locale ('list' translation file entry)
   *                           `  - if $join is missing, a space will be used as glue
   *                           if int passed, it add modifiers:
   *                           Possible values:
   *                           - CarbonInterface::DIFF_ABSOLUTE          no modifiers
   *                           - CarbonInterface::DIFF_RELATIVE_TO_NOW   add ago/from now modifier
   *                           - CarbonInterface::DIFF_RELATIVE_TO_OTHER add before/after modifier
   *                           Default value: CarbonInterface::DIFF_ABSOLUTE
   * @param bool      $short   displays short format of time units
   * @param int       $parts   maximum number of parts to display (default value: -1: no limits)
   * @param int       $options human diff options
   *
   * @return string
   */
  public function forHumans($syntax = null, $short = false, $parts = -1, $options = null)
  {
    [$syntax, $short, $parts, $options, $join, $aUnit] = $this->getForHumansParameters($syntax, $short, $parts, $options);

    $interval = [];
    $syntax = (int) ($syntax === null ? CarbonInterface::DIFF_ABSOLUTE : $syntax);
    $absolute = $syntax === CarbonInterface::DIFF_ABSOLUTE;
    $relativeToNow = $syntax === CarbonInterface::DIFF_RELATIVE_TO_NOW;
    $count = 1;
    $unit = $short ? 's' : 'second';

    /** @var \Symfony\Component\Translation\Translator $translator */
    $translator = $this->getLocalTranslator();

    $diffIntervalArray = [
      ['value' => $this->years,            'unit' => 'year',   'unitShort' => 'y'],
      ['value' => $this->months,           'unit' => 'month',  'unitShort' => 'm'],
      ['value' => $this->weeks,            'unit' => 'week',   'unitShort' => 'w'],
      ['value' => $this->daysExcludeWeeks, 'unit' => 'day',    'unitShort' => 'd'],
      ['value' => $this->hours,            'unit' => 'hour',   'unitShort' => 'h'],
      ['value' => $this->minutes,          'unit' => 'minute', 'unitShort' => 'min'],
      ['value' => $this->seconds,          'unit' => 'second', 'unitShort' => 's'],
    ];

    $transChoice = function ($short, $unitData) use ($translator, $aUnit) {
      $count = $unitData['value'];

      if ($short) {
        $result = $this->translate($unitData['unitShort'], [], $count, $translator);

        if ($result !== $unitData['unitShort']) {
          return $result;
        }
      } elseif ($aUnit) {
        $key = 'a_'.$unitData['unit'];
        $result = $this->translate($key, [], $count, $translator);

        if ($result !== $key) {
          return $result;
        }
      }

      return $this->translate($unitData['unit'], [], $count, $translator);
    };

    foreach ($diffIntervalArray as $index => $diffIntervalData) {
      if ($diffIntervalData['value'] > 0) {
        $unit = $short ? $diffIntervalData['unitShort'] : $diffIntervalData['unit'];
        $count = $diffIntervalData['value'];
        if(count($interval) === $parts - 1 && $index < count($diffIntervalArray) - 1) {
          $roundValue = 0;
          $nextIntervalData = $diffIntervalArray[$index + 1];
          if($nextIntervalData['unit'] === 'month') {
            $roundValue = $nextIntervalData['value'] / 12;
          } else if ($nextIntervalData['unit'] === 'week') {
            $roundValue = $nextIntervalData['value'] / (365 / 12 / 7);
          } else if ($nextIntervalData['unit'] === 'day') {
            $roundValue = $nextIntervalData['value'] / 7;
          } else if($nextIntervalData['unit'] === 'hour') {
            $roundValue = $nextIntervalData['value'] / 24;
          } else if($nextIntervalData['unit'] === 'minute') {
            $roundValue = $nextIntervalData['value'] / 60;
          } else if($nextIntervalData['unit'] === 'second') {
            $roundValue = $nextIntervalData['value'] / 60;
          }
          $diffIntervalData['value'] += round($roundValue);
          $interval[] = $transChoice($short, $diffIntervalData);
        }
        else{
          $interval[] = $transChoice($short, $diffIntervalData);
        }
      } elseif ($options & CarbonInterface::SEQUENTIAL_PARTS_ONLY && count($interval) > 0) {
        break;
      }

      // break the loop after we get the required number of parts in array
      if (count($interval) >= $parts) {
        break;
      }
    }

    if (count($interval) === 0) {
      if ($relativeToNow && $options & CarbonInterface::JUST_NOW) {
        $key = 'diff_now';
        $translation = $this->translate($key, [], null, $translator);
        if ($translation !== $key) {
          return $translation;
        }
      }
      $count = $options & CarbonInterface::NO_ZERO_DIFF ? 1 : 0;
      $unit = $short ? 's' : 'second';
      $interval[] = $this->translate($unit, [], $count, $translator);
    }

    // join the interval parts by a space
    $time = $join($interval);

    unset($diffIntervalArray, $interval);

    if ($absolute) {
      return $time;
    }

    $isFuture = $this->invert === 1;

    $transId = $relativeToNow ? ($isFuture ? 'from_now' : 'ago') : ($isFuture ? 'after' : 'before');

    if ($parts === 1) {
      if ($relativeToNow && $unit === 'day') {
        if ($count === 1 && $options & CarbonInterface::ONE_DAY_WORDS) {
          $key = $isFuture ? 'diff_tomorrow' : 'diff_yesterday';
          $translation = $this->translate($key, [], null, $translator);
          if ($translation !== $key) {
            return $translation;
          }
        }
        if ($count === 2 && $options & CarbonInterface::TWO_DAY_WORDS) {
          $key = $isFuture ? 'diff_after_tomorrow' : 'diff_before_yesterday';
          $translation = $this->translate($key, [], null, $translator);
          if ($translation !== $key) {
            return $translation;
          }
        }
      }
      // Some languages have special pluralization for past and future tense.
      $key = $unit.'_'.$transId;
      if ($key !== $this->translate($key, [], null, $translator)) {
        $time = $this->translate($key, [], $count, $translator);
      }
    }

    return $this->translate($transId, [':time' => $time], null, $translator);
  }
}
