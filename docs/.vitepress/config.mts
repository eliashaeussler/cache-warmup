import {defineConfig} from 'vitepress';
import markdownItReplaceLink from 'markdown-it-replace-link';
import {groupIconMdPlugin, groupIconVitePlugin} from 'vitepress-plugin-group-icons';
import path from 'path';

import {RepoLinkReplacer} from './repo-link-replacer';
import {version} from '../../package.json';

// General information
const hostname: string = 'https://cache-warmup.dev';
const title: string = 'Cache Warmup';
const description: string = 'Library to warm up website caches of URLs located in XML sitemaps. Highly customizable and written in PHP.';
const image: string = `${hostname}/img/social-media.jpeg`;
const imageAltText: string = 'Screenshot from a terminal window running the "cache-warmup" command on the XML sitemap of cache-warmup.dev.';

// GitHub repository
const repoUrl: string = 'https://github.com/eliashaeussler/cache-warmup';
// @ts-ignore
const rootPath: string = path.resolve(__dirname, '..');
const replacer = new RepoLinkReplacer(repoUrl, rootPath, version);

export default defineConfig({
    title: title,
    description: description,
    lang: 'en',
    head: [
        ['link', {rel: 'icon', href: '/favicon.ico'}],

        // OpenGraph
        ['meta', {property: 'og:title', content: title}],
        ['meta', {property: 'og:description', content: description}],
        ['meta', {property: 'og:image', content: image}],
        ['meta', {property: 'og:image:alt', content: imageAltText}],
        ['meta', {property: 'og:type', content: 'website'}],

        // X/Twitter
        ['meta', {name: 'twitter:title', content: title}],
        ['meta', {name: 'twitter:description', content: description}],
        ['meta', {name: 'twitter:image', content: image}],
        ['meta', {name: 'twitter:image:alt', content: imageAltText}],
        ['meta', {name: 'twitter:card', content: 'summary_large_image'}],
        ['meta', {name: 'twitter:creator', content: '@elias_haeussler'}],
    ],
    sitemap: {
        hostname: hostname,
    },
    lastUpdated: true,
    themeConfig: {
        logo: '/img/logo.svg',
        outline: [2, 3],
        nav: [
            {text: 'Home', link: '/'},
            {text: '💰', link: '/sponsor'},
            {
                text: version,
                items: [
                    {
                        text: 'Release Notes',
                        link: `${repoUrl}/releases/${version}`,
                        rel: 'nofollow',
                    },
                    {
                        text: 'Download',
                        link: `${repoUrl}/releases/download/${version}/cache-warmup.phar`,
                        rel: 'nofollow',
                    },
                ],
            },
        ],
        sidebar: [
            {
                text: 'Getting Started',
                items: [
                    {text: 'Installation', link: '/installation'},
                    {text: 'Configuration', link: '/configuration'},
                    {text: 'Usage in CI/CD', link: '/ci'},
                    {text: 'About this project', link: '/about'},
                ],
            },
            {
                text: 'Configuration Reference',
                items: [
                    {text: 'Overview', link: '/config-reference/', docFooterText: 'Configuration Reference'},
                    {
                        text: 'Input',
                        collapsed: true,
                        items: [
                            {text: 'Sitemaps', link: '/config-reference/sitemaps'},
                            {text: 'URLs', link: '/config-reference/urls'},
                            {text: 'Exclude patterns', link: '/config-reference/exclude'},
                            {text: 'Limit', link: '/config-reference/limit'},
                            {text: 'Configuration file', link: '/config-reference/config'},
                        ],
                    },
                    {
                        text: 'Output',
                        collapsed: true,
                        items: [
                            {text: 'Format', link: '/config-reference/format'},
                            {text: 'Progress bar', link: '/config-reference/progress'},
                            {text: 'Endless mode', link: '/config-reference/repeat-after'},
                        ],
                    },
                    {
                        text: 'Client',
                        collapsed: true,
                        items: [
                            {text: 'Client options', link: '/config-reference/client-options'},
                        ],
                    },
                    {
                        text: 'Crawling',
                        collapsed: true,
                        items: [
                            {text: 'Crawler', link: '/config-reference/crawler'},
                            {text: 'Crawler options', link: '/config-reference/crawler-options'},
                            {text: 'Crawling strategy', link: '/config-reference/strategy'},
                        ],
                    },
                    {
                        text: 'Parsing',
                        collapsed: true,
                        items: [
                            {text: 'Parser', link: '/config-reference/parser'},
                            {text: 'Parser options', link: '/config-reference/parser-options'},
                        ],
                    },
                    {
                        text: 'Logging & Error Handling',
                        collapsed: true,
                        items: [
                            {text: 'Log file', link: '/config-reference/log-file'},
                            {text: 'Log level', link: '/config-reference/log-level'},
                            {text: 'Allow failures', link: '/config-reference/allow-failures'},
                            {text: 'Stop on failure', link: '/config-reference/stop-on-failure'},
                        ],
                    },
                ],
            },
            {
                text: 'API Reference',
                items: [
                    {text: 'Overview', link: '/api/', docFooterText: 'API Reference'},
                    {
                        text: 'CacheWarmer',
                        collapsed: true,
                        items: [
                            {text: 'Option Reference', link: '/api/options'},
                            {text: 'Method Reference', link: '/api/methods'},
                        ],
                    },
                    {
                        text: 'Crawler',
                        collapsed: true,
                        items: [
                            {text: 'Create a custom crawler', link: '/api/crawler'},
                            {text: 'Configurable Crawler', link: '/api/configurable-crawler'},
                            {text: 'Logging Crawler', link: '/api/logging-crawler'},
                            {text: 'Stoppable Crawler', link: '/api/stoppable-crawler'},
                            {text: 'Verbose Crawler', link: '/api/verbose-crawler'},
                        ],
                    },
                    {
                        text: 'Parser',
                        collapsed: true,
                        items: [
                            {text: 'Create a custom parser', link: '/api/parser'},
                            {text: 'Configurable Parser', link: '/api/configurable-parser'},
                        ],
                    },
                    {
                        text: 'HTTP',
                        collapsed: true,
                        items: [
                            {text: 'Client Factory', link: '/api/client-factory'},
                            {text: 'Request Factory', link: '/api/request-factory'},
                            {text: 'Response Handlers', link: '/api/response-handlers'},
                        ],
                    },
                    {
                        text: 'Dependency injection',
                        link: '/api/dependency-injection',
                    },
                    {
                        text: 'Events',
                        link: '/api/events',
                    },
                ],
            },
            {
                items: [
                    {text: 'Frequently Asked Questions', link: '/faq'},
                    {text: 'Migration', link: '/migration'},
                ],
            },
            {
                items: [
                    {text: 'Contribution guide', link: '/contribution-guide'},
                    {text: 'Sponsor this project', link: '/sponsor'},
                    {text: 'License', link: '/license'},
                ],
            },
        ],
        socialLinks: [
            {icon: 'github', link: repoUrl},
        ],
        editLink: {
            pattern: `${repoUrl}/edit/main/docs/:path`,
        },
        footer: {
            message: 'Released under the GNU General Public License 3.0 (or later)',
            copyright: `Copyright © 2020-${new Date().getFullYear()} Elias Häußler`,
        },
        search: {
            provider: 'local',
        },
    },
    markdown: {
        async config(md) {
            md.use(groupIconMdPlugin);
            md.use(markdownItReplaceLink, {
                replaceLink: (
                    link: string,
                    {relativePath}: { [key: string]: string },
                ) => replacer.replaceLink(link, relativePath),
            });
        },
    },
    vite: {
        plugins: [
            groupIconVitePlugin({
                customIcon: {
                    '.php': 'vscode-icons:file-type-php3',
                    'cli': 'vscode-icons:file-type-shell',
                    'composer': 'vscode-icons:file-type-composer',
                    'docker': 'vscode-icons:file-type-docker2',
                    'github actions': 'simple-icons:github',
                    'gitlab ci': 'vscode-icons:file-type-gitlab',
                    'phar': 'vscode-icons:file-type-php3',
                    'phive': 'vscode-icons:file-type-php3',
                    'php': 'vscode-icons:file-type-php3',
                    'json': 'vscode-icons:file-type-light-json',
                    'yaml': 'vscode-icons:file-type-light-yaml',
                },
            }),
        ],
    },
});
