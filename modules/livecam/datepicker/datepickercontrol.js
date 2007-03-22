/**
 * DatePickerControl v.0.9
 *
 * Transform your input text control into a date-picker control.
 *
 * By Hugo Ortega_Hernandez - hugorteg{no_spam}@gmail.com
 *
 * Features:
 *   + Automatic input control conversion with a single attribute
 *     in the 'input' tag.
 *   + Multiple date formats.
 *   + Layered calendar, without pop-up window.
 *   + Mouse and keyboard navigation.
 *   + CSS support.
 *
 * License: GPL (that's, use this code as you wish, just keep it free)
 * Provided as is, without any warranty.
 * Fell free to use this code, but don't remove this disclaimer please.
 *
 * If you're going to use this library, please send me an email, and
 * would be great if you include a photo of your city :)
 * (se habla espa&ntilde;ol)
 *
 * Credits:
 *
 * Functions to calculate days of a year and to generate the calendar code by:
 *    Kedar R. Bhave - softricks{no_spam}@hotmail.com
 *    ttp://www.softricks.com
 *    = Modified by Hugo Ortega_H:
 *      + CSS style
 *      + Remove non useful code (original version with pop-up window)
 *      + Add support for layered calendar
 *      + Many other stuff.
 *
 * Other code functions and lines to calculate objects' size & location by:
 *    Mircho Mirev - mo{no_spam}@momche.net
 *
 * TODO:
 *   1) Is a problem to make changes in the design of the calendar.
 *   2) A mask-edit type control
 *   3) Enter key behaviour as space key (to select the current date and avoid
 *      the form submit).
 *   4) At the moment, any idea is good ;)
 *
 *
 *                                        Veracruz & Monterrey, Mexico, 2005.
 */


//-----------------------------------------------------------------------------
// Some parameters for style and behaviour...

Calendar.defaultFormat   = "YYYY-MM-DD";
Calendar.offsetY         = 1;
Calendar.offsetX         = 0;
Calendar.todayText       = "Heute";
Calendar.closeOnTodayBtn = true; // close if today button is pressed
Calendar.buttonTitle     = "Webcam Kalender";
Calendar.buttonPosition  = "in"; // or "out"
Calendar.buttonOffsetX   = 0; // See below for some considerations about that values (for IE)
Calendar.buttonOffsetY   = 0;

Calendar.Months =
//	["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
//	["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
//	["Janvier", "F&eacute;vrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Ao&ucirc;t", "Septembre", "Octobre", "Novembre", "D&eacute;cembre"];
	["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"];

Calendar.Days =
//	["Dom", "Lun", "Mar", "Mi&eacute;", "Jue", "Vie", "S&aacute;b"];
//	["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
//	["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"];
	["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam"];


//-----------------------------------------------------------------------------
// Specific patches

Calendar.useTrickyBG = false;
// Some people ask me for IE strange behaviour... well, here's the patch
// IE returns object position with one pixel more
// <sarcasm>Patches for IE?, I can believe!</sarcasm>
if (navigator.userAgent.indexOf("MSIE") > 1){
	Calendar.useTrickyBG   = true;
	Calendar.offsetY       = 0;
	Calendar.offsetX       = -1;
	Calendar.buttonOffsetX = -1;
	Calendar.buttonOffsetY = -1;
	// but if document is xhtml dtd, things are differents... :S
	if (document.getElementsByTagName("html")[0].getAttribute("xmlns") != null){
		Calendar.offsetY       = 16;
		Calendar.offsetX       = 10;
		Calendar.buttonOffsetX = 8;
		Calendar.buttonOffsetY = 14;
	}
}


//-----------------------------------------------------------------------------
// Some constants and internal stuff

Calendar.editIdPrefix   = "DPC_";
Calendar.displayed      = false;
Calendar.HIDE_TIMEOUT   = 200;
Calendar.hideTimeout    = null;
Calendar.buttonIdPrefix = "CALBUTTON";
Calendar.dayIdPrefix    = "CALDAY";
Calendar.currentDay     = 1;
Calendar.originalValue  = "";
Calendar.calFrameId     = "calendarframe";
Calendar.submitByKey    = false;
Calendar.dayOfWeek      = 0;
// Non-Leap year month days..
Calendar.DOMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
// Leap year month days..
Calendar.lDOMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
// sunday and saturday as weekend
Calendar.weekend = [0,6];


//-----------------------------------------------------------------------------
// The fun

/**
 * Constructor
 */
function Calendar()
{
}

/**
 * Creates the calendar's div element and the button into the input-texts with
 * attibute datepicker="true".
 */
