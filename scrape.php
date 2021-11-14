<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Browser.php';

$chromedriverFilepath = __DIR__.'/bin/chromedriver';
if (!file_exists($chromedriverFilepath)) {
    die ('chromedriver binary not found.');
}
putenv('CHROME_DRIVER_PATH='.realpath($chromedriverFilepath));

$browser = new Browser();

$browser->start()
    ->visit('https://google.com')
    ->visit('https://slashdot.org')
    ->screenshot()
    ->type('fhfilter', 'starcraft brood war')
    ->click('.btn.icon-search')
    ->waitFor('#fh-pag-div')
    ->screenshot()
    ->visit('https://google.com')
    ->screenshot()
    ->stop();

echo "Done.\n\n";
