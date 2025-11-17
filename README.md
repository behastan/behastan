# Behastan - Modern Static analysis for Behat tests

[![Downloads total](https://img.shields.io/packagist/dt/behastan/behastan.svg?style=flat-square)](https://packagist.org/packages/behastan/behastan/stats)

Find unused and duplicated definitions easily &ndash; without running Behat tests.

<br>

## Install

```bash
composer require behastan/behastan --dev
```

## Usage

```bash
vendor/bin/behastan analyse tests
```

<br>

Do you want to skip some rule? You can:

```bash
vendor/bin/behastan analyse tests --skip=<rule-identifier>
```

<br>

Here are the available rules:

### 1. Find duplicated definitions contents

* identifier: `duplicated-contents`

Some definitions have similar masks, even identical contents. Better use a one definitions with exact mask, to make your tests more precise and easier to maintain:

<br>

### 2. Find duplicate masks

* identifier: `duplicated-masks`

Same as services, there should be no 2 same definition masks. Make them unique with different behavior, or merge them and use one definition instead.

<br>

### 3. Find unused Behat definitions with static analysis

* identifier: `unused-definitions`

Behat uses `@When()`, `@Then()` and `@Given()` annotations or attributes to define a class method that is  called in `*.feature` files. Sometimes test change and lines from `*.feature` files are deleted. But what about definitions?

This rule spots definitions that are no longer needed.

<br>

## Output example

```bash
Found 127 Context and 225 feature files
Extracting definitions masks...

Found 1367 masks:
 * 863 exact
 * 204 /regex/
 * 298 :named

Running analysis...
```

Add this command to CI, to get instant feedback of any changes.

That's it!

<br>

Happy coding!
