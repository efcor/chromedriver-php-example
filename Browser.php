<?php

use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Process\Process;

class Browser
{
    protected $process;

    protected $defaultDims = [
        'width' => 1920,
        'height' => 1080
    ];

    protected function getChromeDriverFile()
    {
        $driverPath = getenv('CHROME_DRIVER_PATH');

        if (!file_exists($driverPath)) {
            throw new RuntimeException("Invalid path to Chromedriver. Try checking the CHROME_DRIVER_PATH env variable.");
        }

        return $driverPath;
    }

    public function startChrome()
    {
        $this->process = new Process([$this->getChromeDriverFile(), '--port=9515']);

        $this->process->start();
    }

    public function stopChrome()
    {
        $this->process->stop();
    }

    public function createWindow($dims = null)
    {
        return retry(5, function () use ($dims) {
            $options = (new ChromeOptions)->addArguments([
                '--disable-gpu',
                '--headless',
                '--window-size=' . ($dims ? $dims['width'].','.$this->dims['height'] : $this->defaultDims['width'].','.$this->defaultDims['height'])
            ]);

            return RemoteWebDriver::create(
                'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                )
            );
        }, 50);
    }

    public function visit($window, $url)
    {
        $window->navigate()->to($url);
    }

    public function type($window, $field, $text)
    {
        $selector = "input[name=\"$field\"]";
        $element = $window->findElement(WebDriverBy::cssSelector($selector));
        $element->sendKeys($text);
    }

    public function click($window, $selector)
    {
        $element = $window->findElement(WebDriverBy::cssSelector($selector));
        $element->click();
    }

    public function waitForElement($window, $selector, $seconds = 5)
    {
        $interval = 100;
        $callback = function () use ($selector, $window) {
            return $window->findElement(WebDriverBy::cssSelector($selector))->isDisplayed();
        };
        $message = "Waited %s seconds for selector [{$selector}].";

        usleep($interval * 1000);

        $started = Carbon::now();

        while (true) {
            try {
                if ($callback()) {
                    break;
                }
            } catch (Exception $e) {
                //
            }

            if ($started->lt(Carbon::now()->subSeconds($seconds))) {
                throw new TimeOutException(sprintf($message, $seconds));
            }

            usleep($interval * 1000);
        }
    }

    public function takeScreenshot($window)
    {
        $screenshotPath = __DIR__ . '/screenshots/screenshot_' . Carbon::now()->format('YmdHis') . '.png';

        $window->takeScreenshot($screenshotPath);

        return $screenshotPath;
    }
}
