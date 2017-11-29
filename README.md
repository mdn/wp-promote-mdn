[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://img.shields.io/badge/License-GPL%20v2-blue.svg)  
wp-promote-mdn is a WordPress plugin to help promote [Mozilla Developer Network](https://developer.mozilla.org).

Features
========

* Widget for sidebars with banner link
* Automatic linking of key terms and phrases ([avalaible on this repo itself](https://raw.githubusercontent.com/mdn/wp-promote-mdn/def-list/terms.txt
)) in posts, pages and comments
* Shortcode `promote_mdn_newsletter` for the newsletter box

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
2. run `composer update`
3. Make changes

Running Tests
-------------

    codeception run wpunit
 