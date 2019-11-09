<?php

namespace Tests;

use App\CarbonInterval;
use PHPUnit\Framework\TestCase;

class CarbonIntervalTest extends TestCase {
  public function testYears(){
    $interval = CarbonInterval::years(2)->months(11);
    $this->assertEquals('3 years', $interval->forHumans(['parts' => 1]));
  }

  public function testRoundUp(){
    $interval = CarbonInterval::days(2)->hours(23);
    $this->assertEquals('3 days', $interval->forHumans(['parts' => 1]));
  }

  public function testRoundDown(){
    $interval = CarbonInterval::days(2)->hours(11);
    $this->assertEquals('2 days', $interval->forHumans(['parts' => 1]));
  }

  public function testRoundDownWhenNextIntervalIsNonSequential(){
    $interval = CarbonInterval::days(2)->minutes(59);
    $this->assertEquals('2 days', $interval->forHumans(['parts' => 1]));
  }

  public function testMultipleParts(){
    $interval = CarbonInterval::days(2)->minutes(45)->seconds(59);
    $this->assertEquals('2 days 46 minutes', $interval->forHumans(['parts' => 2]));
  }

  public function testWeeks(){
    $interval = CarbonInterval::days(13);
    $this->assertEquals('2 weeks', $interval->forHumans(['parts' => 1]));
  }

  public function testWeeksWithMultipleParts(){
    $interval = CarbonInterval::days(13);
    $this->assertEquals('1 week 6 days', $interval->forHumans(['parts' => 2]));
  }

  public function testOverflowNonSequentialRoundUp(){
    $interval = CarbonInterval::years(2)->months(35);
    $this->assertEquals('5 years', $interval->forHumans(['parts' => 1]));
  }

  public function testOverflowNonSequentialRoundDown(){
    $interval = CarbonInterval::years(2)->months(37);
    $this->assertEquals('5 years', $interval->forHumans(['parts' => 1]));
  }

  public function testCarryOverDoesntMatter(){
    $interval = CarbonInterval::days(2)->hours(11)->minutes(59);
    $this->assertEquals('2 days', $interval->forHumans(['parts' => 1]));
  }
}
