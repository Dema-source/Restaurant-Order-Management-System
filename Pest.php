<?php

use Pest\Platform\Platform;

/*
|--------------------------------------------------------------------------
| Test Runner
|--------------------------------------------------------------------------
|
| The test runner is responsible for running the tests and displaying the
| results. You may configure the test runner to suit your needs.
|
*/

$platform = Platform::current();

/*
|--------------------------------------------------------------------------
| Test Suite
|--------------------------------------------------------------------------
|
| The test suite is the group of tests that will be run. You may add
| additional test suites to your configuration file.
|
*/

$testSuite = $platform->testSuite();

$testSuite->testsIn(__DIR__.'/tests');

/*
|--------------------------------------------------------------------------
| Plugins
|--------------------------------------------------------------------------
|
| Plugins allow you to extend Pest with additional functionality. You may
| add plugins to your configuration file.
|
*/

$testSuite->use(\Pest\Plugin\Laravel::class);

/*
|--------------------------------------------------------------------------
| Parallel Testing
|--------------------------------------------------------------------------
|
| Parallel testing allows you to run your tests in parallel, which can
| significantly speed up the test suite. You may enable parallel testing
| by setting the 'parallel' option to true.
|
*/

$testSuite->parallel(false);

/*
|--------------------------------------------------------------------------
| Stop On Failure
|--------------------------------------------------------------------------
|
| When enabled, the test runner will stop on the first failure. This can
| be useful when debugging failing tests.
|
*/

$testSuite->stopOnFailure(false);

/*
|--------------------------------------------------------------------------
| Coverage
|--------------------------------------------------------------------------
|
| Code coverage reports can help you identify which parts of your code
| are not being tested. You may configure code coverage options here.
|
*/

$testSuite->coverage(false);

/*
|--------------------------------------------------------------------------
| Colors
|--------------------------------------------------------------------------
|
| When enabled, the test runner will use colors in the output. This can
| make it easier to read the results.
|
*/

$testSuite->colors(true);
