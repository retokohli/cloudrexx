cd C:\contrexx\c_vbv
rem Temporary fix for broken Doctrine inheritance
ren core\MediaSource\Model\Yaml __Yaml
ren core\DataSource __DataSource
rem del /Q model\entities\Cx\Modules\Lexicon\Model\Entity\*
rem del /Q model\repository\Cx\Modules\Lexicon\Model\Repository\*
cmd /c workbench.bat db update module Lexicon
rem alternatively:
rem workbench.bat db doctrine orm:generate-entities model/entities
rem TODO: copy -Y model/entities/
rem workbench.bat db doctrine orm:generate-repositories model/repository
rem Revert before updating the database!
ren core\MediaSource\Model\__Yaml Yaml
ren core\__DataSource DataSource
