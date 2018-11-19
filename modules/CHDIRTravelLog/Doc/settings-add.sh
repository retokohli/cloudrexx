# Path: ./modules/CHDIRTravelLog/Doc/settings-add.sh
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem project_name 'GAN16' 1 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem project_names 'GAN16,GAN18' 2 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem data_folder 'media/CHDIRTravelLog/' 3 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem pdf_folder 'media/CHDIRTravelLog/PDF/' 4 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_delimiter ';' 5 text ''
# TODO: Not added correctly: as '\"' (without quotes)
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_enclosure '"' 6 text ''
# TODO: Not added correctly: as '\\' (without quotes)
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_escape \\ 7 text ''
# The sync time is inititally reset in order to trigger the update
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem last_sync_time '0' 8 text ''
