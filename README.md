# scip-php

[![CI](https://github.com/davidrjenni/scip-php/actions/workflows/ci.yml/badge.svg)](https://github.com/davidrjenni/scip-php/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/davidrjenni/scip-php/branch/main/graph/badge.svg?token=JYJNWGSDWL)](https://codecov.io/gh/davidrjenni/scip-php)
[![License: MIT](https://img.shields.io/github/license/davidrjenni/scip-php)](https://github.com/davidrjenni/scip-php/blob/main/LICENSE)

SCIP Code Intelligence Protocol (SCIP) indexer for PHP

---

## Development

### Bindings

The directory [`src/Bindings`](src/Bindings) contains auto-generated
bindings for SCIP.  To update the bindings, download the protobuf schema
for SCIP and regenerate the bindings:

```bash
wget -O src/Bindings/scip.proto https://raw.githubusercontent.com/sourcegraph/scip/main/scip.proto
composer gen-bindings
```

The protobuf compiler `protoc` must be present to generate the bindings.
