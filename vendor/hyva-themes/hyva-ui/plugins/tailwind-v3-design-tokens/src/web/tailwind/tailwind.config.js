const colors = require("tailwindcss/colors");
const { twProps, mergeTailwindConfig } = require("@hyva-themes/hyva-modules");

/**
 * The Hyvä Tailwind
 * For customizing this file please see the TailwindCSS Docs
 *
 * @link https://tailwindcss.com/docs/configuration
 */

/** @type {import('tailwindcss').Config} */
module.exports = mergeTailwindConfig({
    // Examples for excluding patterns from purge
    content: [
        // this theme's phtml and layout XML files
        "../../**/*.phtml",
        "../../*/layout/*.xml",
        "../../*/page_layout/override/base/*.xml",
        // parent theme in Vendor (if this is a child-theme)
        "../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml",
        "../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml",
        "../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml",
        // app/code phtml files (if need tailwind classes from app/code modules)
        "../../../../../../../app/code/**/*.phtml",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Segoe UI", "Helvetica Neue", "Arial", "sans-serif"],
            },
            colors: twProps({
                primary: {
                    lighter: colors.purple["600"],
                    DEFAULT: colors.purple["700"],
                    darker: colors.purple["800"],
                },
                secondary: {
                    lighter: colors.purple["100"],
                    DEFAULT: colors.purple["200"],
                    darker: colors.purple["300"],
                },
                background: {
                    lighter: colors.purple["100"],
                    DEFAULT: colors.purple["200"],
                    darker: colors.purple["300"],
                },
                container: {
                    lighter: "#f5f5f5",
                    DEFAULT: "#e7e7e7",
                    darker: "#b6b6b6",
                },
            }),
            backgroundColor: twProps({
                container: {
                    lighter: "#ffffff",
                    DEFAULT: "#fafafa",
                    darker: "#f5f5f5",
                },
            }),
            textColor: ({ theme }) => ({
                ...twProps(
                    {
                        primary: {
                            lighter: colors.gray["700"],
                            DEFAULT: colors.gray["800"],
                            darker: colors.gray["900"],
                        },
                        secondary: {
                            lighter: colors.gray["400"],
                            DEFAULT: colors.gray["600"],
                            darker: colors.gray["800"],
                        },
                    },
                    "text"
                ),
                // Extends and uses the same colors for the text, background, and border
                ...twProps({
                    color: {
                        primary: theme("colors.primary"),
                        secondary: theme("colors.secondary"),
                    },
                }),
                // Fallback for `text-red`, will be removed in the future
                ...{ red: { DEFAULT: colors.red["500"] } },
            }),
            minHeight: {
                a11y: "44px",
                "screen-25": "25vh",
                "screen-50": "50vh",
                "screen-75": "75vh",
            },
            maxHeight: {
                "screen-25": "25vh",
                "screen-50": "50vh",
                "screen-75": "75vh",
            },
            container: {
                center: true,
                padding: "1.5rem",
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
});