Calendar.autoInit = function()
{
	if (!document.getElementById("CalendarPickerControl")){
		Calendar.calBG = null;
		if (Calendar.useTrickyBG){
			// Creates a tricky bg to hide the select controls (IE bug).
			// We use a iframe element, because is one of the elements that can
			// stay on top of select controls.
			Calendar.calBG                = document.createElement("iframe");
			Calendar.calBG.id             = "CalendarPickerControlBG";
			Calendar.calBG.style.zIndex   = "49999"; // below calcontainer
			Calendar.calBG.style.position = "absolute";
			Calendar.calBG.style.display  = "none";
			Calendar.calBG.style.border   = "0px solid transparent";
			document.body.appendChild(Calendar.calBG);
		}
		Calendar.calContainer                = document.createElement("div");
		Calendar.calContainer.id             = "CalendarPickerControl";
		Calendar.calContainer.style.zIndex   = "50000";
		Calendar.calContainer.style.position = "absolute";
		Calendar.calContainer.style.display  = "none";
		document.body.appendChild(Calendar.calContainer);

		if (Calendar.calContainer.attachEvent){
			Calendar.calContainer.attachEvent("onclick", Calendar.onContainerClick);
		}
		else if (Calendar.calContainer.addEventListener){
			Calendar.calContainer.addEventListener("click", Calendar.onContainerClick, false);
		}
	}
	// search for input controls that will be transformed into datepickercontrols
	var inputsLength = document.getElementsByTagName("INPUT").length;
	for (i = 0; i<inputsLength; i++){
		if (document.getElementsByTagName("INPUT")[i].type.toLowerCase() == "text"){
			var editctrl = document.getElementsByTagName("INPUT")[i];
			var sLangAtt = editctrl.getAttribute("datepicker");
			var setEvents = false;
			// if datepicker pseudo-attribute:
			if (sLangAtt != null && sLangAtt == "true"){
				if (editctrl.id){
					if (!Calendar.prototype.createButton(editctrl, false)) continue;
					setEvents = true;
				}
				else{
					alert("Attribute 'id' is mandatory for DatePickerControl.");
				}
			}
			// if fomatted id:
			else if (editctrl.id && editctrl.id.indexOf(Calendar.editIdPrefix) == 0){
				if (!Calendar.prototype.createButton(editctrl, true)) continue;
				setEvents = true;
			}
			// add the events:
			if (setEvents){
				editctrl.setAttribute("maxlength", "10");
				if (editctrl.attachEvent){
					editctrl.attachEvent("onkeyup", Calendar.onEditControlKeyUp);
					editctrl.attachEvent("onkeydown", Calendar.onEditControlKeyDown);
					editctrl.attachEvent("onblur", Calendar.onEditControlBlur);
					editctrl.attachEvent("onfocus", Calendar.onEditControlFocus);
				}
				else if(editctrl.addEventListener){
					editctrl.addEventListener("keyup", Calendar.onEditControlKeyUp, false);
					editctrl.addEventListener("keydown", Calendar.onEditControlKeyDown, false);
					editctrl.addEventListener("blur", Calendar.onEditControlBlur, false);
					editctrl.addEventListener("focus", Calendar.onEditControlFocus, false);
				}
				var theForm = editctrl.form;
				if (theForm){
					if (theForm.attachEvent){
						theForm.attachEvent('onsubmit', Calendar.onFormSubmit);
					}
					else if (theForm.addEventListener){
						theForm.addEventListener('submit', Calendar.onFormSubmit, false);
					}
				}
			}
		}
	}
}

if (window.attachEvent){
	window.attachEvent("onload", Calendar.autoInit);
}
else if (window.addEventListener){
	window.addEventListener("load", Calendar.autoInit, false);
}


/**
 * Creates the calendar button for an text-input control
 * @param input The associated text-input to create the button.
 * @param useId Specify if you want to use the Id of input control to obtain the format
 * @return true is the control has been created, otherwise false
 */
