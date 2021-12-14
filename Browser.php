<?php

use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Process\Process;

class Browser
{
    /**
     * The chromedriver process.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * The window instance.
     *
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $window;

    /**
     * The default dimensions of the browser window.
     *
     * @var array
     */
    protected $defaultDims = [
        'width' => 1920,
        'height' => 1080
    ];

    /**
     * The default amount of seconds to wait before timing out.
     *
     * @var int
     */
    protected $waitSeconds = 5;

    /**
     * Get the window instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Get the filepath of the chromedriver binary.
     *
     * @return string
     */
    protected function getChromeDriverFilepath()
    {
        $driverPath = getenv('CHROME_DRIVER_PATH');

        if (!file_exists($driverPath)) {
            throw new RuntimeException("Invalid path to Chromedriver. Try checking the CHROME_DRIVER_PATH env variable.");
        }

        return $driverPath;
    }

    /**
     * Start the chromedriver process.
     *
     * @return void
     */
    public function startChrome()
    {
        $this->process = new Process([$this->getChromeDriverFilepath(), '--port=9515']);

        $this->process->start();
    }

    /**
     * Stop the chromedriver process.
     *
     * @return void
     */
    public function stopChrome()
    {
        $this->process->stop();
    }

    /**
     * Start the browser.
     *
     * @return $this
     */
    public function start($dims = null)
    {
        $this->startChrome();

        $this->window = $this->createWindow($dims);

        return $this;
    }

    /**
     * Close the browser.
     *
     * @return $this
     */
    public function stop()
    {
        $this->stopChrome();

        return $this;
    }

    /**
     * Create a window instance.
     *
     * Example $dims value: ['width' => 1400, 'height' => 800];
     *
     * @param  array  $dims
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public function createWindow($dims = null)
    {
        return retry(5, function () use ($dims) {
            $options = (new ChromeOptions)->addArguments([
                '--disable-gpu',
                '--headless',
                '--window-size=' . ($dims ? $dims['width'].','.$dims['height'] : $this->defaultDims['width'].','.$this->defaultDims['height'])
            ]);

            return RemoteWebDriver::create(
                'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                )
            );
        }, 50);
    }

    /**
     * Get the first element matching one of the given selectors.
     *
     * @param  array|string  $selectors
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     *
     * @throws \Exception
     */
    public function findElement($selectors)
    {
        foreach ((array) $selectors as $selector) {
            try {
                return $this->window->findElement(WebDriverBy::cssSelector($selector));
            } catch (Exception $e) {
                //
            }
        }

        throw $e;
    }

    /**
     * Pause for the given amount of milliseconds.
     *
     * @param  int  $milliseconds
     * @return $this
     */
    public function pause($milliseconds)
    {
        usleep($milliseconds * 1000);

        return $this;
    }

    /**
     * Browse to a url.
     *
     * @param  string  $url
     * @return $this
     */
    public function visit($url)
    {
        $this->window->navigate()->to($url);

        return $this;
    }

    /**
     * Type into an input.
     *
     * @param  string  $field  Can be the input's name attribute, or a css selector.
     * @param  string  $text  The string to type into the input
     * @return $this
     */
    public function type($field, $text)
    {
        $selectors = ["input[name=\"$field\"]", "textarea[name=\"$field\"]", $field];

        $element = $this->findElement($selectors);
        $element->sendKeys($text);

        return $this;
    }

    /**
     * Clear the text out of a text input or textarea.
     *
     * @param  string  $field  Can be the input's name attribute, or a css selector.
     * @return $this
     */
    public function clear($field)
    {
        $selectors = ["input[name=\"$field\"]", "textarea[name=\"$field\"]", $field];

        $element = $this->findElement($selectors);
        $element->clear();
        // workaround for a bug with ->clear()
        $element->sendKeys(['A', WebDriverKeys::BACKSPACE]);

        return $this;
    }

    /**
     * Select the given value of a drop-down field.
     *
     * @param  string  $field  Can be the selects's name attribute, or a css selector.
     * @param  string  $value  The value of the option element to select.
     * @return $this
     */
    public function select($field, $value)
    {
        $element = $this->findElement(["select[name='{$field}']", $field]);

        $options = $element->findElements(WebDriverBy::cssSelector('option:not([disabled])'));

        foreach ($options as $option) {
            if ((string) $option->getAttribute('value') === (string) $value) {
                $option->click();

                break;
            }
        }

        return $this;
    }

    /**
     * Select the given value of a radio button field.
     *
     * @param  string  $field  Can be the radio input's name attribute, or a css selector.
     * @param  string  $value  If using the input's name for $field, the value of the radio input to select.
     * @return $this
     */
    public function radio($field, $value = null)
    {
        $selector = "input[type=radio][name='{$field}'][value='{$value}']";

        $this->findElement([$selector, $field])->click();

        return $this;
    }

    /**
     * Check the given checkbox.
     *
     * @param  string  $field  Can be the checkbox input's name attribute, or a css selector.
     * @param  string  $value  If using the input's name for $field, the value of the checkbox input to check.
     * @return $this
     */
    public function check($field, $value = null)
    {
        $selector = "input[type=checkbox][name='{$field}'][value='{$value}']";

        $element = $this->findElement([$selector, $field]);

        if (! $element->isSelected()) {
            $element->click();
        }

        return $this;
    }

