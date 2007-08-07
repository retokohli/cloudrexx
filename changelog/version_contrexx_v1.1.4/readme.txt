
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
