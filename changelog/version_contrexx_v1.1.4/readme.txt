
01. 05.2007 Shop - Hersteller
-----------------------------
Neue MARKER für Frontend:
- SHOP_MANUFACTURER_NAME
- SHOP_MANUFACTURER_URL


07.08.2007 Seitenempfehlungen - Captcha
---------------------------------------
Neue MARKER für Frontend:
- RECOM_TXT_CAPTCHA
- RECOM_CAPTCHA_URL
- RECOM_CAPTCHA_ALT
- RECOM_CAPTCHA_OFFSET

Nachfolgend noch ein Codebeispiel zum Einbau des neuen Captcha:

[HTML]
<tr>
    <td>[[RECOM_TXT_CAPTCHA]]:</td>
    <td>
      <img src="[[RECOM_CAPTCHA_URL]]" alt="[[RECOM_CAPTCHA_ALT]]" title="[[RECOM_CAPTCHA_ALT]]" />
      <br /><br />
      <input type="text" name="captchaCode" /><input type="hidden" value="[[RECOM_CAPTCHA_OFFSET]]" name="captchaOffset" />
    </td>
</tr>
[/HTML]


20.08.2007 Direcrory Anpassungen
---------------------------------------
Platzhalter:
[[DIRECTORY_FEED_ATTACHMENT]] wird zu	[[DIRECTORY_FEED_ATTACHMENT]]
										[[DIRECTORY_FEED_LINK]]
										[[DIRECTORY_FEED_RSS_LINK]]

Nachfolgend die neuen Contentseiten fürs Directory:

section=directory&cmd=edit
[HTML]
<b></b><script language="JavaScript">
function add(from,dest,add,remove)
{
    if ( from.selectedIndex < 0)
	{
		if (from.options[0] != null)
			from.options[0].selected = true;
		from.focus();
		return false;
	}
	else
	{
		for (var i=0; i<from.length; i++)
		{
			if (from.options[i].selected)
			{
		    	dest.options[dest.length] = new Option( from.options[i].text, from.options[i].value, false, false);
   			}
		}
	    for (var i=from.length-1; i>=0; i--)
		{
			if (from.options[i].selected)
			{
		       from.options[i] = null;
   			}
		}
	}
    disableButtons(from,dest,add,remove);
}


function remove(from,dest,add,remove)
{
	if ( dest.selectedIndex < 0)
	{
		if (dest.options[0] != null)
			dest.options[0].selected = true;
		dest.focus();
		return false;
	}
	else
	{
		for (var i=0; i<dest.options.length; i++)
		{
			if (dest.options[i].selected)
			{
		    	from.options[from.options.length] = new Option( dest.options[i].text, dest.options[i].value, false, false);
   			}
		}
	    for (var i=dest.options.length-1; i>=0; i--)
		{
			if (dest.options[i].selected)
			{
		       dest.options[i] = null;
   			}
		}
	}
	disableButtons(from,dest,add,remove);
}

function disableButtons(from,dest,add,remove)
{
	if (from.options.length > 0 )
	{
		add.disabled = 0;
	}
	else
		add.disabled = 1;

	if (dest.options.length > 0)
		remove.disabled = 0;
	else
		remove.disabled = 1;
}

function selectAll(CONTROL)
{
	for(var i = 0;i < CONTROL.length;i++)
    {
		CONTROL.options[i].selected = true;
	}
}

function deselectAll(CONTROL)
{
	for(var i = 0;i < CONTROL.length;i++)
	{
		CONTROL.options[i].selected = false;
	}
}

[[DIRECTORY_JAVASCRIPT]]

</script>
[[DIRECTORY_SEARCH]]
<div id="directoryNavtree">
<a href="?section=directory">[[TXT_DIRECTORY_DIR]]</a>[[DIRECTORY_CATEGORY_NAVI]]
</div>
<!-- BEGIN directoryMessage -->
<table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
    <tbody>
        <tr>
            <td width="100%" valign="top">[[DIRECTORY_FEED_UPDATED]]<br /><br /></td>
        </tr>
        <tr>
            <td width="100%" valign="top">[[TXT_DIRECTORY_BACK]]</td>
        </tr>
    </tbody>
