# chromedriver-php-example
Example abstraction over the php-webdriver package.

### Installation

```
git clone https://github.com/efcor/chromedriver-php-example.git
```

```
cd chromedriver-php-example
composer install
```

Download chromedriver binary from 
[https://chromedriver.chromium.org/downloads](https://chromedriver.chromium.org/downloads). As of this writing, the 
download comes as a zip containing just the binary file, so you need to unzip it. Name it `chromedriver` and place it in
the `bin` directory (of the project directory, not the /bin directory of your system).

If you are on MacOS, you must run the a command from the project directory, in order to prevent MacOS from disallowing
the program to run:

```
xattr -d com.apple.quarantine bin/chromedriver
```

### Basic Usage

Simply run the scrape.php script to see a demo.

```
php scrape.php
```

It may take 10 seconds or so for the script to run. It may take much longer if you are on a low-resource system like a
vm with only 1Gb RAM. Then, check the project directory's `screenshots` directory to confirm the script worked. You 
should see a few screenshots of websites.

Use the code in `scrape.php` as a starting point for your own needs!

### Installing and Using on a Linux Web Server

Update apt and your installed packages.

```
apt update
apt -y upgrade
sudo reboot
```

Install some prerequisite libraries for Chrome

```
sudo apt install -y libxss1 libappindicator1 libindicator7
```

Download and install Chrome

```
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome*.deb
```

There will be an error here. Run this to fix it

```
apt install -y -f
```

At this point, follow the instructions under the "Installation" section above.

### Managing Memory on a Linux Web Server

You will probably need to kill chrome after the script finishes to prevent running out of memory:

```
pkill -9 chrome
```
