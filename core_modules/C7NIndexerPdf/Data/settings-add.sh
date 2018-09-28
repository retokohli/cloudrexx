# Custom pdftotext endpoint
./cx Setting add C7NIndexerPdf -group=config -engine=FileSystem url_pdftotext 'http://pdftotext.comvation-webinterfaces.com.copernicus.sui-inter.net/pdftotext.php' 1 text ''
# Default to empty URL -- Note that this will skip PDF indexing entirely!
#./cx Setting add C7NIndexerPdf -group=config -engine=FileSystem url_pdftotext '' 1 text ''
# Endpoint dummy, for development only
#./cx Setting add C7NIndexerPdf -group=config -engine=FileSystem url_pdftotext 'http://localhost/pdftotext.php' 1 text ''