</table>
<!-- END directoryMessage -->
<!-- BEGIN directoryInputFields -->
<form name="addForm" enctype="multipart/form-data" method="post" action="index.php?section=directory&amp;cmd=edit" onSubmit="[[DIRECTORY_FORM_ONSUBMIT]]">
<input type="hidden" name="edit_id" size="10" value="[[DIRECTORY_ID]]" />
    <table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
        <tbody>
            <tr>
                <td width="120" valign="top">[[TXT_DIRECTORY_CATEGORY]]:<font color="red">*</font></td>
                <td>
                 <table width="100%" border="0">
		  <tr>
		    <td width="210">
		      <select name="notSelectedCat[]" size="12" style="width:170px;" multiple="multiple">
		          [[DIRECTORY_CATEGORY_DESELECTED]]
		      </select>
		    </td>
		    <td width="50">
		      <div align="center">
		        <input type="button" value=" &gt;&gt; " name="addCat" onclick="add(document.addForm.elements['notSelectedCat[]'],document.addForm.elements['selectedCat[]'],addCat,removeCat);" />
		        <br />
		        <input type="button" value=" &lt;&lt; " name="removeCat" onclick="remove(document.addForm.elements['notSelectedCat[]'],document.addForm.elements['selectedCat[]'],addCat,removeCat);" />
		        <br /><br />
		      </div>
		    </td>
		    <td>
		      <div align="left">
		        <select name="selectedCat[]" size="12" style="width:170px;" multiple="multiple">
		        	[[DIRECTORY_CATEGORY_SELECTED]]
		        </select>
		      </div>
		    </td>
		  </tr>
		</table>
                </td>
            </tr>
            <!-- BEGIN directoryLevels -->
            <tr>
                <td width="120" valign="top">[[TXT_DIRECTORY_LEVEL]]:<font color="red">*</font></td>
                <td>
                     <table width="100%" border="0">
		     <tr>
		     <td width="210">
		      <select name="notSelectedLevel[]" size="12" style="width:170px;" multiple="multiple">
		          [[DIRECTORY_LEVELS_DESELECTED]]
		      </select>
		    </td>
		    <td width="50">
		      <div align="center">
		        <input type="button" value=" &gt;&gt; " name="addLevel" onclick="add(document.addForm.elements['notSelectedLevel[]'],document.addForm.elements['selectedLevel[]'],addLevel,removeLevel);" />
		        <br />
		        <input type="button" value=" &lt;&lt; " name="removeLevel" onclick="remove(document.addForm.elements['notSelectedLevel[]'],document.addForm.elements['selectedLevel[]'],addLeve,removeLevel);" />
		        <br /><br />
		      </div>
		    </td>
		    <td>
		      <div align="left">
		        <select name="selectedLevel[]" size="12" style="width:170px;" multiple="multiple">
                           [[DIRECTORY_LEVELS_SELECTED]]
		        </select>
		      </div>
		    </td>
		  </tr>
		</table>
                </td>
            </tr>
            <!-- END directoryLevels -->
        </tbody>
    </table>
    <table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
        <tbody>
            <!-- BEGIN inputfieldsOutput -->
            <tr>
                <td width="120" valign="top">[[FIELD_NAME]]:[[FIELD_REQUIRED]]</td>
                <td>[[FIELD_VALUE]]</td>
            </tr>
            <!-- END inputfieldsOutput -->
            <tr>
                <td width="120" valign="top"><br /></td>
                <td><input type="submit" name="edit_submit" value="[[TXT_DIRECTORY_UPDATE]]" /></td>
            </tr>
        </tbody>
    </table>
</form>
<!-- END directoryInputFields -->
[/HTML]

section=directory&cmd=add
[HTML]
<script language="JavaScript">
<!--
function add(from,dest,add,remove)
{
    if ( from.selectedIndex < 0)
	{
		if (from.options[0] != null)
			from.options[0].selected = true;
		from.focus();
		return false;
	}
	else
	{
		for (var i=0; i<from.length; i++)
		{
			if (from.options[i].selected)
			{
		    	dest.options[dest.length] = new Option( from.options[i].text, from.options[i].value, false, false);
   			}
		}
	    for (var i=from.length-1; i>=0; i--)
		{
			if (from.options[i].selected)
			{
		       from.options[i] = null;
   			}
		}
	}
    disableButtons(from,dest,add,remove);
}


function remove(from,dest,add,remove)
{
	if ( dest.selectedIndex < 0)
	{
		if (dest.options[0] != null)
			dest.options[0].selected = true;
		dest.focus();
		return false;
	}
	else
	{
		for (var i=0; i<dest.options.length; i++)
		{
			if (dest.options[i].selected)
			{
		    	from.options[from.options.length] = new Option( dest.options[i].text, dest.options[i].value, false, false);
   			}
		}
	    for (var i=dest.options.length-1; i>=0; i--)
		{
			if (dest.options[i].selected)
			{
		       dest.options[i] = null;
   			}
		}
	}
	disableButtons(from,dest,add,remove);
}

