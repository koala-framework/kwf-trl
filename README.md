This project consists of two scripts:

## Convert: ##

This is used to convert an existing *trl.xml* into the more general *trl.po*.

### Usage: ###
php bin/trl convertTrlXmlToPo PATH-TO-XML PATH-FOR-GENEREATED-PO-FILE LANGUAGE-TO-USE-AS-ID LANGUAGE-OF-TRANSLATION


## Parse: ##

This script parses a complete directory/package/web (.js, .php, .tpl) for trl() or trlKwf() function calls and generates a .po-file.
If used for package or web define kwf-path to exclude trls covered by kwf


### Usage: ###
php bin/trl parseCode PATH-FOR-GENERATED-PO-FILE MASK(trl/trlKwf) DIRECTORY-TO-PARSE PATH-TO-KWF
