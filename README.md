[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://img.shields.io/badge/License-GPL%20v2-blue.svg)  
wp-promote-mdn is a WordPress plugin to help promote [Mozilla Developer Network](https://developer.mozilla.org).

Features
========

* Widget for sidebars with graphic/banner link
* Automatic linking of key terms and phrases ([avalaible on this repo
  itself](https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt
)) in posts, pages and comments
* Ability to notify Mozilla Press and DevRel teams when publishing posts

Development
===========

[<img src="https://travis-ci.org/mdn/wp-promote-mdn.png?branch=master"/>](http://travis-ci.org/#!/mdn/wp-promote-mdn)

How It Works
------------

The plugin automatically links keywords and phrases in WordPress posts and
pages to pages on MDN. The keywords/phrases are loaded once per day from [a
file on GitHub](https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt).

Getting Started
---------------

1. Clone the repo into your `wp-content/plugins` directory
2. Make changes

Running Tests
-------------

Yes, I actually wrote tests for a WordPress Plugin. Don't judge me. To run
them, simply run:

    phpunit
