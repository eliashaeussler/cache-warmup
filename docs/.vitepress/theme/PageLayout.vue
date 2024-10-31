<script setup>
import DefaultTheme from 'vitepress/theme'

const { Layout } = DefaultTheme
</script>

<template>
    <Layout>
        <template #layout-bottom>
            <!-- 100% privacy-first analytics -->
            <component is="script" async defer src="https://scripts.simpleanalyticscdn.com/latest.js"></component>
            <noscript><img src="https://queue.simpleanalyticscdn.com/noscript.gif" alt="" referrerpolicy="no-referrer-when-downgrade" /></noscript>
        </template>
    </Layout>
</template>
