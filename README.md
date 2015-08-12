# Loadsys Composer Plugins

Including this package in your composer.json makes a number of installers and project types available.


## PHP CodeSniffer, `phpcs-coding-standard` type

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

The `postInstall` command checks every installed package for Coding Standard folders, and adds the path for any packaging containing Standards into `phpcs`'s `installed_paths` config setting. This should also be run `post-update` in case the package's installed_path has changed.

The `postPackageUninstall` removes the path for a package being removed from the phpcs config file.










# puphpet-release-composer-installer

Imported docs from the `loadsys/puphpet-release-composer-installer` project.

@TODO: Integrate these docs into the master README above.


[![Build Status](https://travis-ci.org/loadsys/puphpet-release-composer-installer.svg?branch=master)](https://travis-ci.org/loadsys/puphpet-release-composer-installer)

Provides a composer custom installer that works with `loadsys/puphpet-release` to add a PuPHPet.com vagrant box to a project via composer.

You probably will never need to use this project yourself directly. We use it for our [loadsys/puphpet-release](https://github.com/loadsys/puphpet-release) package to copy parts of the PuPHPet package into the necessary locations for the consuming project.


## :warning: Big Important Warning

It's critically important to point out that this installer does things that composer [very explicitly](https://github.com/composer/installers#should-we-allow-dynamic-package-types-or-paths-no) **should not be doing.** We break this very good and wise rule only because the tools we're working with (vagrant and puphpet) leave us with no other practical choice. Again: You should **NOT** do what this installer does. In all likelihood there is a better way.

If you use this installer, it will overwrite existing (important!) files in your project. If you have customized your Vagrantfile, then `composer require` a project that uses this installer, _your `Vagrantfile` file and `puphpet/` folder will be unceremoniously overwritten without notice._ Do not complain about this. This is what this installer is designed to do and you've been duly warned of its danger.


## Usage

To use this installer with another composer package, add the following block to your package's `composer.json` file:

```json
    "type": "puphpet-release",
    "require": {
        "loadsys/puphpet-release-composer-installer": "*"
    },
```


### Composer Post Install Actions

This installer is responsible for performing post-`composer install` actions for the `loadsys/puphpet-release` package.

When this package is included in another project via composer, the installer fires a number of additional actions in order to address some of the incompatibilities between puphpet's default setup and the requirements for Vagrant (such as the `Vagrantfile` living in the project's root directory instead of the composer-installed `/vendors/loadsys/puphpet-release/release/` folder.)

* Copies a Vagrantfile into the consuming project's root folder.
* Copies a puphpet/ folder into the consuming project's root folder.
* Copies the consuming project's `/puphpet.yaml` into the correct place as `/puphpet/config.yaml`.
* Tries to ensure that the consuming project's `/.gitignore` file contains the proper entries to ignore `/Vagrantfile` and `/puphpet/`, if it is present.

Unresolved Questions:

* Do we always overwrite the Vagrantfile and puphpet/ folders?
* What if there are customizations to files/ or exec-*/ folders? Should we even try to detect those? (diff the contents of the package's release/ folder with the versions in project root?)
* Should we try to validate that the target project's config.yaml file has all expected (mandatory) keys as the spec changes upstream. Can we write/maintain a "unit test" and/or diffing tool for it? It's just YAML after all.
* What should we do if there isn't a `/puphpet.yaml` for us to copy? The VM will surely not work correctly with completely "default" options. Maybe prompt the user to go generate one?


## Contributing


### Running Unit Tests

* `composer install`
* `vendor/bin/phpunit`


### Manually Testing Installer Output

Testing this composer plugin is difficult because it involves at least 2 other projects: the loadsys/puphpet-release, and the project from which you want to consume it. This project contains a `tests/integration/` directory that is set up to exercise this installer and test the result of including the `loadsys/puphpet-release` package in a consumer project. To use it:

1. Check out this project: `git clone git@github.com:loadsys/puphpet-release-composer-installer.git`

1. Check out a copy of the puphpet-release project somewhere to work on it. `git clone git@github.com:loadsys/puphpet-release.git` (Make a note of this path.)

1. Create a feature branch in either project, and **commit** your changes to the branch. (Committing the changes is very important to the process: Any changes you wish to test must exist in the git index already, not just in your working copy.)

1. Run `./tests/integration/simulate-composer-install.sh`

	The script will prompt you for any necessary information, reset the build/ dir for use, write the appropriate "composer.json" changes for you, and execute a `composer install` command for you in the build/ dir where you can review the results.

	* The `build/` folder should end up with a `Vagrantfile` and `puphpet/` folder in it.
	* The sample `build/puphpet.yaml` file should have been copied to `build/puphpet/config.yaml`.
	* The sample `.gitignore` file should have been "safely" updated to include the new additions to the "root" project folder (`build/`).

1. From here, the process loops through the following steps:
	* Make changes to the puphpet-release or puphpet-release-composer-installer projects.
	* **Commit** the changes to your working branch.
	* Run `./tests/integration/simulate-composer-install.sh` again.
	* Check the results in the `build/` directory.
	* Repeat.

1. Once you're satisfied with the results, push your branch and submit a PR.


### Running Integration Tests

The simulation script also includes a number of functional tests for verifying the results of the installer's operation. Use the `-t` flag to enable them.

* `./tests/integration/simulate-composer-install.sh -t [puphpet-release-branchname]` # Release project branch name defaults to `master`.

The script will report any errors and exit non-zero on failure.


## License

[MIT](https://github.com/loadsys/puphpet-release/blob/master/LICENSE). In particular, all [PuPHPet](http://puphpet.com) work belongs to the original authors. This project is strictly for our own convenience.


## Copyright

&copy; [Loadsys Web Strategies](http://loadsys.com) 2015
