module.exports = {
	env: {
		node: true,
	},
	extends: [
		'eslint:recommended',
		'@vue/typescript/recommended',
		'plugin:vue/essential',
	],
	parser: 'vue-eslint-parser',
	rules: {
		'max-len': [ 'error', 120 ],
		"no-multiple-empty-lines" : ["error", { "max": 1 }],
		},	
};
