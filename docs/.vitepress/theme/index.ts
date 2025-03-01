import DefaultTheme from 'vitepress/theme';
import PageLayout from './PageLayout.vue';
import 'virtual:group-icons.css';
import './custom.css';

export default {
    extends: DefaultTheme,
    Layout: PageLayout,
};
