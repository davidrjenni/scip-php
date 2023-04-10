# scip-php

[![CI](https://github.com/davidrjenni/scip-php/actions/workflows/ci.yml/badge.svg)](https://github.com/davidrjenni/scip-php/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/davidrjenni/scip-php/branch/main/graph/badge.svg?token=JYJNWGSDWL)](https://codecov.io/gh/davidrjenni/scip-php)
[![License: MIT](https://img.shields.io/github/license/davidrjenni/scip-php)](https://github.com/davidrjenni/scip-php/blob/main/LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/davidrjenni/scip-php)](https://packagist.org/packages/davidrjenni/scip-php)
[![PHP Version](https://img.shields.io/packagist/php-v/davidrjenni/scip-php)](https://packagist.org/packages/davidrjenni/scip-php)
[![Docker Image Version](https://img.shields.io/docker/v/davidrjenni/scip-php?label=docker)](https://hub.docker.com/r/davidrjenni/scip-php)
[![Docker Image Size](https://img.shields.io/docker/image-size/davidrjenni/scip-php)](https://hub.docker.com/r/davidrjenni/scip-php)

SCIP Code Intelligence Protocol (SCIP) indexer for PHP

---

This repository is indexed using itself and available on
[Sourcegraph](https://sourcegraph.com/github.com/davidrjenni/scip-php).

## Development

- Run `composer lint` to run all linters.
- Run `composer test` to run the unit tests.
- Run `composer cover` to generate a coverage report.

### Bindings

The directory [`src/Bindings`](src/Bindings) contains auto-generated
bindings for SCIP.  To update the bindings, download the protobuf schema
for SCIP and regenerate the bindings:

```bash
wget -O src/Bindings/scip.proto https://raw.githubusercontent.com/sourcegraph/scip/main/scip.proto
composer gen-bindings
```

The protobuf compiler `protoc` must be present to generate the bindings.

See [github.com/sourcegraph/scip](https://github.com/sourcegraph/scip)
for further information.
