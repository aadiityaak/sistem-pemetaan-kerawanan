import prettier from 'eslint-config-prettier';
import vue from 'eslint-plugin-vue';

import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        ignores: [
            'vendor',
            'node_modules', 
            'public',
            'bootstrap/ssr',
            'tailwind.config.js',
            'resources/js/components/ui/*',
            'build-deployment/**/*',
            // Legacy files with many lint issues - ignore until cleanup
            'resources/js/pages/AiPrediction/Index.vue',
            'resources/js/pages/Dashboard.vue', 
            'resources/js/pages/Indas/DataEntry.vue',
            'resources/js/pages/KamtibmasCalendar/Index.vue',
            'resources/js/pages/MonitoringData/Create.vue',
            'resources/js/pages/MonitoringData/Edit.vue',
            'resources/js/pages/MonitoringData/Index.vue',
            'resources/js/pages/PartaiPolitik/Create.vue',
            'resources/js/pages/PartaiPolitik/Edit.vue', 
            'resources/js/pages/PartaiPolitik/Index.vue',
            'resources/js/pages/Sembako/Index.vue',
            'resources/js/pages/Users/Index.vue'
        ],
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
    prettier,
);