Calendar.prototype.createButton = function(input, useId)
{
	var newid = Calendar.buttonIdPrefix + input.id;
	if (document.getElementById(newid)) return false; // if exists previously....
	// the format
	var fmt = "";
	if (useId){ // get the format from the control's id
		var arr = input.id.split("_");
		var last = arr[arr.length-1];
		// a not so beauty validation :S
		if ((last.indexOf("-")>0 || last.indexOf("/")>0) &&
			last.indexOf("YY") >= 0 && last.indexOf("D") >= 0 &&
			last.indexOf("M") >= 0){ // is a format
				fmt = last;
		}
		else{
			fmt = Calendar.defaultFormat;
		}
	}
	else{ // get the format from pseudo-attibute
		fmt = input.getAttribute("datepicker_format");
		if (fmt == null) fmt = Calendar.defaultFormat;
	}
	// Position
	var nTop = getObject.getSize("offsetTop", input);
	var nLeft = getObject.getSize("offsetLeft", input);
	// Create the button
	var calButton = document.createElement("div");
	calButton.id = newid;
	calButton.title = Calendar.buttonTitle;
	// Set some attributes to remember the text-input associated
	// with this button and its format:
	calButton.setAttribute("datepicker_inputid", input.id);
	calButton.setAttribute("datepicker_format", fmt);
	// Add the event listeners:
	if (calButton.attachEvent){
		calButton.attachEvent("onclick", Calendar.onButtonClick);
	}
	else if(calButton.addEventListener){
		calButton.addEventListener("click", Calendar.onButtonClick, false);
	}
	// add first to have access to the size properties
	document.body.appendChild(calButton);
	// Set the style and position:
	calButton.className    = "calendarbutton";
	calButton.style.zIndex = 10000;
	calButton.style.cursor = "pointer";
	calButton.style.top    = (nTop + Math.floor((input.offsetHeight-calButton.offsetHeight)/2) + Calendar.buttonOffsetY) + "px";
	btnOffX                = Math.floor((input.offsetHeight - calButton.offsetHeight) / 2);
	if (Calendar.buttonPosition == "in"){
		calButton.style.left = (nLeft + input.offsetWidth - calButton.offsetWidth - btnOffX + Calendar.buttonOffsetX) + "px";
	}
	else{ // "out"
		calButton.style.left = (nLeft + input.offsetWidth + btnOffX + Calendar.buttonOffsetX) + "px";
	}
	// everything is ok
	return true;
}

/**
 * Show the list
 */
Calendar.show = function()
{
	if (!Calendar.displayed){
		var input = Calendar.inputControl;
		if (input == null) return;
		var top  = getObject.getSize("offsetTop", input);
		var left = getObject.getSize("offsetLeft", input);

		Calendar.calContainer.style.top        = top + input.offsetHeight + Calendar.offsetY + "px";
		Calendar.calContainer.style.left       = left + Calendar.offsetX + "px";
		Calendar.calContainer.style.display    = "none";
		Calendar.calContainer.style.visibility = "visible";
		Calendar.calContainer.style.display    = "block";
		if (Calendar.calBG){ // the ugly patch for IE
			Calendar.calBG.style.top        = Calendar.calContainer.style.top;
			Calendar.calBG.style.left       = Calendar.calContainer.style.left;
			Calendar.calBG.style.display    = "none";
			Calendar.calBG.style.visibility = "visible";
			Calendar.calBG.style.display    = "block";
			Calendar.calBG.style.width      = Calendar.calContainer.offsetWidth;
			calframe = document.getElementById(Calendar.calFrameId);
			if (calframe){
				Calendar.calBG.style.height = calframe.offsetHeight;
			}
		}
		Calendar.displayed = true;
		input.focus();
	}
}


/**
 * Hide the list
 */
Calendar.hide = function()
{
	if (Calendar.displayed){
		Calendar.calContainer.style.visibility = "hidden";
		Calendar.calContainer.style.left = -1000; // some problems with overlaped controls
		Calendar.calContainer.style.top = -1000;
		if (Calendar.calBG){ // the ugly patch for IE
			Calendar.calBG.style.visibility = "hidden";
			Calendar.calBG.style.left = -1000;
			Calendar.calBG.style.top = -1000;
		}
		Calendar.inputControl.value = Calendar.originalValue;
		Calendar.displayed = false;
	}
}


/**
 * Gets the name of a numbered month
 */
Calendar.prototype.getMonthName = function(monthNumber)
{
	return Calendar.Months[monthNumber];
}

/**
 * Obtains the days of a given month and year
 */
Calendar.prototype.getDaysOfMonth = function(monthNo, p_year)
{
	/*
	Check for leap year ..
	1.Years evenly divisible by four are normally leap years, except for...
	2.Years also evenly divisible by 100 are not leap years, except for...
	3.Years also evenly divisible by 400 are leap years.
	*/
	if ((p_year % 4) == 0){
		if ((p_year % 100) == 0 && (p_year % 400) != 0){
			return Calendar.DOMonth[monthNo];
		}
		return Calendar.lDOMonth[monthNo];
	}
	else{
		return Calendar.DOMonth[monthNo];
	}
}

