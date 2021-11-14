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

$browser->start();

$browser->visit('https://slashdot.org');

$browser->screenshot();

$browser->type('fhfilter', 'starcraft brood war');

$browser->click('.btn.icon-search');

$browser->waitForElement('#fh-pag-div');

$browser->screenshot();

$browser->visit('https://google.com');

$browser->screenshot();

$browser->stop();

echo "Done.\n\n";
