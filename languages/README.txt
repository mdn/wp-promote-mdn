Instructions for working with verbatim for translations:
(https://localize.mozilla.org/projects/mdn/)

1) make/update the pot file:

php wp-i18n/makepot.php wp-plugin promote-mdn

Note: must copy the wp-promote-mdn folder to a folder named promote-mdn first.

2) svn commit the promote-mdn.pot file to http://svn.mozilla.org/projects/mdn/trunk/locale/templates/LC_MESSAGES/

3) go to https://localize.mozilla.org/templates/mdn/LC_MESSAGES/ and click "Update all from version control"

4) go to https://localize.mozilla.org/projects/mdn/admin.html and "Update from templates"

Later, after the individual promote-mdn.po files are translated ...

1) scp sm-verbatim01:/home/lcrouch/mdn/{locale}/LC_MESSAGES/promote-mdn.po ./languages/promote-mdn-{lang}_{country}.po
2) msgfmt -o languages/promote-mdn-{lang}_{country}.mo languages/promote-mdn-{lang}_{country}.po
3) svn add languages
4) svn ci -m "adding/updating {lang}_{country} translation"

Note: Be sure to add to both the git repos and the svn repos