Calendar.calcMonthYear = function(p_Month, p_Year, incr)
{
	/*
	Will return an 1-D array with 1st element being the calculated month
	and second being the calculated year
	after applying the month increment/decrement as specified by 'incr' parameter.
	'incr' will normally have 1/-1 to navigate thru the months.
	*/
	var ret_arr = new Array();

	if (incr == -1) {
		// B A C K W A R D
		if (p_Month == 0) {
			ret_arr[0] = 11;
			ret_arr[1] = parseInt(p_Year) - 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) - 1;
			ret_arr[1] = parseInt(p_Year);
		}
	} else if (incr == 1) {
		// F O R W A R D
		if (p_Month == 11) {
			ret_arr[0] = 0;
			ret_arr[1] = parseInt(p_Year) + 1;
		}
		else {
			ret_arr[0] = parseInt(p_Month) + 1;
			ret_arr[1] = parseInt(p_Year);
		}
	}

	return ret_arr;
}

/**
 * Gets the calendar code
 */
Calendar.prototype.getAllCode = function()
{
	var vCode = "";
	var vHeader_Code = "";
	var vData_Code = "";

	vCode += "<table class='calframe' id='" + Calendar.calFrameId + "'>";

	vCode += this.getHeaderCode();
	vCode += this.getDaysHeaderCode();
	vCode += this.getDaysCode();

	vCode += "</table>";

	return vCode;
}

/**
 * The title and nav buttons
 */
Calendar.prototype.getHeaderCode = function()
{
	// Show navigation buttons

	var prevMMYYYY = Calendar.calcMonthYear(Calendar.month, Calendar.year, -1);
	var prevMM = prevMMYYYY[0];
	var prevYYYY = prevMMYYYY[1];

	var nextMMYYYY = Calendar.calcMonthYear(Calendar.month, Calendar.year, 1);
	var nextMM = nextMMYYYY[0];
	var nextYYYY = nextMMYYYY[1];

	var gNow = new Date();
	var vCode = "";

	vCode += "<tr><td colspan='7' class='monthname'>";
	vCode += Calendar.monthName + "&nbsp;&nbsp;";

	vCode += "<span title='" + Calendar.Months[Calendar.month] + " " + (parseInt(Calendar.year)-1) + "' class='yearbutton' ";
	vCode += "onclick='Calendar.build(" + Calendar.month + ", " + (parseInt(Calendar.year)-1)+");return false;'>&laquo;</span>";
	vCode += "&nbsp;" + Calendar.year + "&nbsp;";

	vCode += "<span title='"+Calendar.Months[Calendar.month] + " " + (parseInt(Calendar.year)+1) + "' class='yearbutton' ";
	vCode += "onclick='Calendar.build(" + Calendar.month + ", " + (parseInt(Calendar.year)+1) + ");return false;'>&raquo;</span>";
	vCode += "</td></tr>";

	vCode += "<tr><td colspan='7'>";
	vCode += "<table class='navigation' width='100%'><tr>";

	vCode += "<td class='navbutton' title='" + Calendar.Months[prevMM] + " " + prevYYYY + "'";
	vCode += "onclick='Calendar.build(" + prevMM + ", " + prevYYYY + ");return false;'>&lt;&lt;</td>";

	vCode += "<td class='navbutton' title='" + gNow.getDate() + " " + Calendar.Months[gNow.getMonth()] + " " + gNow.getFullYear() + "'";
	vCode += "onclick='Calendar.build(" + gNow.getMonth() + ", " + gNow.getFullYear() + ");Calendar.selectToday();return false;'>";
	vCode += Calendar.todayText + "</td>";

	vCode += "<td class='navbutton' title='" + Calendar.Months[nextMM] + " " + nextYYYY + "'";
	vCode += "onclick='Calendar.build(" + nextMM + ", " + nextYYYY +	");return false;'>&gt;&gt;</td>";

	vCode += "</tr></table>";
	vCode += "</td></tr>";

	return vCode;
}

/**
 * The days' name headers
 */
Calendar.prototype.getDaysHeaderCode = function()
{
	var vCode = "";

	vCode = vCode + "<tr>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[0]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[1]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[2]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[3]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[4]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[5]+"</td>";
	vCode = vCode + "<td class='dayname' width='14%'>"+Calendar.Days[6]+"</td>";
	vCode = vCode + "</tr>";

	return vCode;
}

/**
 * The days numbers code
 */