function disableButtons(from,dest,add,remove)
{
	if (from.options.length > 0 )
	{
		add.disabled = 0;
	}
	else
		add.disabled = 1;

	if (dest.options.length > 0)
		remove.disabled = 0;
	else
		remove.disabled = 1;
}

function selectAll(CONTROL)
{
	for(var i = 0;i < CONTROL.length;i++)
    {
		CONTROL.options[i].selected = true;
	}
}

function deselectAll(CONTROL)
{
	for(var i = 0;i < CONTROL.length;i++)
	{
		CONTROL.options[i].selected = false;
	}
}


[[DIRECTORY_JAVASCRIPT]]

-->
</script>
[[DIRECTORY_SEARCH]]
<div id="directoryNavtree">
<a href="?section=directory">[[TXT_DIRECTORY_DIR]]</a>[[DIRECTORY_CATEGORY_NAVI]]
</div>
<!-- BEGIN directoryMessage -->
<table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
    <tbody>
        <tr>
            <td width="100%" valign="top">[[DIRECTORY_FEED_ADDED]]<br /><br /></td>
        </tr>
        <tr>
            <td width="100%" valign="top">[[TXT_DIRECTORY_BACK]]</td>
        </tr>
    </tbody>
</table>
<!-- END directoryMessage -->
<!-- BEGIN directoryInputFields -->
<form name="addForm" enctype="multipart/form-data" method="post" action="index.php?section=directory&amp;cmd=add" onsubmit="[[DIRECTORY_FORM_ONSUBMIT]]">
    <table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
        <tbody>
            <tr>
                <td width="120" valign="top">[[TXT_DIRECTORY_CATEGORY]]:<font color="red">*</font></td>
                <td>
                     <table width="100%" border="0">
		     <tr>
		     <td width="210">
		      <select name="notSelectedCat[]" size="12" style="width:170px;" multiple="multiple">
		          [[DIRECTORY_CATEGORIES_DESELECTED]]
		      </select>
		    </td>
		    <td width="50">
		      <div align="center">
		        <input type="button" value=" &gt;&gt; " name="addCat" onclick="add(document.addForm.elements['notSelectedCat[]'],document.addForm.elements['selectedCat[]'],addCat,removeCat);" />
		        <br />
		        <input type="button" value=" &lt;&lt; " name="removeCat" onclick="remove(document.addForm.elements['notSelectedCat[]'],document.addForm.elements['selectedCat[]'],addCat,removeCat);" />
		        <br /><br />
		      </div>
		    </td>
		    <td>
		      <div align="left">
		        <select name="selectedCat[]" size="12" style="width:170px;" multiple="multiple">
		        </select>
		      </div>
		    </td>
		  </tr>
		</table>
                </td>
            </tr>
            <!-- BEGIN directoryLevels -->
            <tr>
                <td width="120" valign="top">[[TXT_DIRECTORY_LEVEL]]:<font color="red">*</font></td>
                <td>
                     <table width="100%" border="0">
		     <tr>
		     <td width="210">
		      <select name="notSelectedLevel[]" size="12" style="width:170px;" multiple="multiple">
		          [[DIRECTORY_LEVELS_DESELECTED]]
		      </select>
		    </td>
		    <td width="50">
		      <div align="center">
		        <input type="button" value=" &gt;&gt; " name="addLevel" onclick="add(document.addForm.elements['notSelectedLevel[]'],document.addForm.elements['selectedLevel[]'],addLevel,removeLevel);" />
		        <br />
		        <input type="button" value=" &lt;&lt; " name="removeLevel" onclick="remove(document.addForm.elements['notSelectedLevel[]'],document.addForm.elements['selectedLevel[]'],addLeve,removeLevel);" />
		        <br /><br />
		      </div>
		    </td>
		    <td>
		      <div align="left">
		        <select name="selectedLevel[]" size="12" style="width:170px;" multiple="multiple">
                           [[DIRECTORY_LEVELS_SELECTED]]
		        </select>
		      </div>
		    </td>
		  </tr>
		</table>
                </td>
            </tr>
            <!-- END directoryLevels -->
        </tbody>
    </table>
    <table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
        <tbody>
            <!-- BEGIN inputfieldsOutput -->
            <tr>
                <td width="120" valign="top">[[FIELD_NAME]]:[[FIELD_REQUIRED]]</td>
                <td>[[FIELD_VALUE]]</td>
            </tr>
            <!-- END inputfieldsOutput -->
            <tr>
                <td width="120" valign="top"><br /></td>
                <td><input type="submit" name="addSubmit" value="[[TXT_DIRECTORY_ADD]]" /></td>
            </tr>
        </tbody>
    </table>
</form>
<!-- END directoryInputFields -->
[/HTML]