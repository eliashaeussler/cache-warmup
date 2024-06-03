# Usage in CI/CD

Running cache warmup in Continuous Integration (CI) works best
by using the dedicated **Docker image**. See below example for
GitLab CI to get an overview about how to use the Docker image
in CI systems. Please note that the Docker image is only available
**from version 0.3.1** of the *Cache Warmup* library.

In addition, the **GitHub action** [`eliashaeussler/cache-warmup-action`](https://github.com/eliashaeussler/cache-warmup-action)
provides an easy integration in GitHub workflows. Note that the
action requires **at least version 0.7.0** of the library.

::: code-group

```yaml [GitHub Actions]
# .github/workflows/cache-warmup.yaml

name: Cache Warmup
on:
  push:
    tags:
      - '*'

jobs:
  cache-warmup:
    runs-on: ubuntu-latest
    steps:
      # Checkout is only required when using a config file
      # - uses: actions/checkout@v4

      - name: Set up environment
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          coverage: none

      - name: Run cache warmup
        uses: eliashaeussler/cache-warmup-action@v1
        with:
          sitemaps: "https://www.example.com/sitemap.xml"
```

```yaml [GitLab CI]
# .gitlab-ci.yml

cache-warmup:
  image:
    name: eliashaeussler/cache-warmup
    entrypoint: [""]
  script:
    - /cache-warmup.phar "https://www.example.com/sitemap.xml"
```

:::