Calendar.prototype.getDaysCode = function()
{
	var vDate = new Date();
	vDate.setDate(1);
	vDate.setMonth(Calendar.month);
	vDate.setFullYear(Calendar.year);

	var vFirstDay = vDate.getDay();
	var vDay = 1;
	var vLastDay = this.getDaysOfMonth(Calendar.month, Calendar.year);
	var vOnLastDay = 0;
	var vCode = "";
	Calendar.dayOfWeek = vFirstDay;

	/*
	Get day for the 1st of the requested month/year..
	Place as many blank cells before the 1st day of the month as necessary.
	*/
	var prevm = Calendar.month == 0 ? 11 : Calendar.month-1;
	var prevy = this.prevm == 11 ? Calendar.year - 1 : Calendar.year;
	prevmontdays = this.getDaysOfMonth(prevm, prevy);
	vCode = vCode + "<tr>";
	for (i=0; i<vFirstDay; i++) {
		vCode = vCode + "<td class='dayothermonth'>" + (prevmontdays-vFirstDay+i+1) + "</td>";
	}

	// Write rest of the 1st week
	for (j=vFirstDay; j<7; j++) {
		classname = this.getDayClass(vDay, j);
		vCode = vCode + "<td class='" + classname + "' class_orig='" + classname + "' " +
			"onClick='Calendar.writeDate(" + vDay + ")' id='" + Calendar.dayIdPrefix + vDay + "'>" +
			vDay +
			"</td>";
		vDay = vDay + 1;
	}
	vCode = vCode + "</tr>";

	// Write the rest of the weeks
	for (k=2; k<7; k++){
		vCode = vCode + "<TR>";
		for (j=0; j<7; j++){
			classname = this.getDayClass(vDay, j);
			vCode = vCode + "<td class='" + classname  + "' class_orig='" +  classname + "' " +
				"onClick='Calendar.writeDate(" + vDay + ")' id='" + Calendar.dayIdPrefix + vDay + "'>" +
				vDay +
				"</td>";
			vDay = vDay + 1;
			if (vDay > vLastDay){
				vOnLastDay = 1;
				break;
			}
		}

		if (j == 6)
			vCode = vCode + "</tr>";
		if (vOnLastDay == 1)
			break;
	}

	// Fill up the rest of last week with proper blanks, so that we get proper square blocks
	for (m=1; m<(7-j); m++){
		if (Calendar.yearly)
			vCode = vCode + "<td class='dayothermonth'></td>";
		else
			vCode = vCode + "<td class='dayothermonth'>" + m + "</td>";
	}

	return vCode;
}

/**
 * Get the class according if is 'today', the 'current' date at the control,
 * a 'weekend' day, or a 'normal' day.
 * @param vday The number of the day in the current month and year
 * @param dayofweek The number of the day within the week (1..7)
 */
Calendar.prototype.getDayClass = function(vday, dayofweek)
{
	var gNow      = new Date();
	var vNowDay   = gNow.getDate();
	var vNowMonth = gNow.getMonth();
	var vNowYear  = gNow.getFullYear();

	/*if (Calendar.currentDate && vday == Calendar.currentDate.getDate() && Calendar.month == Calendar.currentDate.getMonth() && Calendar.year == Calendar.currentDate.getFullYear()){
		return "current";
	}
	else*/
	if (vday == vNowDay && Calendar.month == vNowMonth && Calendar.year == vNowYear){
		return "today";
	}
	else{
		for (i=0; i<Calendar.weekend.length; i++) {
			if (dayofweek == Calendar.weekend[i]){
				return "weekend";
			}
		}
		return "day";
	}
}

/**
 * Gets the date string according to Calendar.format
 * The number of the day in the current month and year
 */
Calendar.prototype.formatData = function(p_day)
{
	var vData;
	var vMonth = 1 + Calendar.month;
	vMonth = (vMonth.toString().length < 2) ? "0" + vMonth : vMonth;
	var vMon = this.getMonthName(Calendar.month).substr(0,3).toUpperCase();
	var vFMon = this.getMonthName(Calendar.month).toUpperCase();
	var vY4 = new String(Calendar.year);
	var vY2 = new String(Calendar.year).substr(2,2);
	var vDD = (p_day.toString().length < 2) ? "0" + p_day : p_day;

	switch (Calendar.format) {
		case "MM/DD/YYYY" :
			vData = vMonth + "/" + vDD + "/" + vY4;
			break;
		case "MM/DD/YY" :
			vData = vMonth + "/" + vDD + "/" + vY2;
			break;
		case "MM-DD-YYYY" :
			vData = vMonth + "-" + vDD + "-" + vY4;
			break;
		case "MM-DD-YY" :
			vData = vMonth + "-" + vDD + "-" + vY2;
			break;
		case "YYYY-MM-DD":
			vData = vY4 + "-" + vMonth + "-" + vDD;
			break;
		case "YYYY/MM/DD":
			vData = vY4 + "/" + vMonth + "/" + vDD;
			break;

		case "DD/MON/YYYY" :
			vData = vDD + "/" + vMon + "/" + vY4;
			break;
		case "DD/MON/YY" :
			vData = vDD + "/" + vMon + "/" + vY2;
			break;
		case "DD-MON-YYYY" :
			vData = vDD + "-" + vMon + "-" + vY4;
			break;
		case "DD-MON-YY" :
			vData = vDD + "-" + vMon + "-" + vY2;
			break;

		case "DD/MONTH/YYYY" :
			vData = vDD + "/" + vFMon + "/" + vY4;
			break;
		case "DD/MONTH/YY" :
			vData = vDD + "/" + vFMon + "/" + vY2;
			break;
		case "DD-MONTH-YYYY" :
			vData = vDD + "-" + vFMon + "-" + vY4;
			break;
		case "DD-MONTH-YY" :
			vData = vDD + "-" + vFMon + "-" + vY2;
			break;

		case "DD/MM/YYYY" :
			vData = vDD + "/" + vMonth + "/" + vY4;
			break;
		case "DD/MM/YY" :
			vData = vDD + "/" + vMonth + "/" + vY2;
			break;
		case "DD-MM-YYYY" :
			vData = vDD + "-" + vMonth + "-" + vY4;
			break;
		case "DD-MM-YY" :
			vData = vDD + "-" + vMonth + "-" + vY2;
			break;

		default :
			vData = vMonth + "/" + vDD + "/" + vY4;
	}

	return vData;
}

