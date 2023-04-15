# Contributing to scip-php

Thank you for considering contributing to `scip-php`! This document
outlines the guidelines and information you need to know before
contributing.

## Getting started

1. Fork and clone the repository, then create a new branch for your
   changes.
2. Install the project dependencies:
```bash
composer install
```
3. Ensure the tests and linters run successfully:
```bash
composer test
composer lint
```
3. See the [Development](README.md#development) section for further
   information.
4. Make your changes, commit and push them.
5. Open a pull request.

## Guidelines

### Issues and Bugs

If you find a bug or issue, please open an issue using the "Bug report"
template. Be sure to include as much detail as possible, including steps
to reproduce the issue.

### Feature Requests

If you have an idea for a larger feature, please open an issue using the
"Feature request" template. Be sure to include as much detail as possible,
including why you think the feature would be useful.

### Pull Requests

If you want to contribute to `scip-php`, please follow these guidelines:

- Make sure your code is well-tested and passes the tests and linters.
- Keep your pull requests small and focused on one specific feature or
  bug fix.
- Clearly explain what your code does and why it is necessary.
- Avoid making significant changes to the project without discussing
  them with the community first.

### PHP Style Guide

The coding style is based on [PSR-12](https://www.php-fig.org/psr/psr-12).
It is defined in the `phpcs.xml` file and enforced with the
[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) linter.

## Contact

If you have any questions or need help, please [open a
discussion](https://github.com/davidrjenni/scip-php/discussions/new?category=q-a).
