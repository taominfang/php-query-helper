//defined a basic element class and it's construct function
var my_debug=true;
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
	this.editable = true;
	this.depends = [ parentId ];
}

QueryElement.prototype = {

	getID : function() {
		return this.id;
	},

	statistic : function(collectSet) {
		collectSet.value[this.getID()] = this;
	},

	printNameToHtml : function() {
		if (this.print_name == undefined || this.print_name == "") {
			return '';
		} else {
			return "<div class='query_print_name query_element'>"
					//+ this.spaceToHtml()
					
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
			re += 'query_norequired ';

		}

		if (this.ready) {

			re += 'query_ready ';
		} else {
			re += 'query_noready ';
		}

		if (this.editable) {

			re += 'query_editable ';
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
		var re= "</div>";
		if(my_debug){
			re+="<!-- end div " + this.id + " -->";
		}
		return re;
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
	},

	log : function(str) {
		if(my_debug){
			console.log(str);
		}
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
	this.log("levle:" + l + " type:" + this.type + " id:" + this.id);
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

		re+=this.spaceToHtml()+this.elements[index].toHtml();
		
		
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

QueryClause.prototype.spaceToHtml = function() {
	return "<div class='query_clause_space query_element'>&nbsp;</div>";
}
// //////////// FIELDS CLUASE
function QueryClauseFields(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_fields";
	this.can_empty = false;
	this.ready = true;
	this.required = true;
	this.elements.push(new QueryField(this.getID(), '', '', ''));
}
QueryClauseFields.prototype = new QueryClause();

// //////////// FROM CLAUSE

function QueryClauseFrom(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_from"
	this.ready = false;
	this.can_empty = false;
	this.required = true;
	this.print_name = "FROM";
}
QueryClauseFrom.prototype = new QueryClause();

// //////////// WHERE CLAUSE
function QueryClauseWhere(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_where"
	this.print_name = "W";
	this.ready = false;
	this.required = false;
	this.can_empty = false;

}
QueryClauseWhere.prototype = new QueryClause();

// //////////// Group by CLAUSE
function QueryClauseGroupBy(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_groupby"
	this.print_name = "G";
	this.ready = false;
	this.required = false;
	this.can_empty = false;

}
QueryClauseGroupBy.prototype = new QueryClause();

// /////////// ORDER CLAUSE

function QueryClauseOrder(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_order"
	this.print_name = "O";
	this.ready = false;
	this.required = false;
	this.can_empty = false;
}
QueryClauseOrder.prototype = new QueryClause();

// /////////// LIMIT CLAUSE

function QueryClauseLimit(parentId) {
	QueryClause.call(this, parentId);
	this.type="clause_limit"
	this.print_name = "L";
	this.ready = false;
	this.required = false;
	this.can_empty = false;
}
QueryClauseLimit.prototype = new QueryClause();
// ////////////SELECT CLUASE

function QueryClauseSelect(parentId) {
	QueryClause.call(this, parentId);
	this.type = "clause_select";
	this.print_name = "SELECT";
	this.can_empty = false;
	this.ready = false;
	this.editable = false;

	this.elements.push(new QueryClauseFields(this.id));
	this.elements.push(new QueryClauseFrom(this.id));
	this.elements.push(new QueryClauseWhere(this.id));
	this.elements.push(new QueryClauseGroupBy(this.id));
	this.elements.push(new QueryClauseOrder(this.id));
	this.elements.push(new QueryClauseLimit(this.id));

}
QueryClauseSelect.prototype = new QueryClause();

// //

function reinitVariablesFromTree(__collect, __tree) {
	__collect.value = {};
	__tree.setTreeLevel(1);
	__tree.statistic(__collect);
}

// create a object
var root_collect = {
	value : ''
};
var root_id = my_uuid();
var query_root = new QueryClauseSelect(root_id);
query_root.required = true;

reinitVariablesFromTree(root_collect, query_root);

function findQueryElementById(id){
	return root_collect.value[id];
}
