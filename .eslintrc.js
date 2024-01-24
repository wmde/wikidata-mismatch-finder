module.exports = {
	env: {
		node: true,
		jest: true
	},
	extends: [
		'eslint:recommended',
		'@vue/typescript/recommended',
		'plugin:vue/vue3-strongly-recommended',
	],
	parser: 'vue-eslint-parser',
	rules: {
		'max-len': [ 'error', 120 ],
		'no-multiple-empty-lines' : ['error', { 'max': 1 }],
		'vue/multi-word-component-names' : [ 'off' ]
		},
};
