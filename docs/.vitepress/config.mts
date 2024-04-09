import {defineConfig} from 'vitepress';
// @ts-ignore
import {version} from '../../package.json';

const hostname: string = 'https://cache-warmup.dev';
const title: string = 'Cache Warmup';
const description: string = 'Library to warm up website caches of URLs located in XML sitemaps. Highly customizable and written in PHP.';
const image: string = `${hostname}/img/social-media.png`;
const imageAltText: string = 'Screenshot from a terminal window running the "cache-warmup" command on the XML sitemap from Google.';

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
    themeConfig: {
        logo: '/img/logo.svg',
        nav: [
            {text: 'Home', link: '/'},
            {text: '💰', link: '/sponsor'},
            {
                text: version,
                items: [
                    {
                        text: 'Release Notes',
                        link: 'https://github.com/eliashaeussler/cache-warmup/releases/latest',
                        rel: 'nofollow',
                    },
                    {
                        text: 'Download',
                        link: 'https://github.com/eliashaeussler/cache-warmup/releases/latest/download/cache-warmup.phar',
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
                    {text: 'About this project', link: '/about'},
                ],
            },
            {
                text: 'Configuration Reference',
                items: [
                    {text: 'Overview', link: '/config-reference/'},
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
                        text: 'Crawling',
                        collapsed: true,
                        items: [
                            {text: 'Crawler', link: '/config-reference/crawler'},
                            {text: 'Crawler options', link: '/config-reference/crawler-options'},
                            {text: 'Crawling strategy', link: '/config-reference/strategy'},
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
                    {text: 'Overview', link: '/api/'},
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
                            {text: 'Create a custom Crawler', link: '/api/crawler'},
                            {text: 'Configurable Crawler', link: '/api/configurable-crawler'},
                            {text: 'Logging Crawler', link: '/api/logging-crawler'},
                            {text: 'Stoppable Crawler', link: '/api/stoppable-crawler'},
                            {text: 'Verbose Crawler', link: '/api/verbose-crawler'},
                        ],
                    },
                ],
            },
            {
                text: 'Contributing',
                items: [
                    {text: 'Contribution guide', link: '/contribution-guide'},
                    {text: 'Sponsor this project', link: '/sponsor'},
                    {text: 'License', link: '/license'},
                ],
            },
        ],
        socialLinks: [
            {icon: 'github', link: 'https://github.com/eliashaeussler/cache-warmup'},
        ],
        editLink: {
            pattern: 'https://github.com/eliashaeussler/cache-warmup/edit/main/docs/:path',
        },
        footer: {
            message: 'Released under the GNU General Public License 3.0 (or later)',
            copyright: `Copyright © 2020-${new Date().getFullYear()} Elias Häußler`,
        },
        search: {
            provider: 'local',
        },
    },
});