    /**
     * Uncheck the given checkbox.
     *
     * @param  string  $field  Can be the checkbox input's name attribute, or a css selector.
     * @param  string  $value  If using the input's name for $field, the value of the checkbox input to uncheck.
     * @return $this
     */
    public function uncheck($field, $value = null)
    {
        $selector = "input[type=checkbox][name='{$field}'][value='{$value}']";

        $element = $this->findElement([$selector, $field]);

        if ($element->isSelected()) {
            $element->click();
        }

        return $this;
    }

    /**
     * Click an element.
     *
     * @param  string  $selector  The css selector with which to specify the element.
     * @return $this
     */
    public function click($selector)
    {
        $element = $this->window->findElement(WebDriverBy::cssSelector($selector));
        $element->click();

        return $this;
    }

    /**
     * Wait for the given selector to be visible.
     *
     * @param  string  $selector  The css selector with which to specify the element.
     * @param  int|null  $seconds  How many seconds to wait before timing out.
     * @return $this
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitFor($selector, $seconds = 5)
    {
        $interval = 100;
        $callback = function () use ($selector) {
            return $this->window->findElement(WebDriverBy::cssSelector($selector))->isDisplayed();
        };
        $message = "Waited %s seconds for selector [{$selector}].";

        usleep($interval * 1000);

        $started = Carbon::now();

        while (true) {
            try {
                if ($callback()) {
                    break;
                }
            } catch (\Exception $e) {
                //
            }

            if ($started->lt(Carbon::now()->subSeconds($seconds))) {
                throw new TimeOutException(sprintf($message, $seconds));
            }

            usleep($interval * 1000);
        }

        return $this;
    }

    /**
     * Wait for the current page to reload.
     *
     * $callback argument can be used to pass the code you want execute before starting
     * to wait for the page to reload. This is necessary sometimes if the page reloads
     * too quickly for the javascript strategy this function relies on.
     *
     * @param  \Closure|null  $callback
     * @param  int|null  $seconds
     * @return $this
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitForReload($callback = null, $seconds = null)
    {
        $token = 'a' . random_int(100000, 999999);

        $this->window->executeScript("window['{$token}'] = {};");

        if ($callback) {
            $callback($this);
        }

        return $this->waitUsing($seconds, 100, function () use ($token) {
            return $this->window->executeScript("return typeof window['{$token}'] === 'undefined';");
        }, 'Waited %s seconds for page reload.');
    }

    /**
     * Wait for the given callback to be true.
     *
     * @param  int|null  $seconds
     * @param  int  $interval
     * @param  \Closure  $callback
     * @param  string|null  $message
     * @return $this
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitUsing($seconds, $interval, Closure $callback, $message = null)
    {
        $seconds = is_null($seconds) ? $this->waitSeconds : $seconds;

        $this->pause($interval);

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
                throw new TimeOutException($message
                    ? sprintf($message, $seconds)
                    : "Waited {$seconds} seconds for callback."
                );
            }

            $this->pause($interval);
        }

        return $this;
    }

    /**
     * Get the full path of the screenshot directory.
     *
     * @return string
     */
    protected function getScreenshotDir()
    {
        return __DIR__ . '/screenshots';
    }

    /**
     * Get the default filename for saving a screenshot.
     *
     * Generates a timestamp-based filename in the format: screenshot_YmdHis.png
     *
     * @return string
     */
    protected function getDefaultScreenshotFilename()
    {
        $timePortion = Carbon::now()->format('YmdHis');

        //$screenshotPath = __DIR__ . '/screenshots/screenshot_' . $timePortion . '.png';
        $filename = 'screenshot_' . $timePortion . '.png';

        // handle case where a screenshot filename already exists for the current timestamp
        while (file_exists($this->getScreenshotDir() . "/$filename")) {
            $i = isset($i) ? ($i + 1) : 2;
            $filename = 'screenshot_' . $timePortion . '_' . $i . '.png';
        }

        return $filename;
    }

    /**
     * Take a screenshot of the current browser window.
     *
     * Outputs into the screenshots folder for security reasons. If the screenshot
     * needs to be somewhere else, copy it to the necessary location.
     *
     * @param  string  $filename  Name of the outputted png file.
     * @return $this
     */
    public function screenshot($filename = null)
    {
        $filename = is_null($filename)
            ? $this->getDefaultScreenshotFilename()
            : ( (file_exists(realpath($filename)) ? realpath($filename) : $filename) . '.png' );

        $this->window->takeScreenshot($this->getScreenshotDir() . "/$filename");

        return $this;
    }

    /**
     * Execute a script in the current browser window.
     *
     * @param  string  $script  The script to inject.
     * @return mixed The return value of the script.
     */
    public function script($script)
    {
        return $this->window->executeScript($script);
    }

    /**
     * Inject jQuery into the current page if it's not already present.
     *
     * @return $this
     */
    public function ensureJQueryIsAvailable()
    {
        if ($this->window->executeScript('return window.jQuery == null')) {
            $jqueryFilepath = __DIR__.'/bin/jquery.js';

            if (!file_exists($jqueryFilepath)) {
                throw new \Exception('jquery.js not found');
            }

            $this->script(file_get_contents($jqueryFilepath));
        }

        return $this;
    }
}
