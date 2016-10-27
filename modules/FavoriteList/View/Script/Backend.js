cx.ready(function () {
    var checkboxes = [
        'notificationMail',
        'notificationPrint',
        'notificationRecommendation',
        'notificationInquiry',
    ];
    if (document.getElementById(checkboxes[0]) !== null) {
        // Only show inputfield if the checkbox is selected
        function checkboxDisplay() {
            for (var i = 0; i < checkboxes.length; i++) {
                if (!document.getElementById(checkboxes[i]).checked) {
                    document.getElementById(checkboxes[i]).nextElementSibling.style.display = 'none';
                } else {
                    document.getElementById(checkboxes[i]).nextElementSibling.style.display = 'inline-block';
                }
            }
        }
        checkboxDisplay();
        // Adds an event listener on each checkbox
        for (var i = 0; i < checkboxes.length; i++) {
            document.getElementById(checkboxes[i]).addEventListener('click', checkboxDisplay, false);
        }
    }
});
