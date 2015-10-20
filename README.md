# Loadsys Composer Plugins

[![Build Status](https://travis-ci.org/loadsys/composer-plugins.svg?branch=master)](https://travis-ci.org/loadsys/composer-plugins)

Including this package in your composer.json makes a number of installers and project types available.




## PHP CodeSniffer, `phpcs-coding-standard` type (copying folders approach)

When you develop your own Coding Standard for the [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer), you can package it for installation via Composer, but `phpcs` won't know about your standard unless you either manually specify it on the command line every time:

```shell
$ vendor/bin/phpcs --standard=vendor/yourname/your-codesniffer/YourStandard .
```

Or you must use the `--config-set` switch to write your Standard's path into the `phpcs` config file:

```shell
$ vendor/bin/phpcs --config-set installed_paths vendor/yourname/your-codesniffer
```

Neither of these is convenient, and defeats the purpose of using Composer to make your dependencies "automatically" available to your project.

This plugin provide a custom installer that engages for the Composer `type` of `phpcs-coding-standard`.

When you create your coding standard package, use this `type` in your composer.json file, and `require` this package of composer plugins in order to gain access to the Installer for that type:

```json
{
  "name": "yourname/your-codesniffer",
  "type": "phpcs-coding-standard",
  "require": {
      "squizlabs/php_codesniffer": "~2.3",
      "loadsys/composer-plugins": "dev-master"
  }
}
```

You must also make sure your coding standard lives in a subfolder of the package, usually with a proper name. For example: `GIT_ROOT/YourStandard/ruleset.xml`. This allows for multiple standards to be installed from a single package.

It also usually makes sense for your coding standard to include the required version of `squizlabs/php_codesniffer` itself, so that your projects that use this standard don't have to require it themselves, or accidentally require the wrong version.

With this setup, when your standard is included in another project, the installer in this package will search its `vendor/your-name/your-codesniffer/` folder for folders that contain `ruleset.xml` files, indicating that those sub-folders contain Coding Standards. This installer will then copy those folders into the `vendor/squizlabs/CodeSniffer/Standards/` folder for you, making your Coding Standard immediately available to `phpcs --standard YourStandard .` without any additional configuration.




## PHP CodeSniffer, post-install hook (copying folders approach)

Sometimes you want to use somebody else's coding standard package where you can't set the `type` explicitly. In cases like this, this package provides composer hook scripts that can be used to accomplish the same effect.

The script works by scanning each installed package for any immediate sub-folders containing a `ruleset.xml` file (which indicates that folder contains a Coding Standard.) It then copies those folders into the `CodeSniffer/Standards/` directory, making them available to `phpcs`.

To use this hook script, add the following to your root project's `composer.json`:

```json
{
    "require": {
        "squizlabs/php_codesniffer": "~2.3",
        "loadsys/composer-plugins": "dev-master"
    },
    "scripts": {
        "post-install-cmd": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::postInstall"
        ],
        "post-update-cmd": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::postInstall"
        ],
        "pre-package-uninstall": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::prePackageUninstall"
        ]
    }
}
```

The `postInstall` command checks every installed package for Coding Standard folders, and copies those folders into the `CodeSniffer/Standards/` folder directly. This should also be run post-update, in order to copy updates to a Coding Standard into the correct place for use.

The `prePackageUninstall` removes any Coding Standard folders from a package that is being removed from the phpcs `CodeSniffer/Standards/` folder.




## PHP CodeSniffer, post-install hook (config editing approach)

:warning: Speculative! :warning:

@TODO: _This approach is partially coded already, but the plan is to eventually use an `extra` flag in the composer.json file to determine whether to copy standards folders or manage entries in phpcs's config file. This approach could be used both by the `phpcs-coding-standard` type as well as the postInstall hooks._



Sometimes you want to use somebody else's coding standard package where you can't set the `type` explicitly. In cases like this, this package provides composer hook scripts that can be used to accomplish the same effect.

The script works by scanning each installed package for any folders containing a `ruleset.xml` file (which indicates that folder contains a Coding Standard.) It then adds the vendor paths for these folders into the `CodeSniffer.conf` file, making them available to `phpcs` in their natural install location.

To use this hook script, add the following to your root project's `composer.json`:

```json
{
    "require": {
        "squizlabs/php_codesniffer": "~2.3",
        "loadsys/composer-plugins": "dev-master"
    },
    "scripts": {
        "post-install-cmd": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::postInstall"
        ],
        "post-update-cmd": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::postInstall"
        ],
        "pre-package-uninstall": [
            "Loadsys\\Composer\\PhpCodesniffer\\CodingStandardHook::prePackageUninstall"
        ]
    }
}
```

The `postInstall` command checks every installed package for Coding Standard folders, and adds the path for any packages containing Standards into `phpcs`'s `installed_paths` config setting. This should also be run `post-update` in case the package's installed_path has changed.

The `postPackageUninstall` removes the path for a package that is being removed from the phpcs config file.




## Contributing

Create an issue or submit a pull request.

### Running Unit Tests

* `composer install`
* `vendor/bin/phpunit`




## License

[MIT](https://github.com/loadsys/puphpet-release/blob/master/LICENSE).




## Copyright

&copy; 2015 [Loadsys Web Strategies](http://loadsys.com)
