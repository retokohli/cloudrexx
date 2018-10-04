# Path: modules/CHDIRTravelLog/Doc/settings-add.sh
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem project_name 'GAN16' 1 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem data_folder '/media/Travellog/' 2 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem pdf_folder '/media/Travellog/PDF/' 3 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_delimiter ';' 4 text ''
# TODO: Not added correctly:
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_enclosure '"' 5 text ''
# TODO: Not added correctly:
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem csv_escape \\ 6 text ''
# The sync time may inititally be reset in order to trigger the update
#./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem last_sync_time '1519911128' 7 text ''
./cx Setting add CHDIRTravelLog -group=config -engine=FileSystem last_sync_time '0' 7 text ''
