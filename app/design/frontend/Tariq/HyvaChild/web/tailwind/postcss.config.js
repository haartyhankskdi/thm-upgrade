const { postcssImportHyvaModules } = require("@hyva-themes/hyva-modules");

module.exports = {
    plugins: [
         postcssImportHyvaModules({
          excludeDirs: ["vendor/amasty/module-xsearch-hyva-compatibility"],
        }),
        require('postcss-import'),
        require('tailwindcss/nesting'),
        require('tailwindcss'),
        require('postcss-preset-env'),
    ]
}
