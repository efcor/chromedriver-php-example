<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Browser.php';

// Get chromedriver from https://chromedriver.chromium.org/downloads
$chromedriverFilepath = __DIR__.'/bin/chromedriver';
if (!file_exists($chromedriverFilepath)) {
    die ('chromedriver binary not found.');
}
putenv('CHROME_DRIVER_PATH='.realpath($chromedriverFilepath));

$browser = new Browser();

$browser->startChrome();
        
$window = $browser->createWindow();

$browser->visit($window, 'https://slashdot.org');

$screenshotPath = $browser->takeScreenshot($window);

echo "Screenshot captured to $screenshotPath.";

$browser->type($window, 'fhfilter', 'starcraft brood war');

$browser->click($window, '.btn.icon-search');

$browser->waitForElement($window, '#fh-pag-div');

$screenshotPath2 = $browser->takeScreenshot($window);

echo "<br><br>Second screenshot captured to $screenshotPath2.";

$browser->visit($window, 'https://clemson.edu');

$screenshotPath3 = $browser->takeScreenshot($window);

echo "<br><br>Third screenshot captured to $screenshotPath3.";

$browser->stopChrome();
