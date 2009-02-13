	var projectTable;
	var rowId = 0;
	function addColumn() {
		var foo = document.getElementById("rowTemplate");	
		var rowTemplate = foo.cloneNode(true);
		
		projectTable = document.getElementById("projectTable");
		rowTemplate.id = "projectRow_"+rowId++;
//		columnCounter.id = "columnCounter_"+rowId++;
		document.getElementById("columnCounter").value = rowId;
		rowTemplate.style.display = "table-row";
		projectTable.insertBefore(rowTemplate, foo);
 	}
		
	var deleteRow = function (obj) {
		var tr = obj.parentNode.parentNode;
		tr.parentNode.removeChild(tr);
	}
	
	
	
	
	
	function preview() {
		div = document.getElementById("preview");
		numberOfElements = document.project.column.length;		
		
		var tableStart = '<table id="preview" border="0" cellpadding="5" cellspacing="0"><tr>';
		var tableEnd = '<\/tr><\/table>';
		var rows = "";
		var blindText = "";
		var i = 1;
		
		while (i < numberOfElements) {
			var rows = rows + '<td><b>' + document.project.column[i].value + '<\/b><\/td>';
			var blindText = blindText + '<td>lorem ipsum..<\/td>';
			i++;
		}
		div.innerHTML = tableStart + rows + '<tr>' + blindText + '<\/tr>' + tableEnd;
	}
	
	
	
	
	function validateInput(input) {
		var expression = 	/\W/;
		input = input.replace(expression, "_");
		
		if(input == "country") {
			input = "country2";
		}
		
		return input;
	}
	
	
	
	
	
	function checkColumns() {
		numberOfElements = document.project.column.length;
		
		if(!numberOfElements) {
			return "NO_COLUMN";
		}
		
		var i = 1;
		var error = false;
		while (i < numberOfElements) {
			if(document.project.column[i].value == "") {
				return "EMPTY_COLUMN";
			}
			i++;
		}
		
		return "OK";		
	}
	
	
	