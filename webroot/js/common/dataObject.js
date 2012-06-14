//defined a basic element class and it's construct function

function QueryElement(parentId) {
	this.id = my_uuid();
	this.type = "abstract";
	this.print_name = "";
	this.ready = false;
	this.required = false;
	this.parent_id = parentId;
	this.tree_level = 0;
	this.style_classes = [ 'query_element' ];
	this.alias_name = "";
	this.depends = [ parentId ];
}

QueryElement.prototype = {

	getID : function() {
		return this.id;
	},

	statistic : function(collectSet) {
		collectSet[this.getID()] = this;
	},

	printNameToHtml : function() {
		if (this.print_name == undefined || this.print_name == "") {
			return '';
		} else {
			return "<div class='query_print_name query_element'>"
					+ this.spaceToHtml()
					+ "<span class='span_query_print_name'>" + this.print_name
					+ "</span></div>";
		}
	},

	sytleClassesToHtml : function() {
		re = "";
		for (index in this.style_classes) {
			re += this.style_classes[index];
			re += ' ';
		}
		if (this.required) {
			re += 'query_required ';
		} else {
			re += 'query_unrequired ';
		}
		
		if(this.ready){
			
			re += 'query_ready ';
		} else {
			re += 'query_noready ';
		}
		

		re += "query_tree_level_" + this.tree_level + " ";
		return re;
	},

	divHeaderToHtml : function() {
		var re = "<div ";
		re += "id='" + this.id + "' ";
		re += "class='" + this.sytleClassesToHtml() + "' ";
		re += ">";
		return re;
	},
	divTailerToHtml : function() {
		return "</div><!-- end div " + this.id + " -->";
	},

	spaceToHtml : function() {
		return "<span class='query_space'>&nbsp;</span>";
	},

	toHtml : function() {
		re = this.divHeaderToHtml();
		re += this.extraHeader();
		re += this.printNameToHtml();
		re += this.extraTailer();
		re += this.divTailerToHtml();
		return re;
	},

	setTreeLevel : function(l) {
		this.tree_level = l;
	},
	extraHeader : function() {
		return '';
	},

	extraTailer : function() {
		return '';
	}

}

// /// query field

function QueryField(parentId, tableId, filedName, aliasName) {
	QueryElement.call(this, parentId);
	this.type = 'field';
	this.ready = true;
	if (tableId == undefined || tableId == "" || filedName == undefined
			|| filedName == "") {
		this.print_name = '*';
	} else {
		this.print_name = '*';
	}

}

QueryField.prototype = new QueryElement();

// // query table class

function QueryTable(parentId, tableName, aliasName) {
	QueryElement.call(this, parentId);
	this.type = 'field';
	this.table_name = tableName;
	if (aliasName == undefined || aliasName == "") {
		this.alias_name = tableName;
	} else {
		this.alias_name = aliasName;
	}

}
QueryTable.prototype = new QueryElement();

// defined the constant class
function QueryConstant(parentId, printName) {
	QueryElement.call(this, parentId);
	this.print_name = printName;
	this.type = "constant";
	this.ready = true;
	this.style_classes.push('query_constant');
}

// let the first class be the second class's parent
QueryConstant.prototype = new QueryElement();

// ////////////////////// query clause class
function QueryClause(parentId) {
	QueryElement.call(this, parentId);
	this.type = "clause";
	this.style_classes.push('query_clause');
	this.can_empty = false;
	this.elements = [];
}
QueryClause.prototype = new QueryElement();

QueryClause.prototype.setTreeLevel = function(l) {
	this.tree_level = l;
	l++;
	for (x in this.elements) {
		this.elements[x].setTreeLevel(l);
	}
}
// override the method and call this parent's funtion
QueryClause.prototype.toHtml = function() {
	var re = this.divHeaderToHtml();

	re += this.extraHeader();
	re += this.printNameToHtml();

	for (index in this.elements) {

		re += this.elements[index].toHtml();
	}
	re += this.extraTailer();
	re += this.divTailerToHtml();
	return re;
}
QueryClause.prototype.statistic = function(collectSet) {
	QueryElement.prototype.statistic.call(this, collectSet);
	for (index in this.elements) {
		this.elements[index].statistic(collectSet);
	}
}
// //////////// FIELDS CLUASE
function QueryClauseFields(parentId) {
	QueryClause.call(this, parentId);
	this.can_empty = false;
	this.ready = true;
	this.required=true;
	this.elements.push(new QueryField(this.getID(), '', '', ''));
}
QueryClauseFields.prototype = new QueryClause();

// //////////// FROM CLAUSE

function QueryClauseFrom(parentId) {
	QueryClause.call(this, parentId);
	this.ready = false;
	this.can_empty = false;
	this.required=true;
	this.print_name = "FROM";
}
QueryClauseFrom.prototype = new QueryClause();

// //////////// WHERE CLAUSE
function QueryClauseWhere(parentId) {
	QueryClause.call(this, parentId);
}
QueryClauseWhere.prototype = new QueryClause();

// /////////// ORDER CLAUSE

function QueryClauseOrder(parentId) {
	QueryClause.call(this, parentId);
}
QueryClauseOrder.prototype = new QueryClause();

// /////////// LIMIT CLAUSE

function QueryClauseLimit(parentId) {
	QueryClause.call(this, parentId);
}
QueryClauseLimit.prototype = new QueryClause();
// ////////////SELECT CLUASE

function QueryClauseSelect(parentId) {
	QueryClause.call(this, parentId);
	this.type = "select_clause";
	this.print_name = "SELECT";
	this.can_empty=false;
	this.ready=false;

	this.elements.push(new QueryClauseFields(this.id));
	this.elements.push(new QueryClauseFrom(this.id));
}
QueryClauseSelect.prototype = new QueryClause();

// create a object
var root_id = my_uuid();
var query_root = new QueryClauseSelect(root_id);
query_root.required=true;
var root_collect = {};
query_root.statistic(root_collect);
