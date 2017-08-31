Those scripts parse web, package or koala-framework (.js, .php, .tpl, .underscore.tpl) for trl() or trlKwf() function calls and generates .po-files.

Add this repository to your composer.json file and run composer update.
Simply run

`composer require koala-framework/kwf-trl`


### Usage: ###

`./vendor/bin/kwf-trl/bin/trl parseWeb`

`./vendor/bin/kwf-trl/bin/trl parsePackage`

`./vendor/bin/kwf-trl/bin/trl parseKwf`
