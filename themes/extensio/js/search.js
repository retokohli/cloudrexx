function clearText(thefield) {    
    if (thefield.defaultValue == thefield.value)
        thefield.value = ""
}
function resetText(thefield) {
    if ("" == thefield.value)
        thefield.value = thefield.defaultValue
}