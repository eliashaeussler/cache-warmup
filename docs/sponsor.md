---
layout: home

hero:
  name: Sponsor this project
  tagline: Fund maintenance and development to keep the project alive. Thanks for your support!
  actions:
    - theme: brand
      text: GitHub Sponsors
      link: https://github.com/sponsors/eliashaeussler
    - theme: alt
      text: PayPal
      link: https://paypal.me/eliashaeussler
    - theme: alt
      text: Become a Contributor
      link: /contribution-guide

features:
  - icon: ❤️
    title: Support development
    details: Your sponsorship helps to keep further development of this project alive.
  - icon: 🚜
    title: Keep maintenance active
    details: Maintaining an open source project is hard work. Will you be part of it?
  - icon: 💡
    title: Invest in Open Source
    details: With Open Source, the world is a better place to live. Ready to find it out?
  - icon: ☕️
    title: Buy me a coffee
    details: Sometimes it's hard to keep up the good work if there's not enough coffee.
---

<script setup>
import { VPTeamMembers } from 'vitepress/theme';

const members = [
  {
    avatar: 'https://www.github.com/eliashaeussler.png',
    name: 'Elias Häußler',
    title: 'Backend Developer',
    links: [
      { icon: 'github', link: 'https://haeussler.dev/github' },
      { icon: 'bluesky', link: 'https://haeussler.dev/bluesky' },
      { icon: 'mastodon', link: 'https://haeussler.dev/mastodon' },
    ],
    sponsor: 'https://github.com/sponsors/eliashaeussler',
  },
];
</script>

## About the author

<VPTeamMembers size="small" :members="members" />
