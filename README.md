# Asset Management
*PHP - Zend Framework 2 - Module*<br>
<i>Version: <b>Alpha</b></i>

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
The module is only an integration of [Assatic][1] and the the different filters
it uses to preform it's tasks.

## Installation
1. Copy the 'AssetManagement' folder in the 'src' directory to the vendors
folder of your project
2. Get [Assatic][1] and tell your autoloader about the namespace and where it
can be found. <i><b>Tip:</b> Have a look at the 'Module' class where the
Reference has been made but out commented</i>
3. Get external filters that you wish to use.<br>
*List of links..*

## Usage
**Description will follow when project reaches RC.**

[1]: https://github.com/kriswallsmith/assetic