/**
 * Writes the specified date in the control and close the calendar.
 */
Calendar.writeDate = function(day)
{
	var d = Calendar.prototype.formatData(day);
	Calendar.inputControl.value = d;
	Calendar.originalValue = d;
	Calendar.hide();
	Calendar.inputControl.focus();
}

/**
 * Writes the current date in the control
 */
Calendar.writeCurrentDate = function()
{
	var d = Calendar.prototype.formatData(Calendar.currentDay);
	Calendar.inputControl.value = d;
}


/**
 * Creates and write the calendar code
 * @param m The month to build
 * @param y The year to build
 */
Calendar.build = function(m, y)
{
	if (m==null){
		var now = new Date();
		Calendar.month     = now.getMonth();
		Calendar.monthName = Calendar.Months[Calendar.month];
		Calendar.year      = now.getFullYear();
	}
	else{
		Calendar.month     = m;
		Calendar.year      = y;
		Calendar.monthName = Calendar.Months[Calendar.month];
	}
	var code = Calendar.prototype.getAllCode();
	writeLayer(Calendar.calContainer.id, null, code);
	Calendar.inputControl.focus();
	Calendar.selectDay(Calendar.currentDay);

	if (Calendar.calBG){ // the ugly patch for IE
		// adjust the height according to the container table
		calframe = document.getElementById(Calendar.calFrameId);
		if (calframe){
			Calendar.calBG.style.height = calframe.offsetHeight;
		}
	}
}

/**
 * Build the prev month calendar
 */
Calendar.buildPrev = function()
{
	if (!Calendar.displayed) return;
	var prevMMYYYY = Calendar.calcMonthYear(Calendar.month, Calendar.year, -1);
	var prevMM = prevMMYYYY[0];
	var prevYYYY = prevMMYYYY[1];
	Calendar.build(prevMM, prevYYYY);
}

/**
 * Build the next month calendar
 */
Calendar.buildNext = function()
{
	if (!Calendar.displayed) return;
	var nextMMYYYY = Calendar.calcMonthYear(Calendar.month, Calendar.year, 1);
	var nextMM = nextMMYYYY[0];
	var nextYYYY = nextMMYYYY[1];
	Calendar.build(nextMM, nextYYYY);
}


/**
 * Today button action
 */
Calendar.selectToday = function()
{
	var now = new Date();
	var today = now.getDate();
	if (Calendar.closeOnTodayBtn){
		Calendar.currentDay = today;
		Calendar.writeDate(Calendar.currentDay);
	}
	else{
		Calendar.selectDay(today);
	}
}

/**
 * Select a specific day
 */
Calendar.selectDay = function(day)
{
	if (!Calendar.displayed) return;
	var n = Calendar.currentDay;
	var max = Calendar.prototype.getDaysOfMonth(Calendar.month, Calendar.year);
	if (day > max) return;
	var newDayObject = document.getElementById(Calendar.dayIdPrefix+day);
	var currentDayObject = document.getElementById(Calendar.dayIdPrefix+Calendar.currentDay);
	if (currentDayObject){
		currentDayObject.className = currentDayObject.getAttribute("class_orig");
	}
	if (newDayObject){
		newDayObject.className = "current";
		Calendar.currentDay = day;
		Calendar.writeCurrentDate();
	}
}

