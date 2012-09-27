<!-- <contrexx> -->
<!--    <core_routing> -->
            <configuration>
                <system.webServer>
                    <rewrite>
                      <rules>
                        <!-- Folders that may be accessed by page requests are NO ALIASES by design -->
                        <rule name="rule_1" stopProcessing="true">
                          <match url="^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)(\/|$)(.*)" ignoreCase="false" />
                          <action type="Rewrite" url="{R:2}{R:3}{R:4}" appendQueryString="true" />
                        </rule>
                        <rule name="rule_2" stopProcessing="true">
                          <match url="." ignoreCase="false" />
                          <conditions>
                            <!-- Anything that is neither a directory nor a file *might* be an alias.-->
                            <!-- Append the entire request to the query string.-->
                            <add input="{REQUEST_FILENAME}" pattern="index.php" ignoreCase="false" />
                          </conditions>
                          <action type="Rewrite" url="index.php?__cap={URL}" appendQueryString="true" />
                        </rule>
                        <rule name="rule_3" stopProcessing="true">
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
<!--    </core_routing> -->
<!-- </contrexx> -->
