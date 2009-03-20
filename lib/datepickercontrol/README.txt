added missing hLayer check (JS error when null)

getObject.getSize:
-   while ((hLayer.tagName) && !( /(body|html)/i.test(hLayer.tagName))){
+	while (hLayer && (hLayer.tagName) /*ID 10 t*/ && !( /(body|html)/i.test(hLayer.tagName))){
 