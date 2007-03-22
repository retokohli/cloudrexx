INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` ) VALUES ('', '2', 'navigation', 'TXT_LINKS_MODULE_DESCRIPTION', '1', 'index.php?cmd=directory', '_self', '12', '0');
INSERT INTO `contrexx_modules` VALUES (12, 'directory', 'TXT_LINKS_MODULE_DESCRIPTION', 'y', 0, 0);

INSERT INTO `contrexx_module_repository` VALUES (443, 12, '<!-- BEGIN restore_password -->\r\n<form name=\\"lostPassword\\" method=\\"post\\" action=\\"?section=directory&cmd=lostpw\\">\r\n  <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr> \r\n      <td colspan=\\"2\\">{TXT_LOST_PW}</td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\"><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">{TXT_EMAIL}:</td>\r\n\r\n      <td><input name=\\"email\\"  style=\\"width:220px;\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_RESET}\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\"><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\">{TXT_STATUS}</td>\r\n    </tr>\r\n</table>\r\n</form>\r\n<!-- END restore_password -->\r\n<!-- BEGIN restore_password_in_progress -->\r\n<table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr> \r\n      <td>{TXT_STATUS}</td>\r\n    </tr>\r\n    <tr> \r\n      <td><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td><a href=\\"?section=directory\\">{TXT_BACK}</a></td>\r\n    </tr>\r\n</table>\r\n<!-- END restore_password_in_progress -->\r\n<br/>', 'Password lost', 'lostpw', 'y', 442, 'off', 'daeppen', 0, '2');
INSERT INTO `contrexx_module_repository` VALUES (444, 12, '<table cellspacing=\\"0\\" summary=\\"category\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<th width=\\"94%\\" height=\\"22\\" align=\\"left\\">&raquo;&nbsp;<a href=\\"?section=directory\\">{TXT_DIR}</a>{TXT_CATEGORY}<br />{TXT_DESCRIPTION_CAT}</th>\r\n<th width=\\"6%\\" valign=\\"middle\\"><div align=\\"center\\">{RSS_CAT}</div></th>\r\n</tr>\r\n</table>\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" summary=\\"rss\\" width=\\"100%\\" border=\\"0\\" id=\\"rss\\">\r\n<tr>\r\n<td><br /></td>\r\n</tr>\r\n<!-- BEGIN showFeed -->\r\n<tr>\r\n<td>\r\n<table cellspacing=\\''0\\'' cellpadding=\\''1\\'' summary=\\"feed\\" width=\\''100%\\'' border=\\''0\\'' class=\\"feed\\">\r\n<tr>\r\n<th colspan=\\''2\\''><div align=\\''left\\''>{TXT_TITLE}&nbsp;{NEW_FEED}</div></th>\r\n</tr>\r\n<tr>\r\n<td colspan=\\''2\\''>{TXT_DESCRIPTION}</td>\r\n</tr>\r\n<tr>\r\n<td valign=\\''top\\'' width=\\''17%\\''><div align=\\''left\\''><b>{TXT_EXTENSION_NAME}</b></div></td>\r\n<td valign=\\''top\\'' width=\\''83%\\''>{TXT_EXTENSION_VALUE}</td>\r\n</tr>\r\n{TXT_EXTENSION_SIZE}\r\n{TXT_EXTENSION_MD5}\r\n<!-- BEGIN fieldsFeed -->\r\n<tr>\r\n<td valign=\\''top\\'' width=\\''17%\\''><div align=\\''left\\''><b>{TXT_NAME}</b></div></td>\r\n<td valign=\\''top\\'' width=\\''83%\\''>{TXT_VALUE}</td>\r\n</tr><!-- END fieldsFeed -->\r\n<tr>\r\n<td class=\\''footer\\''>{TXT_DATE}</td>\r\n<td class=\\''footer\\'' valign=\\''top\\''><div align=\\''right\\''>{TXT_HITS}&nbsp;Hits&nbsp;</div></td>\r\n</tr>\r\n</table>\r\n<br />\r\n</td>\r\n</tr>\r\n<!-- END showFeed -->\r\n<!-- BEGIN noFeeds -->\r\n<tr>\r\n<td><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noFeeds -->\r\n</table>', 'Show feed', 'detail', 'y', 442, 'off', 'daeppen', 1, '2');
INSERT INTO `contrexx_module_repository` VALUES (442, 12, '<table cellspacing=\\"0\\" summary=\\"directory\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<th width=\\"94%\\" height=\\"22\\" align=\\"left\\">&raquo;&nbsp;<a href=\\"?section=directory\\">{TXT_DIR}</a>{TXT_CATEGORY}<br />{TXT_DESCRIPTION_CAT}</th>\r\n<th width=\\"6%\\" valign=\\"middle\\"><div align=\\"center\\"><!-- {RSS_CAT} --></div></th>\r\n</tr>\r\n<!-- BEGIN showCategories -->\r\n<tr>\r\n<td colspan=\\"2\\">\r\n<table cellspacing=\\"0\\" summary=\\"categories\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" class=\\"categories\\">\r\n<tr>\r\n{CATEGORY}\r\n</tr>\r\n</table>\r\n</td>\r\n</tr>\r\n<!-- END showCategories -->\r\n</table>\r\n<br />\r\n<table cellspacing=\\"0\\" summary=\\"entries\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" class=\\"feed\\">\r\n<tr>\r\n<th width=\\"58%\\">Name&nbsp;</th>\r\n<th width=\\"25%\\" colspan=\\"2\\">Details&nbsp;</th>\r\n<th width=\\"11%\\">Added&nbsp;</th>\r\n<th width=\\"6%\\">Hits&nbsp;</th>\r\n</tr>\r\n<!-- BEGIN showLatest -->\r\n<tr>\r\n<td colspan=\\"5\\" class=\\"title\\" valign=\\"top\\"><a href=\\"?section=directory&amp;cmd=detail&amp;id={DETAIL}\\">{NAME}</a>&nbsp;{NEW_FEED}</td>\r\n</tr>\r\n<tr>\r\n<td class=\\"content\\" valign=\\"top\\">{DES}&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"8%\\">Typ:&nbsp;<br />Autor:&nbsp;<br />Source:&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"17%\\">{TYP}<br />{AUTHOR}<br />{LINK}</td>\r\n<td class=\\"content\\" valign=\\"top\\">{DATE}</td>\r\n<td class=\\"content\\" valign=\\"top\\">{HITS}&nbsp;{TXT_HITS}&nbsp;</td>\r\n</tr>\r\n<!-- END showLatest -->\r\n<!-- BEGIN noFeeds -->\r\n<tr>\r\n<td class=\\"spacer\\"><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noFeeds -->\r\n</table>\r\n<br />\r\n{SEARCH_PAGING}', 'Security Directory', '', 'y', 0, 'on', 'schmid', 2, '2');
INSERT INTO `contrexx_module_repository` VALUES (451, 12, '{STATUS} <!-- BEGIN login -->\r\n<form onsubmit=\\"return CheckForm()\\" action=\\"?section=directory&amp;cmd=add\\" method=\\"post\\" enctype=\\"multipart/form-data\\" name=\\"addEntry\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"150\\">{TXT_USERNAME}:</td>\r\n                <td><input style=\\"width: 220px;\\" name=\\"username\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"150\\">{TXT_PW}:</td>\r\n                <td><input type=\\"password\\" style=\\"width: 220px;\\" name=\\"password\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"150\\">&nbsp;</td>\r\n                <td><input type=\\"submit\\" value=\\"{TXT_LOGIN}\\" name=\\"login\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"150\\">&nbsp;</td>\r\n                <td><a href=\\"?section=directory&amp;cmd=lostpw\\">{TXT_LOST_PW}</a></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"150\\">&nbsp;</td>\r\n                <td><br /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"150\\">&nbsp;</td>\r\n                <td>{TXT_NOLOGIN}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<br /> <!-- END login --> <!-- BEGIN newFeed -->\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\nfunction CheckForm() {\r\nwith( document.addEntry ) {\r\nif (new_description.value == \\"\\" || new_name.value == \\"\\")\r\n{\r\nalert (\\"{TXT_FIELDS_REQUIRED}\\");\r\nreturn false;\r\n}\r\nreturn true;\r\n}\r\n}\r\nfunction hideRow(type)\r\n{\r\nif (type==\\''file\\'')\r\n{\r\ndocument.getElementById(\\''hiddenfile\\'').style.display = \\''block\\'';\r\ndocument.getElementById(\\''hiddenlink\\'').style.display = \\''none\\'';\r\ndocument.getElementById(\\''hiddenrss\\'').style.display = \\''none\\'';\r\ndocument.addEntry.linkname.value = \\''http://\\'';\r\ndocument.addEntry.rssname.value = \\''http://\\'';\r\nreturn true;\r\n}\r\nelse if (type==\\''rss\\'')\r\n{\r\ndocument.getElementById(\\''hiddenlink\\'').style.display = \\''none\\'';\r\ndocument.getElementById(\\''hiddenfile\\'').style.display = \\''none\\'';\r\ndocument.getElementById(\\''hiddenrss\\'').style.display = \\''block\\'';\r\ndocument.addEntry.linkname.value = \\''http://\\'';\r\nreturn true;\r\n}\r\nelse\r\n{\r\ndocument.getElementById(\\''hiddenlink\\'').style.display = \\''block\\'';\r\ndocument.getElementById(\\''hiddenfile\\'').style.display = \\''none\\'';\r\ndocument.getElementById(\\''hiddenrss\\'').style.display = \\''none\\'';\r\ndocument.addEntry.rssname.value = \\''http://\\'';\r\nreturn true;\r\n}\r\n}\r\n</script>\r\n<form onsubmit=\\"return CheckForm()\\" action=\\"?section=directory&amp;cmd=add\\" method=\\"post\\" enctype=\\"multipart/form-data\\" name=\\"addEntry\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\">\r\n        <tbody>\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\">{TXT_FILETYPE}:</td>\r\n                <td>           <input type=\\"radio\\" checked=\\"\\" onclick=\\"javascript:hideRow(\\''rss\\'')\\" value=\\"rss\\" name=\\"type\\" />         {TXT_RSSLINK}         <input type=\\"radio\\" onclick=\\"javascript:hideRow(\\''file\\'')\\" value=\\"file\\" name=\\"type\\" />         {TXT_FILE}          <input type=\\"radio\\" onclick=\\"javascript:hideRow(\\''link\\'')\\" value=\\"link\\" name=\\"type\\" />         {TXT_LINK}</td>\r\n            </tr>\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\">{TXT_CATEGORY}:</td>\r\n                <td>         <select style=\\"width: 300px;\\" name=\\"inputValue[catid]\\"></select>       </td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    <div style=\\"display: none;\\" id=\\"hiddenlink\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\">\r\n        <tbody>\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\" height=\\"26\\">{TXT_LINK}:<font color=\\"red\\">*</font></td>\r\n                <td><input value=\\"http://\\" style=\\"width: 300px;\\" name=\\"linkname\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    </div>\r\n    <div id=\\"hiddenrss\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\">\r\n        <tbody>\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\" height=\\"26\\">{TXT_RSSLINK}:</td>\r\n                <td><input value=\\"http://\\" style=\\"width: 300px;\\" name=\\"rssname\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    </div>\r\n    <div style=\\"display: none;\\" id=\\"hiddenfile\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\">\r\n        <tbody>\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\" height=\\"26\\">{TXT_FILE}:<font color=\\"red\\">*</font></td>\r\n                <td>          <input type=\\"file\\" style=\\"width: 300px;\\" size=\\"37\\" value=\\"\\" name=\\"fileName\\" />         </td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    </div>\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"center\\" class=\\"adminlist\\">\r\n        <!-- BEGIN inputfieldsOutput -->\r\n        <tbody>\r\n            <tr class=\\"{FIELD_ROW}\\">\r\n                <td width=\\"150\\" valign=\\"top\\">{FIELD_NAME}:</td>\r\n                <td>{FIELD_VALUE}</td>\r\n            </tr>\r\n            <!-- END inputfieldsOutput -->\r\n            <tr class=\\"row1\\">\r\n                <td width=\\"150\\">&nbsp;</td>\r\n                <td><input type=\\"submit\\" value=\\"{TXT_ADD}\\" name=\\"new_submit\\" />&nbsp;<font color=\\"red\\">*</font> = {TXT_REQUIRED_FIELDS}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    <input type=\\"hidden\\" style=\\"width: 300px;\\" size=\\"37\\" value=\\"{ADDED_BY_ID}\\" name=\\"inputValue[addedby]\\" />\r\n</form>\r\n<br /> <!-- END newFeed -->', 'Submit', 'add', 'n', 442, 'on', 'daeppen', 3, '2');
INSERT INTO `contrexx_module_repository` VALUES (453, 12, '<!-- START module_directory_user_add.html -->\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\nfunction checkForm()\r\n{\r\n	if (document.addForm.firstname.value == \\"\\" || document.addForm.lastname.value == \\"\\" || document.addForm.email.value == \\"\\" || document.addForm.username.value == \\"\\" || document.addForm.password.value == \\"\\") \r\n		{\r\n		        alert (\\"{TXT_FIELDS_REQUIRED}\\");       \r\n		        return false;\r\n		}else{ 	\r\n		    return true;\r\n		}\r\n\r\n}\r\n\r\nfunction checkPW()\r\n{\r\n	if (document.addForm.password.value == \\"\\") \r\n		{\r\n		        alert (\\"{TXT_FIELDS_REQUIRED}\\");       \r\n		        return false;\r\n		}else{ 	\r\n		    return true;\r\n		}\r\n\r\n}\r\n\r\n</script>\r\n{STATUS}\r\n<!-- BEGIN activate -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\">{TXT_SUCCESSFULL_ACTIVATE}</td>\r\n    </tr>\r\n</table>\r\n<!-- END activate -->\r\n<!-- BEGIN pw_updated -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\">{TXT_DIR_PW_SUCCESSFULL_UPDATED}</td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\"><br /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\"><a href=\\"?section=directory\\">{TXT_BACK}</a></td>\r\n    </tr>\r\n</table>\r\n<!-- END pw_updated -->\r\n<!-- BEGIN restore_pw -->\r\n<form name=\\"addForm\\" method=\\"post\\" action=\\"?section=directory&cmd=reg\\" onsubmit=\\"return checkPW()\\">\r\n  <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n     <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\">{TXT_SUCCESSFULL_RESETE}</td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\"><br /></td>\r\n    </tr>\r\n     <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_USERNAME}: <font color=\\"red\\"></font><input type=\\"hidden\\" name=\\"userid\\" style=\\"width: 20px;\\" maxlength=\\"255\\" value=\\"{USER_ID}\\" /></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"username_fake\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{USER_NAME}\\" disabled /></td>\r\n    </tr> \r\n     <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_PASSWORD}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"password\\" name=\\"password\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"\\" /></td>\r\n    </tr>  \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" value=\\"{TXT_REG}\\" name=\\"restore_submit\\" />&nbsp;<font color=\\"red\\">*</font><b></b> = {TXT_REQUIRED_FIELDS}</td>\r\n    </tr>\r\n    </table>\r\n</form>\r\n<!-- END restore_pw -->\r\n<!-- BEGIN registration -->\r\n<form name=\\"addForm\\" method=\\"post\\" action=\\"?section=directory&cmd=reg\\" onsubmit=\\"return checkForm()\\">\r\n  <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_USERNAME}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"username\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{USERNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_PASSWORD}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"password\\" name=\\"password\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"\\" /></td>\r\n    </tr>  \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_FIRST_NAME}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"firstname\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{FIRSTNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_LAST_NAME}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"lastname\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{LASTNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_EMAIL}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"email\\" style=\\"width: 220px;\\" maxlength=\\"255\\" value=\\"{EMAIL}\\" />\r\n      </td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" value=\\"{TXT_REG}\\" name=\\"add_submit\\" />&nbsp;<font color=\\"red\\">*</font><b></b> = {TXT_REQUIRED_FIELDS}</td>\r\n    </tr>\r\n    </table>\r\n</form>\r\n<!-- END registration -->\r\n<br />\r\n<!-- END module_directory_user_add.html -->', 'Registration', 'reg', 'y', 442, 'off', 'schmid', 4, '2');
INSERT INTO `contrexx_module_repository` VALUES (455, 12, '<form action=\\"?section=directory&amp;cmd=search\\" method=\\"post\\">\r\n<table summary=\\"search box\\" cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\">\r\n<tr><td colspan=\\"4\\"><input name=\\"term\\" value=\\"Searchterm...\\" size=\\"35\\" maxlength=\\"100\\" onFocus=\\"this.value=\\''\\''\\" /><input type=\\"hidden\\" name=\\"section\\" value=\\"directory\\" size=\\"30\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"cmd\\" value=\\"search\\" size=\\"30\\" maxlength=\\"100\\" />&nbsp;<input value=\\"{TXT_SEARCH}\\" name=\\"search\\" type=\\"submit\\" /></td>\r\n</tr>\r\n</table>\r\n</form>\r\n<br />\r\n<table summary=\\"search results\\" cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" class=\\"feed\\">\r\n<!-- BEGIN showTitle -->\r\n<tr>\r\n<th width=\\"58%\\">Name&nbsp;</td>\r\n<th width=\\"25%\\" colspan=\\"2\\">Details&nbsp;</th>\r\n<th width=\\"11%\\">Added&nbsp;</th>\r\n<th width=\\"6%\\">Hits&nbsp;</th>\r\n</tr>\r\n<!-- END showTitle -->\r\n<!-- BEGIN showResults -->\r\n<tr>\r\n<td colspan=\\"5\\" class=\\"title\\" valign=\\"top\\"><a href=\\"?section=directory&amp;cmd=detail&amp;id={DETAIL}\\">{NAME}</a>&nbsp;{NEW_FEED}</td>\r\n</tr>\r\n<tr>\r\n<td class=\\"content\\" valign=\\"top\\">{DES}&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"8%\\">Typ:&nbsp;<br />Autor:&nbsp;<br />Source:&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"17%\\">{TYP}<br />{AUTHOR}<br />{LINK}</td>\r\n<td class=\\"content\\" valign=\\"top\\">{DATE}</td>\r\n<td class=\\"content\\" valign=\\"top\\">{HITS}&nbsp;{TXT_HITS}&nbsp;</td>\r\n</tr>\r\n<!-- END showResults -->\r\n<!-- BEGIN noResult -->\r\n<tr>\r\n<td class=\\"spacer\\"><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noResult -->\r\n</table>\r\n<br />\r\n{SEARCH_PAGING}', 'Search', 'search', 'y', 442, 'on', 'daeppen', 6, '2');



DROP TABLE IF EXISTS `contrexx_module_directory_categories`;
CREATE TABLE `contrexx_module_directory_categories` (
  `id` smallint(6) NOT NULL auto_increment,
  `parentid` smallint(6) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NOT NULL default '',
  `displayorder` smallint(6) unsigned NOT NULL default '1000',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` varchar(250) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `contrexx_module_directory_dir`;
