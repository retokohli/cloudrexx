<!-- <contrexx> -->
<!--     <core_routing> -->
             <configuration>
                 <system.webServer>
                     <rewrite>
                         <rules>
                             <!-- Use value of ASCMS_PATH_OFFSET with a leading slashe here-->
                             <!-- Folders that may be accessed by page requests are NO ALIASES by design-->
                             <rule name="Importierte Regel 1" stopProcessing="true">
                                 <match url="^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)(\/|$)(.*)" ignoreCase="false" />
                                 <action type="Rewrite" url="{R:2}{R:3}{R:4}" appendQueryString="true" />
                             </rule>
                             <!-- Resolve language specific sitemap.xml-->
                             <rule name="Importierte Regel 2" stopProcessing="true">
                                 <match url="^(\w+)\/sitemap.xml" />
                                 <action type="Rewrite" url="sitemap_{R:1}.xml" />
                             </rule>
                             <rule name="Importierte Regel 3" stopProcessing="true">
                                 <match url="." ignoreCase="false" />
                                 <conditions>
                                     <!-- Anything that is neither a directory nor a file *might* be an alias.-->
                                     <!-- Append the entire request to the query string.-->
                                    <add input="{REQUEST_FILENAME}" pattern="index.php" ignoreCase="false" />
                                 </conditions>
                                 <action type="Rewrite" url="index.php?__cap={URL}" appendQueryString="true" />
                             </rule>
                             <rule name="Importierte Regel 4" stopProcessing="true">
                                 <match url="." ignoreCase="false" />
                                 <conditions>
                                     <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                                     <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                                 </conditions>
                                 <action type="Rewrite" url="index.php?__cap={URL}" appendQueryString="true" />
                             </rule>
                         </rules>
                     </rewrite>
                 </system.webServer>
             </configuration>
<!--     </core_routing> -->
<!-- </contrexx> -->