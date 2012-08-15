Instructions for loading a new translation from verbatim:
(https://localize.mozilla.org/projects/mdn/)

1) scp sm-verbatim01:/home/lcrouch/mdn/{locale}/LC_MESSAGES/promote-mdn.po ./languages/promote-mdn-{lang}_{country}.po
2) msgfmt -o languages/promote-mdn-{lang}_{country}.mo languages/promote-mdn-{lang}_{country}.po
3) svn add languages
4) svn ci -m "adding {lang}_{country} translation"

Note: Be sure to add to both the git repos and the svn repos
