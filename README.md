# Asset Management
*PHP - Zend Framework 2 - Module*

Version: **Beta**

## What's this?
This is an asset management module for zf2 that allows other modules to specify
a public folder where it's assets are stored. You can then refer to thees
assets without moving them out of there directory to the servers public
specific one.

A typical asset could be a JavaScript, CSS or Image file.

### What more does it do?
You can also specify different filters to be able to parse scripts such as
LESS or CoffeeScript. The assigned filters can also minify the output or
include references.

### Anything else?
Yes, you can also combine multiple files into one single output.

## How it works
Behind the scenes the module uses the [Assetic][1] library.
**More information will follow in RC..**

## Installation
1. Copy the 'AssetManagement' folder in the 'src' directory to the vendors
folder of your project
2. Integrate the [Assetic][1] library in your application
3. Get external filters that you wish to use.<br>
**List of links will follow in RC..**
4. Activate the module in the config file

## Usage
**Description will follow in RC..**

[1]: https://github.com/kriswallsmith/assetic
