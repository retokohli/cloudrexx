# Use value of ASCMS_PATH_OFFSET with a leading slashe here
RewriteBase  %PATH_ROOT_OFFSET%

# Remove trailing slash for old Apaches (newer ones stop rewriting when result matches request)
#RewriteRule  ^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)\/$ $2 [L,QSA]

# Folders that may be accessed by page requests are NO ALIASES by design
RewriteRule  ^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)(\/|$)(.*) $2$3$4 [L,QSA]

# Resolve language specific sitemap.xml
RewriteRule  ^(\w+)\/sitemap.xml sitemap_$1.xml [L,NC]

# Anything that is neither a directory nor a file *might* be an alias.
# Append the entire request to the query string.
RewriteCond  %{REQUEST_FILENAME}  index.php
RewriteRule  .  index.php?__cap=%{REQUEST_URI} [L,QSA]

RewriteCond  %{REQUEST_FILENAME}  !-d
RewriteCond  %{REQUEST_FILENAME}  !-f
RewriteRule  .  index.php?__cap=%{REQUEST_URI} [L,QSA]