/**
 * Select the prev week day
 * @param decr Use 1 for yesterday or 7 for prev week
 */
Calendar.selectPrevDay = function(decr)
{
	if (!Calendar.displayed) return;
	var n = Calendar.currentDay;
	var max = Calendar.prototype.getDaysOfMonth(Calendar.month, Calendar.year);
	var prev = n - decr;
	if ( prev <= 0 ){
		if (decr == 7){
			n = (n + Calendar.dayOfWeek) + 28 - Calendar.dayOfWeek;
			n--;
			prev = n > max ? n-7 : n;
		}
		else{
			prev = max;
		}
	}
	Calendar.selectDay(prev);
}

/**
 * Select the next week day
 * @param decr Use 1 for tomorrow or 7 for next week
 */
Calendar.selectNextDay = function(incr)
{
	if (!Calendar.displayed) return;
	var n = Calendar.currentDay;
	var max = Calendar.prototype.getDaysOfMonth(Calendar.month, Calendar.year);
	var next = n + incr;
	if ( next > max ){
		if (incr == 7){
			n = ((n + Calendar.dayOfWeek) % 7) - Calendar.dayOfWeek;
			next = n < 0 ? n+7 : n;
			next++;
		}
		else{
			next = 1;
		}
	}
	Calendar.selectDay(next);
}

/**
 * Show the calendar for an edit control
 */
Calendar.showEdit = function(edit)
{
	if (Calendar.displayed) return;
	if (edit == null) return;
	var idbtn = Calendar.buttonIdPrefix + edit.id;
	var button = document.getElementById(idbtn);
	Calendar.inputControl = edit;
	Calendar.originalValue = edit.value;
	// and the format
	var format = button.getAttribute("datepicker_format");
	if (format == null) format = Calendar.defaultFormat;
	Calendar.format = format;
	// build with the date
	if (edit.value == ""){
		Calendar.currentDate = null;
		Calendar.build(null, null);
		Calendar.currentDay  = 1;
	}
	else{
		var date = Calendar.prototype.getDateFromControl();
		Calendar.currentDate = date;
		Calendar.build(date.getMonth(), date.getFullYear());
		Calendar.currentDay  = date.getDate();
	}
	var currentDayObject = document.getElementById(Calendar.dayIdPrefix+Calendar.currentDay);
	if (currentDayObject) currentDayObject.className = "current";
	Calendar.writeCurrentDate();
	// and show
	Calendar.show();
}


/**
 * Click event for calendar button
 */
Calendar.onButtonClick = function(event)
{
	if (!Calendar.displayed){
		// get the button
		if (event == null) event = window.event;
		var button = (event.srcElement) ? event.srcElement : event.originalTarget;
		// gets the associated input:
		var input = document.getElementById(button.getAttribute("datepicker_inputid"));
		Calendar.showEdit(input);
	}
	else{
		Calendar.hide();
	}
}

/**
 * Click event for calendar layer.
 */
Calendar.onContainerClick = function(event)
{
	if (event == null) event = window.event;
	if (Calendar.hideTimeout){
		clearTimeout(Calendar.hideTimeout);
		Calendar.hideTimeout = null;
	}
	Calendar.inputControl.focus();
	return false;
}

/**
 * Key-up event for edit controls as date-pickers
 */
Calendar.onEditControlKeyUp = function(event)
{
	if (event == null) event = window.event;
	var edit = (event.srcElement) ? event.srcElement : event.originalTarget;
	//alert(event.keyCode);
	switch (event.keyCode){
		case 37: // left arrow key
			Calendar.selectPrevDay(1);
			break;

		case 38: // up arrow key
			Calendar.selectPrevDay(7);
			break;

		case 39: // right arrow key
			Calendar.selectNextDay(1);
			break;

		case 40: // down arrow key
			if (!Calendar.displayed){
				Calendar.showEdit(edit);
			}
			else{
				Calendar.selectNextDay(7);
				break;
			}
			break;

		case 27: // escape key
			Calendar.hide();
			break;

		case 33: // repag key
			Calendar.buildPrev();
			break;

		case 34: // avpag key
			Calendar.buildNext();
			break;

		case 13: // enter-key (forms without submit buttons)
			if (Calendar.displayed && Calendar.currentDay > 0 && Calendar.submitByKey){
				Calendar.writeDate(Calendar.currentDay);
			}
			break;
	}
	return false;
}

/**
 * Key-down event for edit controls as date-pickers
 */
