# Behastan - Modern Static analysis for Behat tests

[![Downloads total](https://img.shields.io/packagist/dt/behastan/behastan.svg?style=flat-square)](https://packagist.org/packages/behastan/behastan/stats)

<br>

## Install

```bash
composer require behastan/behastan --dev
```

## Features

## 1. Find duplicated definitions

Some definitions have very similar masks, but even identical contents. Better use a one definitions with exact mask, to make your tests more precise and easier to maintain:

```bash
vendor/bin/behastan duplicated-definitions tests
```


<br>

## 2. Find unused Behat definitions with static analysis

Behat uses `@When()`, `@Then()` and `@Given()` annotations and their PHP 8 attribute alternatives to define method to be called in `*.feature` files. Sometimes test change and lines from `*.feature` files are deleted. But what about definitions?

This command helps you to spot definitions that are no longer needed. Just provide test directory (1 or more) and let it statically compare defined and used masks:

```bash
vendor/bin/behastan unused-definitions tests
```

↓

```bash
Checking static, named and regex masks from 100 *Feature files
==============================================================

Found 1036 masks:

 * 747 exact
 * 106 /regex/
 * 181 :named

 1036/1036 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

the product price is :value
tests/Behat/ProductContext.php

/^I submit order form and see payment page$/
tests/Behat/OrderContext.php


 [ERROR] Found 2 unused definitions
```

You can also add this command to CI, to get instant feedback about unused definitions.


That's it!

<br>

Happy coding!
