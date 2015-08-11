# composer-plugins
Including this package in your composer.json makes a number of installers and project types available.


## PHP CodeSniffer, Coding Standard installer type

When you develop your own Coding Standard, you can package it for installation via Composer, but `phpcs` won't know about your standard unless you either manually specify it on the command line:

```shell
$ vendor/bin/phpcs --standard=vendor/yourname/your-codesniffer/YourStandard .
```

Or you must use the `--config-set` switch to write your path into `phpcs`'s config file:

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

It also usually makes sense for your coding standard to include the required version of php_codesniffer itself, so that your projects that use this standard don't have to require it themselves, or accidentally require the wrong version.

With this setup, when your standard is included in another project, the installer in this package will search its `vendor/your-name/your-codesniffer/` folder for folders that contain `ruleset.xml` files, indicating that those sub-folders contain Coding Standards. This installer will then copy those folders into the `vendor/squizlabs/CodeSniffer/Standards/` folder for you, making your Coding Standard immediately available to `phpcs --standard YourStandard .` without any additional configuration.



## PHP CodeSniffer, post-install hook (copying folders approach)

Sometimes you want to use somebody else's coding standard package where you can't set the `type` explicitly. In cases like this, this package provides composer hook scripts that can be used to accomplish the same effect.

The script works by scanning each installed package for any folders containing a `ruleset.xml` file (which indicates that folder contains a Coding Standard.) It then copies those folders into the `CodeSniffer/Standards/` directory, making them available to `phpcs`.

To use this hook script, add the following to your root project's `composer.json`:

```json
{
    "require": {
        "squizlabs/php_codesniffer": "~2.3",
        "loadsys/composer-plugins": "dev-master"
    },
    "scripts": {
        "post-install-cmd": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::postInstall"
        ],
        "post-update-cmd": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::postInstall"
        ],
        "pre-package-uninstall": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::prePackageUninstall"
        ]
    }
}
```

The `postInstall` command checks every installed package for Coding Standard folders, and copies those folders into the `CodeSniffer/Standards/` folder directly. This should also be run post-update, in order to copy updates to a Coding Standard into the correct place for use.

The `prePackageUninstall` removes any Coding Standard folders from a package that is being removed from the phpcs `CodeSniffer/Standards/` folder.










# Config File Approach

## PHP CodeSniffer, post-install hook

Sometimes you want to use somebody else's coding standard package where you can't set the `type` explicitly. In cases like this, this package provides composer hook scripts that can be used to accomplish the same effect.

The script works by scanning each installed package for any folders containing a `ruleset.xml` file (which indicates that folder contains a Coding Standard.) It then adds the vendor paths for these folders into the CodeSniffer.conf file, making them available to `phpcs` in their natural install location.

To use this hook script, add the following to your root project's `composer.json`:

```json
{
    "require": {
        "squizlabs/php_codesniffer": "~2.3",
        "loadsys/composer-plugins": "dev-master"
    },
    "scripts": {
        "post-install-cmd": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::postInstall"
        ],
        "post-update-cmd": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::postInstall"
        ],
        "pre-package-uninstall": [
            "Loadsys\\Composer\\PhpcsCodingStandardHook::prePackageUninstall"
        ]
    }
}
```

The `postInstall` command checks every installed package for Coding Standard folders, and adds the path for any packaging containing Standards into `phpcs`'s `installed_paths` config setting. This should also be run `post-update` in case the package's installed_path has changed.

The `postPackageUninstall` removes the path for a package being removed from the phpcs config file.
