# Prime Shell
[![build](https://github.com/b2pweb/bdf-prime-shell/actions/workflows/php.yml/badge.svg)](https://github.com/b2pweb/bdf-prime-shell/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2pweb/bdf-prime-shell/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-prime-shell/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/b2pweb/bdf-prime-shell/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-prime-shell/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-prime-shell.svg)](https://packagist.org/packages/b2pweb/bdf-prime-shell)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-prime-shell.svg)](https://packagist.org/packages/b2pweb/bdf-prime-shell)
[![Type Coverage](https://shepherd.dev/github/b2pweb/bdf-prime-shell/coverage.svg)](https://shepherd.dev/github/b2pweb/bdf-prime-shell)

Interactive shell for execute queries using prime repositories.

## Usage

Install using composer

```
composer require b2pweb/bdf-prime-shell
```

Register the command into the console (symfony example) :

```yaml
services:
    Bdf\Prime\Shell\PrimeShellCommand:
        class: 'Bdf\Prime\Shell\PrimeShellCommand'
        arguments: ['@prime']
        tags: ['console.command']
```

Now you can execute the shell :

```
bin/console prime:shell -p src/Entity
```

The `-p` option allow preloading entity classes for autocomplete.
Now you can call repositories and queries methods like in real PHP !
