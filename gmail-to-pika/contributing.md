# Contributing

### TODO ###
1. Need final links

We are more than happy to accept external contributions/improvements to this project in the form of feedback, bug reports and pull requests

## Issue submission

In order for us to help you please check that you've completed the following steps:

* Used the search feature to ensure that the bug hasn't been reported before
* Included as much information about the bug as possible, including any output you've received, what OS and version you're on, etc.
 
  
[Submit your issue](need-final-link)


## Quick Start

- Clone the repo of [tig-google-apps](need-final-link)
- Install [node.js](http://nodejs.org)
- Scaffold Environment: 
```
npm install -g grunt-cli
npm install -g bower
npm install
```
- Download dependencies:
```
bower install
```
- Start hacking :)


## Style Guide

This project uses single-quotes, two space indentation, multiple var statements and whitespace around arguments. Use a single space after keywords like `function`. Ex:

```
function () { ... }
function foo() { ... }
```

Please ensure any pull requests follow this closely. If you notice existing code which doesn't follow these practices, feel free to shout and we will address this.


## Pull Request Guidelines

* Please check to make sure that there aren't existing pull requests attempting to address the issue mentioned. We also recommend checking for issues related to the issue on the tracker.
* Non-trivial changes should be discussed in an issue first
* Develop in a topic branch, not master
* Lint the code by running `grunt`
* Add relevant tests to cover the change
* Make sure test-suite passes: `npm test`
* Squash your commits
* Write a convincing description of your PR and why we should accept it