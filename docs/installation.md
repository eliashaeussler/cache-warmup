# Installation

[![PHAR downloads](https://img.shields.io/github/downloads/eliashaeussler/cache-warmup/total?label=PHAR+downloads&logo=github)](https://github.com/eliashaeussler/cache-warmup/releases)
[![Docker pulls](https://img.shields.io/docker/pulls/eliashaeussler/cache-warmup?label=Docker+pulls&logo=docker)](https://hub.docker.com/r/eliashaeussler/cache-warmup)
[![Packagist downloads](https://img.shields.io/packagist/dt/eliashaeussler/cache-warmup?label=Packagist+downloads&logo=packagist)](https://packagist.org/packages/eliashaeussler/cache-warmup)

There are various installation methods for the *Cache Warmup* library.
Choose the one that suits you best. We recommend using the PHAR file
because it is the easiest to integrate into your project.

## Download

Choose one of the following installation methods to download the
*Cache Warmup* library:

::: code-group

```bash [PHAR]
curl -LO https://github.com/eliashaeussler/cache-warmup/releases/latest/download/cache-warmup.phar
chmod +x cache-warmup.phar
```

```bash [PHIVE]
phive install cache-warmup
```

```bash [Docker]
# Docker Hub
docker pull eliashaeussler/cache-warmup

# GitHub Container Registry
docker pull ghcr.io/eliashaeussler/cache-warmup
```

```bash [Composer]
composer require eliashaeussler/cache-warmup
```

:::

## First steps

Once downloaded, you can start by passing the URL to an XML sitemap:

::: code-group

```bash [PHAR]
./cache-warmup.phar "https://www.example.com/sitemap.xml"
```

```bash [PHIVE]
./tools/cache-warmup "https://www.example.com/sitemap.xml"
```

```bash [Docker]
# Docker Hub
docker run --rm -it eliashaeussler/cache-warmup \
    "https://www.example.com/sitemap.xml"

# GitHub Container Registry
docker run --rm -it ghcr.io/eliashaeussler/cache-warmup \
    "https://www.example.com/sitemap.xml"
```

```bash [Composer]
./vendor/bin/cache-warmup "https://www.example.com/sitemap.xml"
```

:::

For a more fine-grained configuration of the cache warmup process,
please have a look at [Configuration](configuration.md).