CREATE TABLE `contrexx_module_directory_dir` (
  `id` mediumint(7) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `date` varchar(14) default NULL,
  `description` mediumtext NOT NULL,
  `size` int(9) default '0',
  `checksum` varchar(255) NOT NULL default '',
  `relatedlinks` varchar(255) NOT NULL default '',
  `typ` varchar(10) NOT NULL default '',
  `catid` smallint(5) NOT NULL default '0',
  `platform` varchar(40) NOT NULL default '',
  `language` varchar(40) NOT NULL default '',
  `hits` int(9) NOT NULL default '0',
  `popular_hits` int(7) NOT NULL default '0',
  `popular_date` varchar(30) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  `addedby` varchar(50) NOT NULL default '',
  `provider` varchar(255) NOT NULL default '',
  `homepage` varchar(100) NOT NULL default '',
  `ip` varchar(255) NOT NULL default '',
  `mail` varchar(50) NOT NULL default '',
  `validatedate` varchar(14) NOT NULL default '',
  `lastip` varchar(50) NOT NULL default '',
  `xml_refresh` varchar(15) NOT NULL default '',
  `canton` varchar(50) NOT NULL default '',
  `searchkeys` varchar(255) NOT NULL default '',
  `coname` varchar(100) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `plz` varchar(5) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `person` varchar(100) NOT NULL default '',
  `infozeile` varchar(100) NOT NULL default '',
  `telefax` varchar(15) NOT NULL default '',
  `mobile` varchar(15) NOT NULL default '',
  `branche` varchar(100) NOT NULL default '',
  `rechtsform` varchar(50) NOT NULL default '',
  `umsatz` varchar(50) NOT NULL default '',
  `mitarbeiter` varchar(255) NOT NULL default '',
  `gründungsjahr` varchar(10) NOT NULL default '',
  `mwst` varchar(50) NOT NULL default '',
  `öffnungszeiten` varchar(255) NOT NULL default '',
  `betriebsferien` varchar(255) NOT NULL default '',
  `suchorte` varchar(255) NOT NULL default '',
  `logo` varchar(50) NOT NULL default '',
  `team` varchar(255) NOT NULL default '',
  `bilder` varchar(255) NOT NULL default '',
  `referenzen` varchar(255) NOT NULL default '',
  `angebote` varchar(255) NOT NULL default '',
  `konzept` varchar(255) NOT NULL default '',
  `map` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`),
  KEY `date` (`date`),
  KEY `temphitsout` (`popular_hits`),
  FULLTEXT KEY `name` (`title`,`description`)
) ENGINE=MyISAM;



DROP TABLE IF EXISTS `contrexx_module_directory_inputfields`;
CREATE TABLE `contrexx_module_directory_inputfields` (
  `id` int(7) NOT NULL auto_increment,
  `typ` int(2) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `active` int(1) NOT NULL default '0',
  `is_required` int(11) NOT NULL default '0',
  `read_only` int(1) NOT NULL default '0',
  `sort` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



INSERT INTO `contrexx_module_directory_inputfields` VALUES (1, 1, 'title', 'TXT_DIR_F_TITLE', 1, 1, 0, 1);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (2, 2, 'description', 'TXT_DIR_F_DESCRIPTION', 1, 1, 0, 2);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (3, 3, 'platform', 'TXT_DIR_F_PLATFORM', 1, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (4, 3, 'language', 'TXT_DIR_F_LANG', 1, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (5, 1, 'addedby', 'TXT_DIR_F_ADDED_BY', 1, 1, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (6, 1, 'relatedlinks', 'TXT_DIR_F_RELATED_LINKS', 1, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (7, 3, 'canton', 'TXT_DIR_F_CANTON', 0, 0, 0, 8);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (8, 2, 'searchkeys', 'TXT_DIR_F_SEARCH_KEYS', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (9, 1, 'coname', 'TXT_DIR_F_CO_NAME', 0, 0, 0, 3);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (10, 1, 'street', 'TXT_DIR_F_STREET', 0, 0, 0, 5);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (11, 1, 'plz', 'TXT_DIR_F_PLZ', 0, 0, 0, 6);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (12, 1, 'phone', 'TXT_DIR_F_PHONE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (13, 1, 'person', 'TXT_DIR_F_PERSON', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (15, 1, 'infozeile', 'TXT_INFOZEILE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (14, 1, 'city', 'TXT_DIR_CITY', 0, 0, 0, 7);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (16, 1, 'telefax', 'TXT_TELEFAX', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (17, 1, 'mobile', 'TXT_MOBILE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (18, 1, 'mail', 'TXT_EMAIL', 1, 0, 0, 5);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (19, 1, 'homepage', 'TXT_HOMEPAGE', 1, 0, 0, 4);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (20, 1, 'branche', 'TXT_BRANCHE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (21, 1, 'rechtsform', 'TXT_RECHTSFORM', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (22, 2, 'umsatz', 'TXT_UMSATZ', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (23, 2, 'mitarbeiter', 'TXT_MITARBEITER', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (24, 1, 'gründungsjahr', 'TXT_GRUENDUNGSJAHR', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (25, 1, 'mwst', 'TXT_MWST_NR', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (26, 2, 'öffnungszeiten', 'TXT_OEFFNUNGSZEITEN', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (27, 2, 'betriebsferien', 'TXT_BETRIEBSFERIEN', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (28, 2, 'suchorte', 'TXT_SUCHORTE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (29, 1, 'logo', 'TXT_LOGO', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (30, 2, 'team', 'TXT_TEAM', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (31, 1, 'bilder', 'TXT_BILDER', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (32, 2, 'referenzen', 'TXT_REFERENZEN', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (33, 2, 'angebote', 'TXT_ANGEBOTE', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (34, 2, 'konzept', 'TXT_KONZEPT', 0, 0, 0, 99);
INSERT INTO `contrexx_module_directory_inputfields` VALUES (35, 1, 'map', 'TXT_MAP', 0, 0, 0, 99);



DROP TABLE IF EXISTS `contrexx_module_directory_mail`;
CREATE TABLE `contrexx_module_directory_mail` (
  `id` tinyint(4) NOT NULL auto_increment,
  `tplname` varchar(60) NOT NULL default '',
  `protected` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



INSERT INTO `contrexx_module_directory_mail` VALUES (4, 'TXT_DIR_MAIL_NEW_ACCOUNT', 1);
INSERT INTO `contrexx_module_directory_mail` VALUES (5, 'TXT_DIR_MAIL_LOST_PASSWORD', 1);
INSERT INTO `contrexx_module_directory_mail` VALUES (6, 'TXT_DIR_MAIL_CONFIRM_PASSWORD', 1);
INSERT INTO `contrexx_module_directory_mail` VALUES (7, 'TXT_DIR_MAIL_CONFIRM_FEED', 1);



DROP TABLE IF EXISTS `contrexx_module_directory_mail_content`;
CREATE TABLE `contrexx_module_directory_mail_content` (
  `id` tinyint(4) NOT NULL auto_increment,
  `tpl_id` tinyint(4) NOT NULL default '0',
  `lang_id` tinyint(2) unsigned NOT NULL default '0',
  `from_mail` varchar(255) NOT NULL default '',
  `xsender` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `contrexx_module_directory_settings`;
CREATE TABLE `contrexx_module_directory_settings` (
  `setid` smallint(6) NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setdescription` varchar(50) NOT NULL default '',
  `settyp` int(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) ENGINE=MyISAM;


INSERT INTO `contrexx_module_directory_settings` VALUES (5, 'xmlLimit', '15', 'XML Limite', 1);
INSERT INTO `contrexx_module_directory_settings` VALUES (6, 'platforms', ', Windows (all), Win2003 Server, WinXP, Win2k, Win9x, WinNT, WinME, WinCE, Linux, Solaris, HPUX, FreeBSD, PalmOS, Java, MacOS, IRIX, OS/2, DOS, Unix', 'Plattformen', 0);
INSERT INTO `contrexx_module_directory_settings` VALUES (7, 'language', ',Deutsch, English, Italian, French', 'Sprachen', 0);
INSERT INTO `contrexx_module_directory_settings` VALUES (10, 'latest', '30', 'Anzahl neuste Einträge', 1);
INSERT INTO `contrexx_module_directory_settings` VALUES (11, 'popular', '20', 'Anzahl beliebteste Einträge', 1);
INSERT INTO `contrexx_module_directory_settings` VALUES (13, 'description', '0', 'Kategorie Beschreibung', 2);
INSERT INTO `contrexx_module_directory_settings` VALUES (12, 'status', '0', 'Automatisch aktiv', 2);
INSERT INTO `contrexx_module_directory_settings` VALUES (14, 'populardays', '1', 'Anzahl Tage für Popular', 1);
INSERT INTO `contrexx_module_directory_settings` VALUES (16, 'canton', ',Aargau, Appenzell-Ausserrhoden, Appenzell-Innerrhoden, Basel-Land, Basel-Stadt, Bern, Freiburg, Genf, Glarus, Graubünden, Jura, Luzern, Neuenburg, Nidwalden, Obwalden, St. Gallen, Schaffhausen, Schwyz, Solothurn, Thurgau, Tessin, Uri, Waadt, Wallis, Zug, Zürich', 'Kantone', 0);
INSERT INTO `contrexx_module_directory_settings` VALUES (17, 'refreshfeeds', '3600', 'XML aktualisieren (sec)', 1);
INSERT INTO `contrexx_module_directory_settings` VALUES (19, 'show_rss', '0', 'RSS anzeigen', 2);
INSERT INTO `contrexx_module_directory_settings` VALUES (20, 'show_links', '1', 'Links anzeigen', 2);
INSERT INTO `contrexx_module_directory_settings` VALUES (21, 'show_files', '1', 'Dateien anzeigen', 2);
INSERT INTO `contrexx_module_directory_settings` VALUES (22, 'mark_new_entrees', '4', 'Neue Einträge markieren (Day)', 1);



DROP TABLE IF EXISTS `contrexx_module_directory_users`;
CREATE TABLE `contrexx_module_directory_users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `username` varchar(40) default NULL,
  `password` varchar(32) default NULL,
  `regdate` varchar(30) default '2003-00-00',
  `email` varchar(255) default NULL,
  `firstname` varchar(150) default NULL,
  `lastname` varchar(150) default NULL,
  `language` varchar(50) NOT NULL default '',
  `active` tinyint(1) NOT NULL default '0',
  `md5key` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM;

