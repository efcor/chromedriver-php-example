# chromedriver-php-example
Example abstraction over the php-webdriver package.

### Installation

Prerequisites:
- a non-root user with sudo capabilities (see [setup-new-sudo-user.sh](https://gist.github.com/efcor/89012c49206db5bbe9c110ff7a3c2c88))
- composer package manager (see [install-composer.sh](https://gist.github.com/efcor/3e0f70b91987039ae0464bcd57fad35c))

Clone the project and install the dependencies.

```
git clone https://github.com/efcor/chromedriver-php-example.git

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

It should take less than 10 seconds for the script to run (longer if on a low-resource system). Then, check the 
`screenshots` directory to confirm the script worked. You should see a few screenshots of websites.

Use the code in `scrape.php` as a starting point for your own needs! Open Browser.php to view all the available methods.

### Installing and Using on a Linux Web Server

You must have a non-root user with sudo capabilities. See the "Installation" section above for a link to instructions.

Update apt and your installed packages.

```
sudo apt update

# if you are on a brand new vm, go ahead and update all the software
sudo apt -y upgrade
```

Install some prerequisite libraries for Chrome, and unzip (for composer later)

```
sudo apt install -y libxss1 libappindicator1 libindicator7 unzip

# if your php version doesn't have curl and zip extensions, 
# install those (replace "8.0" with your version)
sudo apt install -y php8.0-curl php8.0-zip
```

Download and install Chrome

```
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome*.deb
```

There will be an error here. Run this to fix it

```
sudo apt install -y -f
```

At this point, follow the instructions under the "Installation" section above.

### Managing Memory on a Linux Web Server

You will probably need to kill chrome after the script finishes to prevent running out of memory:

```
pkill -9 chrome
```

#### How to Add Swap

Here is how to add swap to an Ubuntu 20.04 vm that has no swap:

```
sudo fallocate -l 2G /swapfile

sudo chmod 600 /swapfile

sudo mkswap /swapfile

sudo swapon /swapfile
```

Verify the swap area has been enabled

```
sudo swapon -s
```

Output:

```
Filename    Type     Size       Used     Priority
/swapfile   file     2097148    44088    -1
```

Add the following line to the `/etc/fstab` file to make the swap file permanent.

```
/swapfile   none    swap    sw    0   0
```

### Todo

- $browser->waitForReload()
- Convenience method to insert jquery to the page
- Add docblocks throughout Browser.php
- Make Browser class methods fluent
- screenshot allow specifying a filename outside screenshots folder if starts with "/" otherwise put in that folder
