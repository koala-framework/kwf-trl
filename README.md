This project consists of two scripts:

## Parse: ##

This script parses a complete directory/package/web (.js, .php, .tpl) for trl() or trlKwf() function calls and generates a .po-file.
If used for package or web define kwf-path to exclude trls covered by kwf


### Usage: ###
`php bin/trl parseCode [-p|--path="..."] [-m|--mask="..."] [-k|--kwfpath="..."] [dir]`


## Convert: ##

This is used to convert an existing *trl.xml* into the more general *trl.po*.

### Usage: ###
`php bin/trl convertTrlXmlToPo [-p|--poPath[="..."]] [-b|--baseLanguage[="..."]] [-t|--targetLanguage[="..."]] xmlPath`
