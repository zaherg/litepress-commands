# Litepress Composer Commands

This package provides Composer commands for setting up and managing Litepress projects. It handles various tasks such as:

- Project initialization
- Database setup
- WordPress installation
- WP-CLI configuration
- Cleanup operations

## Installation

```bash
composer require zaherg/litepress-composer-commands
```

## Usage

Add the following to your composer.json scripts section:

```json
{
    "scripts": {
        "post-create-project-cmd": [
            "Composer\\Litepress\\ScriptHandler::handleCreateProject"
        ],
        "post-install-cmd": [
            "Composer\\Litepress\\ScriptHandler::handleDatabase",
            "Composer\\Litepress\\ScriptHandler::handleWordPressInstallation",
            "Composer\\Litepress\\ScriptHandler::handleGeneratingWPCliConfigFile"
        ],
        "cleanup": [
            "Composer\\Litepress\\ScriptHandler::handleCleanup"
        ],
        "re-install": [
            "Composer\\Litepress\\ScriptHandler::handleReinstall"
        ]
    }
}
```

## Requirements

- PHP 8.3 or higher
- Composer 2.0 or higher

## License

MIT