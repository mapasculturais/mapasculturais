module.exports = {
  extends: [
    'semistandard',
    "plugin:cypress/recommended"
  ],
  rules: {
    "semi": ["error", "always"],
    "quotes": ["error", "double"],
    "cypress/no-unnecessary-waiting": ["off"],
  }
};
