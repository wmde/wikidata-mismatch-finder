const path = require("path");
module.exports = {
    // Where are your vue tests located?
    "roots": [
        "<rootDir>/tests/Vue"
    ],
    // Make sure tests are run in a browser-like environment
    "testEnvironment": "jsdom",
    // vue: transform vue with vue-jest to make jest understand Vue's syntax
    // js: transform js files with babel, we can now use import statements in tests
    // ts: transform ts files with babel, to import ts files into js specs
    "transform": {
        ".*\\.(vue)$": "<rootDir>/node_modules/@vue/vue3-jest",
        "^.+\\.js$": "<rootDir>/node_modules/babel-jest",
        "^.+\\.tsx?$": "<rootDir>/node_modules/ts-jest"
    },
    // (Optional) This file helps you later for global settings
    "setupFilesAfterEnv": [
        "<rootDir>tests/Vue/setup.js"
    ],
    // (Optional) with that you can import your components like
    // "import Counter from '@/Counter.vue'"
    // (no need for a full path)
    "moduleNameMapper": {
        "^vue$": "@vue/compat",
        '^@vue/composition-api$': '@vue/compat',
        '^@wmde/wikit-vue-components$':
            '@wmde/wikit-vue-components/dist/wikit-vue-components-vue3compat.common.js',
        '^wikit-dist(.*)$': "<rootDir>/node_modules/@wmde/wikit-vue-components/dist$1",
        "^@/(.*)$": "<rootDir>/resources/js/$1",
    },
    // For Vue migration build
    // Further info: https://test-utils.vuejs.org/migration/#-vue-vue3-jest-jest-28
    "testEnvironmentOptions": {
        "customExportConditions": ["node", "node-addons"],
    },
    // For Vue migration build
    // Add compat config to test as well
    "globals": {
        "vue-jest": {
            "compilerOptions": {
                compatConfig: {
                    MODE: 3,
                    COMPILER_V_ON_NATIVE: true,
                    COMPILER_V_BIND_SYNC: true
                }
            }
        }
    }
}
