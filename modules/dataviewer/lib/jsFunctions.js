	var projectTable;
	var rowId = 2;
	function addColumn() {
		var foo = document.getElementById("rowTemplate");	
		var rowTemplate = foo.cloneNode(true);
		projectTable = document.getElementById("projectTable");
		rowTemplate.id = "projectRow_"+rowId;
		foo.getElementsByTagName('SPAN')[0].innerHTML = rowId;
		rowTemplate.style.display = "table-row";
		projectTable.insertBefore(rowTemplate, foo);
		rowId++;
		checkNumeration();
 	}
		
	var deleteRow = function (obj) {
		var tr = obj.parentNode.parentNode;
		tr.parentNode.removeChild(tr);
		checkNumeration();
	}
	
	function checkNumeration(){
		nofElements = document.getElementsByTagName('SPAN').length;
//		alert(nofElements);
		for(i=1; i<nofElements; i++) {
			document.getElementsByTagName('SPAN')[i-1].innerHTML = i;
		}
	}
	
	
	
	
	
	
	
	
	
	function validateInput(input) {
		var expression = 	/\W/;
		input = input.replace(expression, "_");
		
		if(input == "country") {
			input = "country2";
		}
		
		return input;
	}
