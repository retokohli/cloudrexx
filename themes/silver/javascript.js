// Default statusbar text
window.defaultStatus = "Contrexx® secure Content Management System";

function addFav(){
    if (document.all)
        window.external.AddFavorite("http://www.contrexx.com/", "Contrexx.com - Open Source CMS");
    else if (window.sidebar)
        window.sidebar.addPanel("Contrexx.com - Open Source CMS", "http://www.contrexx.com/", "")
}