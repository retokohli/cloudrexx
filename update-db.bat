cd C:\contrexx\c_vbv
ren core\MediaSource\Model\__Yaml Yaml
ren core\__DataSource DataSource
rem workbench.bat db doctrine orm:schema-tool:create --dump-sql > modules/Lexicon/Model/Data/install.sql
workbench.bat db doctrine orm:schema-tool:create --dump-sql
