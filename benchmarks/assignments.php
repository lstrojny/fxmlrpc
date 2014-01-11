<?php
const LIMIT = 1000000;

$bitmask = 0b0001;
const BITMASK = 0b0001;

$results = [];

class Clazz
{
    const BITMASK = 0b0001;

    public static function staticVariableAssignment(array &$results)
    {
        static $bitmask = 0b0001;

        $v = null;

        $start = microtime(true) * 1000;
        for ($a = 0; $a < LIMIT; ++$a) {
            $v = $bitmask;
        }
        $end = microtime(true) * 1000;
        $results["Static variable assignments (method): %fms"] = $end - $start;
    }

    public static function variableAssignment(array &$results)
    {
        $bitmask = 0b0001;

        $v = null;

        $start = microtime(true) * 1000;
        for ($a = 0; $a < LIMIT; ++$a) {
            $v = $bitmask;
        }
        $end = microtime(true) * 1000;
        $results["Variable assignments (method): %fms"] = $end - $start;
    }

    public static function constantAssignmentStatic(array &$results)
    {
        $v = null;

        $start = microtime(true) * 1000;
        for ($a = 0; $a < LIMIT; ++$a) {
            $v = static::BITMASK;
        }
        $end = microtime(true) * 1000;
        $results["Class constant assignments (method, static): %fms"] = $end - $start;
    }

    public static function constantAssignmentSelf(array &$results)
    {
        $v = null;

        $start = microtime(true) * 1000;
        for ($a = 0; $a < LIMIT; ++$a) {
            $v = static::BITMASK;
        }
        $end = microtime(true) * 1000;
        $results["Class constant assignments (method, self): %fms"] = $end - $start;
    }

    public static function constantAssignmentFq(array &$results)
    {
        $v = null;

        $start = microtime(true) * 1000;
        for ($a = 0; $a < LIMIT; ++$a) {
            $v = Clazz::BITMASK;
        }
        $end = microtime(true) * 1000;
        $results["Class constant assignments (method, fully-qualified): %fms"] = $end - $start;
    }
}

$a = 0;
$v = null;

$start = microtime(true) * 1000;
for ($a = 0; $a < LIMIT; ++$a) {
    $v = $bitmask;
}
$end = microtime(true) * 1000;
$results["Variable assignments: %fms"] = $end - $start;

$start = microtime(true) * 1000;
for ($a = 0; $a < LIMIT; ++$a) {
    $v = BITMASK;
}
$end = microtime(true) * 1000;
$results["Constant assignments: %fms"] = $end - $start;

$start = microtime(true) * 1000;
for ($a = 0; $a < LIMIT; ++$a) {
    $v = Clazz::BITMASK;
}
$end = microtime(true) * 1000;
$results["Class constant assignments: %fms"] = $end - $start;

$start = microtime(true) * 1000;
for ($a = 0; $a < LIMIT; ++$a) {
    $v = 1;
}
$end = microtime(true) * 1000;
$results["Value assignments (integer syntax): %fms"] = $end - $start;

$start = microtime(true) * 1000;
for ($a = 0; $a < LIMIT; ++$a) {
    $v = 0b0001;
}
$end = microtime(true) * 1000;
$results["Value assignments (binary syntax): %fms"] = $end - $start;

Clazz::staticVariableAssignment($results);
Clazz::variableAssignment($results);
Clazz::constantAssignmentStatic($results);
Clazz::constantAssignmentSelf($results);
Clazz::constantAssignmentFq($results);

asort($results);
foreach ($results as $description => $result) {
    printf($description . "\n", $result);
}
