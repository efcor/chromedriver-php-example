# chromedriver-php-example
Example abstraction over the php-webdriver package.

### Installation

```
git clone https://github.com/efcor/chromedriver-php-example.git
```

```
cd chromedriver-php-example
```

(Set up whatever local php server to serve out of the directory)

```
composer install
```

Download chromedriver binary from 
[https://chromedriver.chromium.org/downloads](https://chromedriver.chromium.org/downloads). As of this writing, the 
download comes as a zip containing just the binary file, so you need to unzip it. Name it `chromedriver` and place it in
the `bin` directory (of the project, not your computer). 

From the directory that chromedriver is in:

```
xattr -d com.apple.quarantine chromedriver
```

This will keep MacOS from disallowing the program to run.

### Basic Usage

Browse to whatever url you are serving from, ie `http://chromedriver-php-example.test/`

Click the "Run Script" button on the page. Wait 10 seconds or so for the script to run. 

Then, check your screenshots directory to confirm the script worked! You should see screenshots of websites.

Use the code in `scrape.php` as a starting point for your own needs!
