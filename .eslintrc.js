module.exports = {
	env: {
		node: true,
		jest: true
	},
	extends: [
		'eslint:recommended',
		'@vue/typescript/recommended',
		'plugin:vue/essential',
	],
	parser: 'vue-eslint-parser',
	rules: {
		'max-len': [ 'error', 120 ],
		'no-multiple-empty-lines' : ['error', { 'max': 1 }],
		'vue/multi-word-component-names' : [ 'off' ],
		// Migration changes
		'vue/no-deprecated-v-bind-sync': 'off',
		'vue/no-deprecated-slot-attribute': 'off',
		'vue/require-explicit-emits': 'off',
		'vue/no-deprecated-v-on-native-modifier': 'off',
		'vue/no-deprecated-slot-scope-attribute': 'off',
		'vue/no-v-for-template-key-on-child': 'off',
		'vue/no-deprecated-destroyed-lifecycle': 'off',
		'vue/no-v-model-argument': 'off'
		},
};
