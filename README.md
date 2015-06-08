This script parses a complete directory/package/web (.js, .php, .tpl) for trl() or trlKwf() function calls and generates a .po-file.
If used for package or web define kwf-path to exclude trls covered by kwf


### Usage: ###
`php bin/trl parseCode [-p|--poFile="..."] [-m|--mask="..."] [-k|--kwfpath="..."] [dir]`
