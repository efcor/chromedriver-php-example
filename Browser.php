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
    protected $process;

    protected $window;

    protected $defaultDims = [
        'width' => 1920,
        'height' => 1080
    ];

    public function getWindow()
    {
        return $this->window;
    }

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

    public function start()
    {
        $this->startChrome();

        $this->window = $this->createWindow();
    }

    public function stop()
    {
        $this->stopChrome();
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

    /**
     * Pause for the given amount of milliseconds.
     *
     * @param  int  $milliseconds
     * @return void
     */
    public function pause($milliseconds)
    {
        usleep($milliseconds * 1000);
    }

    public function visit($url)
    {
        $this->window->navigate()->to($url);
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

    public function type($field, $text)
    {
        $selectors = ["input[name=\"$field\"]", "textarea[name=\"$field\"]", $field];

        $element = $this->findElement($selectors);
        $element->sendKeys($text);
    }

    /**
     * Select the given value of a drop-down field.
     *
     * @param  string  $field
     * @param  string  $value
     * @return void
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
    }

    /**
     * Select the given value of a radio button field.
     *
     * @param  string  $field
     * @param  string  $value
     * @return $this
     */
    public function radio($field, $value = null)
    {
        $selector = "input[type=radio][name='{$field}'][value='{$value}']";

        $this->findElement([$selector, $field])->click();
    }

    /**
     * Check the given checkbox.
     *
     * @param  string  $field
     * @param  string  $value
     * @return void
     */
    public function check($field, $value = null)
    {
        $selector = "input[type=checkbox][name='{$field}'][value='{$value}']";

        $element = $this->findElement([$selector, $field]);

        if (! $element->isSelected()) {
            $element->click();
        }
    }

    /**
     * Uncheck the given checkbox.
     *
     * @param  string  $field
     * @param  string  $value
     * @return $this
     */
    public function uncheck($field, $value = null)
    {
        $selector = "input[type=checkbox][name='{$field}'][value='{$value}']";

        $element = $this->findElement([$selector, $field]);

        if ($element->isSelected()) {
            $element->click();
        }
    }

    public function clear($field)
    {
        $selectors = ["input[name=\"$field\"]", "textarea[name=\"$field\"]", $field];

        $element = $this->findElement($selectors);
        $element->clear();
        // workaround for a bug with ->clear()
        $element->sendKeys(['A', WebDriverKeys::BACKSPACE]);
    }

    public function click($selector)
    {
        $element = $this->window->findElement(WebDriverBy::cssSelector($selector));
        $element->click();
    }

    public function waitForElement($selector, $seconds = 5)
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
    }

    protected function getDefaultScreenshotFilepath()
    {
        $timePortion = Carbon::now()->format('YmdHis');

        $screenshotPath = __DIR__ . '/screenshots/screenshot_' . $timePortion . '.png';

        // handle case where a screenshot filename already exists for the current timestamp
        while (file_exists($screenshotPath)) {
            $i = isset($i) ? ($i + 1) : 2;
            $screenshotPath = __DIR__ . '/screenshots/screenshot_' . $timePortion . '_' . $i . '.png';
        }

        return $screenshotPath;
    }

    public function screenshot($filename = null)
    {
        $filepath = is_null($filename)
            ? $this->getDefaultScreenshotFilepath()
            : (__DIR__ . '/screenshots/'.$filename.'.png');

        $this->window->takeScreenshot($filepath);
    }

    public function script($script)
    {
        $this->window->executeScript($script);
    }
}
