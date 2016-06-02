
# Gmail to Pika - Attach your email to the Pika CMS in a couple of clicks

<img align="right" height="164" src="https://s3.amazonaws.com/download.mcplusa.com/public+assets/gmail-to-pika/logo128.png">


---

[Extension for Google Chrome](https://chrome.google.com/webstore/detail/gmail-to-pika/mnapnnkgdnkpobhafdgclljfihpobnnf?hl=en) that integrates your Gmail inbox with [Pika](http://www.pikasoftware.com/).

This extension adds a button to your Gmail page that allows to easily attach your received and/or send emails to Pika in a couple of clicks.

**Check out our full documentation on [Our Github Page](http://mcplusa.github.io/TIG-Google-Apps-Integration)**

**Do you like this project? Check out what other projects we have at [MC+A](http://mcplusa.com)**

## Usage

Using the Gmail To Pika extension is very easy. Just install it as any other Google Chrome extension, set your Pika system URL, and use it.

### Step 1. Install the Extension

[Install the extension](https://chrome.google.com/webstore/detail/gmail-to-pika/mnapnnkgdnkpobhafdgclljfihpobnnf?hl=en) from the official Chrome Web Store.

### Step 2. Set up your Pika system

Go to [chrome://extensions](chrome://extensions) and search for *Gmail to Pika*. Click on Options and enter your Pika CMS URL, credentials and configuration for your Email Content.
<center>
<img src="https://s3.amazonaws.com/download.mcplusa.com/public+assets/gmail-to-pika/gmail-to-pika-send.png" width="291" height="385" alt="Extension Options">
</center>

### Step 3. Attach your email to Pika

Now when you go to your [Gmail](https://mail.google.com), you'll see a Red Button on your email. Click it and you'll be able to search for Pika objects. Select the one that you want to attach your email to, and follow the instructions.

<center>
<img src="https://s3.amazonaws.com/download.mcplusa.com/public+assets/gmail-to-pika/gmail-to-pika-email.png" width="531" height="300" alt="Extension Options"><br/>
<img src="https://s3.amazonaws.com/download.mcplusa.com/public+assets/gmail-to-pika/gmail-to-pika-attach.png" width="478" height="300" alt="Extension Options"><br/>
</center>

## Build from Source

To build this project you'll need to install [Bower](http://bower.io/) and [Grunt](http://gruntjs.com/):
```
$ npm install -g bower
$ npm install -g grunt-cli
```

Once you finish setting up your environment, install the required dependencies

```
$ npm install
$ bower install
```

And build the project
```
$ grunt build
```

## Running from Source

To run the extension on Google Chrome:

```
Go to chrome://extensions
Click on "Load unpacked extension..."
Select the directory of the project
```

## Testing

Run the Grunt Test task

```
$ grunt test
```
