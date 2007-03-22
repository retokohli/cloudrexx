/*
EASYMENU - XHTML/CSS/DHTML Semantically correct drop down menu 
Author: 	Sam Hampton-Smith
Site: 	http://www.easymenu.co.uk
*/
	// Load up the stylesheet for the menus. This code is here rather than in the html
	// document to allow for non-javascript browsers to render a seperately styled non-
	// interactive list rather than the DHTML menu
        // document.write("<link rel='stylesheet' href='cssmenu.css' type='text/css' />");
	var currentMenu = null;
	var lastMenuStarter = null;
	var mytimer = null;
	var timerOn = false;
	var opera = window.opera ? true : false;

	if (!document.getElementById)
		document.getElementById = function() { return null; }

	function initialiseDummy(dummy, root) {
		dummy.onmouseover = function() {
			containingMenu = this.parentNode.parentNode;
			for (var x=0;x<containingMenu.childNodes.length;x++) {
				if (containingMenu.childNodes[x].nodeName.toUpperCase()=="LI") {
					if (containingMenu.childNodes[x].getElementsByTagName("ul").length>0) {
						containingMenu.childNodes[x].getElementsByTagName("UL").item(0).style.visibility = 'hidden';
					}
				}
			}
		}
		dummy.onfocus = function() {
			dummy.onmouseover();
		}
	}
	
	function initialiseMenu(menu, starter, root) {
		var leftstarter = false;
	
		if (menu == null || starter == null) return;
			currentMenu = menu;
	
		starter.onmouseover = function() {
			if (currentMenu) {
				if (this.parentNode.parentNode!=currentMenu) {
					currentMenu.style.visibility = "hidden";
					hideAllMenus(currentMenu, root);

				}
				if (this.parentNode.parentNode==root) {
					while (currentMenu.parentNode.parentNode!=root) {
						currentMenu.parentNode.parentNode.style.visibility = "hidden";
						currentMenu = currentMenu.parentNode.parentNode;
					}
				}
				currentMenu = null;
				this.showMenu();
	        	}
		}
	
		menu.onmouseover = function() {
			if (currentMenu) {
				currentMenu = null;
				this.showMenu();
	        	}
		}	
	
		starter.showMenu = function() {
			if (!opera) {
				if (this.parentNode.parentNode==root) {
					menu.style.left = this.offsetLeft + "px";
					menu.style.top = this.offsetTop + this.offsetHeight + "px";
					if (menu.offsetWidth < this.offsetWidth) menu.style.width = this.offsetWidth;
				}
				else {
				 	menu.style.left = this.offsetLeft + this.offsetWidth + "px";
				 	menu.style.top = this.offsetTop + "px";
				}
			}
			else {
				var rootOffset = root.offsetLeft;
				if (this.parentNode.parentNode==root) {
					menu.style.left = this.offsetLeft - rootOffset + "px";
					menu.style.width = this.offsetWidth;
					menu.style.top = this.offsetHeight + "px";
				}
				else {
				 	menu.style.left = this.offsetWidth - rootOffset + "px";
				 	menu.style.top = this.offsetTop + "px"; //menu.style.top - menu.style.offsetHeight + "px";
				}

			}
			menu.style.visibility = "visible";
			currentMenu = menu;
		}

		starter.onfocus	 = function() {
			starter.onmouseover();
		}
	
		menu.onfocus	 = function() {
//			currentMenu.style.visibility="hidden";
		}

		menu.showMenu = function() {
			menu.style.visibility = "visible";
			currentMenu = menu;
			stopTime();
		}

		menu.hideMenu = function()  {
			if (!timerOn) {
				mytimer = setInterval("killMenu('" + this.id + "', '" + root.id + "');", 2000);
				timerOn = true;
				for (var x=0;x<menu.childNodes.length;x++) {
					if (menu.childNodes[x].nodeName=="LI") {
						if (menu.childNodes[x].getElementsByTagName("UL").length>0) {
							menuItem = menu.childNodes[x].getElementsByTagName("UL").item(0);
							menuItem.style.visibility = "hidden";
						}
					}
				}
			}
		}

		menu.onmouseout = function(event) {
			this.hideMenu();
		}
		menu.onblur = function() {
			this.hideMenu();
		}
		starter.onmouseout = function() {
			for (var x=0;x<menu.childNodes.length;x++) {
				if (menu.childNodes[x].nodeName=="LI") {
					if (menu.childNodes[x].getElementsByTagName("UL").length>0) {
						menuItem = menu.childNodes[x].getElementsByTagName("UL").item(0);
						menuItem.style.visibility = "hidden";
					}
				}
			}
			menu.style.visibility = "hidden";
		}
}
	function killMenu(menu, root) {
 timerOn = false;
		var menu = document.getElementById(menu);
		var root = document.getElementById(root);
		menu.style.visibility = "hidden";
		for (var x=0;x<menu.childNodes.length;x++) {
			if (menu.childNodes[x].nodeName=="LI") {
				if (menu.childNodes[x].getElementsByTagName("UL").length>0) {
					menuItem = menu.childNodes[x].getElementsByTagName("UL").item(0);
					menuItem.style.visibility = "hidden";
				}
			}
		}
		while (menu.parentNode.parentNode!=root) {
			menu.parentNode.parentNode.style.visibility = "hidden";
			menu = menu.parentNode.parentNode;
		}
		stopTime();
	}
	function stopTime() {
		if (mytimer) {
		 	 clearInterval(mytimer);
			 mytimer = null;
			 timerOn = false;
		}
	} 

	window.onload = function() {
		var root = document.getElementById("menuList");
		var rootOffset = root.offsetLeft;
		getMenus(root, root);
	}

function getMenus(elementItem, root) {
	var selectedItem;
	var menuStarter;
	var menuItem;
	for (var x=0;x<elementItem.childNodes.length;x++) {
		if (elementItem.childNodes[x].nodeName.toUpperCase()=="LI") {
			if (elementItem.childNodes[x].getElementsByTagName("ul").length>0) {
				menuStarter = elementItem.childNodes[x].getElementsByTagName("A").item(0);
				menuItem = elementItem.childNodes[x].getElementsByTagName("UL").item(0);
				getMenus(menuItem, root);
				initialiseMenu(menuItem, menuStarter, root);
			}
			else {
				initialiseDummy(elementItem.childNodes[x].getElementsByTagName("A").item(0), root);
			}
		}
	}
}
function hideAllMenus(elementItem, root) {
	for (var x=0;x<elementItem.childNodes.length;x++) {
		if (elementItem.childNodes[x].nodeName.toUpperCase()=="LI") {
			if (elementItem.childNodes[x].getElementsByTagName("ul").length>0) {
				elementItem.childNodes[x].getElementsByTagName("UL").item(0).style.visibility = 'hidden';
				hideAllMenus(elementItem.childNodes[x].getElementsByTagName("UL").item(0), root);
			}
		}
	}
}