This project consists of two scripts:

## Convert: ##

This is used to convert an existing *trl.xml* into the more general *trl.po*.

### Usage: ###
php bin/trl convertTrlXmlToPo PATH-TO-XML PATH-FOR-GENEREATED-PO-FILE LANGUAGE-TO-USE-AS-ID LANGUAGE-OF-TRANSLATION


## Parse: ##

This script parses a complete directory (.js, .php, .tpl) for trl() or trlKwf() function calls and generates a .po-file.


### Usage: ###
php bin/trl parseCode PATH-FOR-GENERATED-PO-FILE MASK(trl/trlKwf) DIRECTORY-TO-PARSE
