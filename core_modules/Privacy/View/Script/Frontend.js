document.addEventListener("DOMContentLoaded", function() {
    var value = "; " + document.cookie;
    var parts = value.split("; ClxCookieNote=");
    if (
        parts.length == 2 &&
        parts.pop().split(";").shift() == "accepted"
    ) {
        document.getElementById("cookie-note").style.display = "none";
    }
    document.getElementById("cookie-note-ok").addEventListener(
        "click",
        function () {
            document.cookie = "ClxCookieNote=accepted;"
            document.getElementById("cookie-note").style.display = "none";
        }
    );
});
