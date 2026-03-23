module.exports = {
    content: [
        '../templates/**/*.phtml',
        '../layout/**/*.xml'
    ],
    theme: {
        extend: {
            colors: {
                ambar: {
                    background: 'var(--ambar-background)',
                    links: 'var(--ambar-links-color)',
                    'policy-text': 'var(--ambar-policy-text)',
                    'info-bar-background': 'var(--ambar-information-background)',
                    'info-bar-title': 'var(--ambar-information-title)',
                    'info-bar-description': 'var(--ambar-information-description)',
                    'info-bar-table-header': 'var(--ambar-information-table-header)',
                    'info-bar-table-content': 'var(--ambar-information-table-content)',
                    'info-bar-button': 'var(--ambar-information-button-color)',
                    'info-bar-button-hover': 'var(--ambar-information-button-hover-color)',
                    'info-bar-button-text': 'var(--ambar-information-button-text)',
                    'info-bar-button-hover-text': 'var(--ambar-information-button-hover-text)',
                    'settings-bar-button': 'var(--ambar-setting-button-color)',
                    'settings-bar-button-hover': 'var(--ambar-setting-button-hover-color)',
                    'settings-bar-button-text': 'var(--ambar-setting-button-text)',
                    'settings-bar-button-hover-text': 'var(--ambar-setting-button-hover-text)',
                    'settings-bar-group-background': 'var(--ambar-setting-bar-group-background)',
                    'settings-bar-group-title': 'var(--ambar-setting-bar-group-title)',
                    'settings-bar-group-description': 'var(--ambar-setting-bar-group-description)',
                    'settings-bar-group-links': 'var(--ambar-setting-bar-group-links)'
                }
            }
        }
    }
}