Calendar.onEditControlKeyDown = function(event)
{
	if (event == null) event = window.event;
	var edit = (event.srcElement) ? event.srcElement : event.originalTarget;
	//alert(event.keyCode);
	switch (event.keyCode){
		case 13: // enter key
			Calendar.submitByKey = true;
			break;
		case 9:  // tab key
		case 32: // space-bar key
			if (Calendar.displayed && Calendar.currentDay > 0){
				Calendar.writeDate(Calendar.currentDay);
			}
			break;
	}
}

/**
 * blur event for edit controls as date-pickers
 */
Calendar.onEditControlBlur = function(event)
{
	if (event == null) event = window.event;
	if (!Calendar.hideTimeout){
		Calendar.hideTimeout = setTimeout("Calendar.hide()", Calendar.HIDE_TIMEOUT);
	}
}

/**
 * focus event for edit controls as date-pickers
 */
Calendar.onEditControlFocus = function(event)
{
	if (event == null) event = window.event;
	var edit = (event.srcElement) ? event.srcElement : event.originalTarget;
	if (Calendar.inputControl && Calendar.inputControl.id != edit.id){
		Calendar.hide();
	}
	else if (Calendar.hideTimeout){
		clearTimeout(Calendar.hideTimeout);
		Calendar.hideTimeout = null;
	}
}

/**
 * Form's onSubmit event
 */
Calendar.onFormSubmit = function(event)
{
	if (Calendar.submitByKey){
		Calendar.submitByKey = false;
		if (Calendar.displayed && Calendar.currentDay > 0){
			Calendar.writeDate(Calendar.currentDay);
			if (event == null) event = window.event;
			var theForm = (event.srcElement) ? event.srcElement : event.originalTarget;
			event.returnValue = false
			if (event.preventDefault){
				event.preventDefault();
			}
			return false;
		}
	}
}

/**
 * Try to get the date from the control according to the format:
 * This function doesn't work with named months
 * @return An object of class Date with the current date in the control (if succesfull) or
 *  today if fails.
 */
Calendar.prototype.getDateFromControl = function()
{
	var aDate = new Date();
	var value = Calendar.inputControl.value;
	var day, month, year;
	value = value.replace("/", "@").replace("/", "@");
	value = value.replace("-", "@").replace("-", "@");
	// si persisten los caracteres el formato es invalido:
	if (value.indexOf("/")>=0 || value.indexOf("-")>=0) return aDate;
	// validate all other stuff
	var data = value.split("@");
	if (data.length != 3) return aDate;
	for (i=0; i<3; i++){
		if (isNaN(parseInt(data[i]))) return aDate;
	}
	// formatos que comienzan con D,
	if (Calendar.format.substring(0,1).toUpperCase() == "D"){
		aDate.setDate(data[0]);
		aDate.setMonth(data[1]-1);
		aDate.setFullYear(data[2]);
	}
	else if (Calendar.format.substring(0,1).toUpperCase() == "Y"){
		aDate.setDate(data[2]);
		aDate.setMonth(data[1]-1);
		aDate.setFullYear(data[0]);
	}
	else if (Calendar.format.substring(0,1).toUpperCase() == "M"){
		aDate.setDate(data[1]);
		aDate.setMonth(data[0]-1);
		aDate.setFullYear(data[2]);
	}
	return aDate;
}

// Following 2 functions by: Mircho Mirev

function getObject(sId)
{
	if (bw.dom){
		this.hElement = document.getElementById(sId);
		this.hStyle = this.hElement.style;
	}
	else if (bw.ns4){
		this.hElement = document.layers[sId];
		this.hStyle = this.hElement;
	}
	else if (bw.ie){
		this.hElement = document.all[sId];
		this.hStyle = this.hElement.style;
	}
}


getObject.getSize = function(sParam, hLayer)
{
	nPos = 0;
	while ((hLayer.tagName) && !( /(body|html)/i.test(hLayer.tagName))){
		nPos += eval('hLayer.' + sParam);
		if (sParam == 'offsetTop'){
			if (hLayer.clientTop){
				nPos += hLayer.clientTop;
			}
		}
		if (sParam == 'offsetLeft'){
			if (hLayer.clientLeft){
				nPos += hLayer.clientLeft;
			}
		}
		hLayer = hLayer.offsetParent;
	}
	return nPos;
}


/**
 * Based on code by: Peter Todorov
 */
function writeLayer(ID, parentID, sText)
{
	if (document.layers){
		var oLayer;
		if(parentID){
			oLayer = eval('document.' + parentID + '.document.' + ID + '.document');
		}
		else{
			oLayer = document.layers[ID].document;
		}
		oLayer.open();
		oLayer.write(sText);
		oLayer.close();
	}
	else if(document.all){
		document.all[ID].innerHTML = sText;
	}
	else{
		document.getElementById(ID).innerHTML = sText;
	}
}