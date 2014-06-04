/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function QueryLogic(div_id, initData, conditionOptions) {

    if(typeof div_id === 'undefined'){
        return;
    }

    if (typeof initData === 'undefined' || initData === null) {
        initData = this.createLogicNode(null, 0);
    }
    this.div_id = div_id;
    this.data = initData;

    this.uid_map = {};
    this.buildUidMap(this.data);
    this.current_edit_node = null;


    var that = this;
    $('#logic_edit_dialog').dialog({
        autoOpen: false,
        modal: true,
        buttons: [
            {
                text: "Save",
                click: function() {
                    $(this).dialog("close");

                    that.editDialogSaveValue();

                }
            },
            {
                text: "Close",
                click: function() {
                    $(this).dialog("close");
                }
            }
        ],
        width: 800,
        title: 'Logic Edit'
    });

    this.uid_show_text_map = {};

    for (var ind in conditionOptions) {
        var one = conditionOptions[ind];
        var showText = (typeof one.column.talias !== 'undefined' && one.column.talias !== '') ?
                one.column.talias + "." + one.column.cname :
                one.column.cname;
        this.uid_show_text_map[one.uuid] = showText;
    }

    var options = "";

    options += "<option value='custom_value'>CUSTOM VALUE</option>";
    options += "<option value='variable_value'>VARIABLE</option>";

    for (var ind in this.uid_show_text_map) {



        options += "<option value='";
        options += ind;
        options += "'>";

        options += that.escapeHTML(this.uid_show_text_map[ind]);

        options += "</option>";


    }



    $('#logic_edit_dialog_left_options').html(options);
    $('#logic_edit_dialog_right_options').html(options);
    $('#logic_edit_dialog_extra_options').html(options);






    $('#logic_edit_dialog_cleft_bt').click(function() {

        that.convert2ChildNode(true);

        return false;
    });

    $('#logic_edit_dialog_cright_bt').click(function() {

        that.convert2ChildNode(false);

        return false;
    });


    $('#logic_edit_dialog_cand_bt').click(function() {

        that.setNodeConnectorLogic("AND");

        return false;
    });

    $('#logic_edit_dialog_cor_bt').click(function() {

        that.setNodeConnectorLogic("OR");
        return false;
    });

    $('#logic_edit_dialog_delete_bt').click(function() {
        var editNode = that.current_edit_node;

        if (editNode.type === 0) {
            //root
            that.data = that.createLogicNode(null, 0);

        }
        else {
            if (editNode.type === 1) {
                //I am left
                var keep = editNode.parent.right;
            }
            else if (editNode.type === 2) {

                //I am right
                var keep = editNode.parent.left;
            }

            keep.type = keep.parent.type;
            if (keep.parent.type === 0) {
                //parent is root
                that.data = keep;
                keep.parent = null;

            }
            else if (keep.parent.type === 1) {
                //parent is left
                keep.parent.parent.left = keep;
                keep.parent = keep.parent.parent;
            }
            else if (keep.parent.type === 2) {
                //parent is right
                keep.parent.parent.right = keep;
                keep.parent = keep.parent.parent;
            }

        }
        that.uid_map = {};
        that.buildUidMap(that.data);
        that.current_edit_node = null;
        that.logic_view_rendering();
        $('#logic_edit_dialog').dialog("close");
        return false;
    });

    this.editDialogSelectInputSet = {
        logic_edit_dialog_left_options: {
            input_id: 'logic_edit_dialog_left_value',
            saved_select_option_name: 'left_select',
            saved_input_name: 'left_value'
        },
        logic_edit_dialog_logic_options: {
            input_id: 'logic_edit_dialog_logic_value',
            saved_select_option_name: 'logic_select',
            saved_input_name: 'logic_value'
        },
        logic_edit_dialog_right_options: {
            input_id: 'logic_edit_dialog_right_value',
            saved_select_option_name: 'right_select',
            saved_input_name: 'right_value'
        },
        logic_edit_dialog_extra_options: {
            input_id: 'logic_edit_dialog_extra_value',
            saved_select_option_name: 'extra_select',
            saved_input_name: 'extra_value'
        }
    };

    for (var ind in this.editDialogSelectInputSet) {
        this.initEditDialogSelectEvent(ind);
    }


}

QueryLogic.prototype.setResultTextId = function(tid) {
    this.text_result_id = tid;
};

QueryLogic.prototype.editDialogSaveValue = function() {
    if (this.current_edit_node !== null) {
        //get description
        this.current_edit_node.desc = $('#logic_edit_dialog_desc').val();

        if (this.current_edit_node.connector === null) {
            // this is simple condition
            var values = {};
            var vc = 0; //the total number of unempty input, value coutner
            for (var ind in this.editDialogSelectInputSet) {
                this.initEditDialogSelectEvent(ind);
                var v1 = $('#' + this.editDialogSelectInputSet[ind]['input_id']).val();
                if (v1 !== '') {
                    vc++;
                }
                values[this.editDialogSelectInputSet[ind]['saved_input_name']] = v1;
            }



            var logicSelect = $('#logic_edit_dialog_logic_options').val();

            if (vc > 1) {
                this.current_edit_node.condition = {};
                for (var ind in this.editDialogSelectInputSet) {
                    var savaValueName = this.editDialogSelectInputSet[ind]['saved_input_name'];
                    var saveSelectName = this.editDialogSelectInputSet[ind]['saved_select_option_name'];

                    var sel = $('#' + ind).val();
                    this.current_edit_node.condition[saveSelectName] = sel;
                    if (sel === "variable_value" || sel === 'custom_value' || ind === 'logic_edit_dialog_logic_options') {
                        this.current_edit_node.condition[savaValueName] = values[savaValueName];

                    }
                    else {
                        this.current_edit_node.condition[savaValueName] = sel;
                    }

                }

                //decide is it finised, in most situation , the follow code work, in some special , it will give the wrong finished flag.
                this.current_edit_node.finished = false;
                if (logicSelect === 'BETWEEN') {
                    if (vc > 3) {
                        this.current_edit_node.finished = true;
                    }
                }

                else if (logicSelect === 'IS NULL' || logicSelect === 'IS NOT NULL') {
                    if (this.current_edit_node.condition['left_value'] !== '') {
                        this.current_edit_node.finished = true;
                    }
                }
                else {
                    if (vc > 2) {
                        this.current_edit_node.finished = true;
                    }
                }

            }
        }

        this.finish_status_check(this.data);
    }

    this.logic_view_rendering();
};


QueryLogic.prototype.initEditDialogSelectEvent = function(selectId) {
    var that = this;
    $('#' + selectId).change(function() {
        that.editDialogSetInputBySelect(selectId);
    });
};

QueryLogic.prototype.initEditDialogSelectInputValue = function() {
    if (this.current_edit_node !== null) {

        for (var ind in this.editDialogSelectInputSet) {


            if (this.current_edit_node.condition === null) {
                this.setSelectSelectedByValue(ind, null);
                $('#' + this.editDialogSelectInputSet[ind]['input_id']).val('');
            }
            else {
                this.setSelectSelectedByValue(ind, this.current_edit_node.condition[this.editDialogSelectInputSet[ind]['saved_select_option_name']]);


                $('#' + this.editDialogSelectInputSet[ind]['input_id']).val(this.current_edit_node.condition[this.editDialogSelectInputSet[ind]['saved_input_name']]);

            }

            this.editDialogSetInputBySelect(ind);
        }






    }
};



QueryLogic.prototype.editDialogSetInputBySelect = function(selectId) {
    var inputId = this.editDialogSelectInputSet[selectId]['input_id'];
    var cv = $('#' + selectId).val();
    if (cv === 'custom_value' || cv === 'variable_value') {
        $('#' + inputId).prop('readonly', false);
    }
    else {

        var showText = "";

        $('#' + selectId + " option").each(function() {
            if ($(this).val() === cv) {
                showText = $(this).text();
            }
        });


        $('#' + inputId).val(showText);
        $('#' + inputId).prop('readonly', true);
    }

    if (selectId === 'logic_edit_dialog_logic_options') {
        //logic

        if (cv === 'BETWEEN') {
            $('.logic_edit_dialog_extra_option').show();
        }
        else {
            $('.logic_edit_dialog_extra_option').hide();
        }

        if (cv === 'IS NULL' || cv === 'IS NOT NULL') {

            $('.logic_edit_dialog_right_option').hide();
        }
        else {
            $('.logic_edit_dialog_right_option').show();
        }

    }
};

QueryLogic.prototype.convert2ChildNode = function(isLeftNode) {

    var editNode = this.current_edit_node;

    var parent = this.createLogicNode(null, 0);

    parent.single = false;

    if (isLeftNode) {
        parent.right = this.createLogicNode(parent, 2);

    }
    else {
        parent.left = this.createLogicNode(parent, 1);
    }
    this.buildUidMap(parent);


    if (isLeftNode) {
        parent.left = editNode;
    }
    else {
        parent.right = editNode;
    }

    if (editNode.parent === null) {
        this.data = parent;

    }
    else {
        parent.parent = editNode.parent;

        parent.type = editNode.type;

        if (editNode.type === 1) {
            parent.parent.left = parent;
        }
        else if (editNode.type === 2) {
            parent.parent.right = parent;
        }

    }

    editNode.parent = parent;



    this.logic_view_rendering();

    $('#logic_edit_dialog').dialog("close");
};

QueryLogic.prototype.setNodeConnectorLogic = function(logic) {

    var editNode = this.current_edit_node;

    if (editNode.condition === null) {

        if (editNode.left === null) {
            editNode.left = this.createLogicNode(editNode, 1);
            this.buildUidMap(editNode.left);
        }
        if (editNode.right === null) {
            editNode.right = this.createLogicNode(editNode, 2);
            this.buildUidMap(editNode.right);
        }
        editNode.single = false;
        editNode.connector = logic;
        this.logic_view_rendering();
    }

    $('#logic_edit_dialog').dialog("close");
};

QueryLogic.prototype.buildUidMap = function(node) {
    if (node === null) {
        return;
    }
    this.uid_map[node.uuid] = node;
    this.buildUidMap(node.left);
    this.buildUidMap(node.right);

};

QueryLogic.prototype.createLogicNode = function(parent, type, copyFrom) {
    var re = {
        single: true,
        finished: false,
        desc: "",
        left: null,
        right: null,
        parent: parent,
        type: type,
        connector: null,
        condition: null,
        reverse: false,
        uuid: this.getUUID()

    };

    if (typeof copyFrom !== 'undefined') {
        for (var ind in re) {
            if (typeof copyFrom[ind] !== 'undefined') {
                if (ind === 'left' && copyFrom[ind] !== null) {


                    re.left = this.createLogicNode(null, 0, copyFrom.left);;
                    re.left.parent = re;
                }
                else if (ind === 'right' && copyFrom[ind] !== null) {
                    re.right = this.createLogicNode(null, 0, copyFrom.right);
                    re.right.parent = re;
                }
                else {
                    re[ind] = copyFrom[ind];
                }
            }
        }


    }
    return re;
};

QueryLogic.prototype.removeCircularStructureFromNod = function(node) {
    if (node !== null) {
        node.parent = null;
        this.removeCircularStructureFromNod(node.left);
        this.removeCircularStructureFromNod(node.right);

    }

    return node;

};

QueryLogic.prototype.buildeNodeFamilyRelationship = function(node,parent) {
    if (node !== null) {
        node.parent = parent;
        this.buildeNodeFamilyRelationship(node.left,node);
        this.buildeNodeFamilyRelationship(node.right,node);

    }

};



QueryLogic.prototype.initEditButtonClickEvent = function() {
    var that = this;
    $('.logic_div_hint').click(function() {
        var id = $(this).attr('id');

        if (typeof that.uid_map[id] !== 'undefined') {

            var editNode = that.uid_map[id];
            that.current_edit_node = editNode;

            $('.logic_edit_dialog_convert_bt').show();

            if (editNode.condition !== null) {
                $('#logic_edit_dialog_cand_bt').hide();
                $('#logic_edit_dialog_cor_bt').hide();
            }

            $('#logic_edit_dialog_desc').val(editNode.desc);

            that.initEditDialogSelectInputValue();

            $('#logic_edit_dialog').dialog("open");
        }
    });

};

QueryLogic.prototype.setSelectSelectedByValue = function(id, value) {

    if (value === null) {
        //use the first
        $('#' + id + ' option').first().prop('selected', true);
    }
    else {
        $('#' + id + ' option').each(function() {
            if ($(this).val() === value) {
                $(this).prop('selected', true);
            }
        });
    }
};



QueryLogic.prototype.logic_view_rendering = function() {

    var html = this.logic_view_nest_rendering(this.data, 0);

    $('#' + this.div_id).html(html);

    this.initEditButtonClickEvent();

    if (typeof this.text_result_id !== 'undefined') {
        $('#' + this.text_result_id).val(this.toConditionStr());
    }
};

QueryLogic.prototype.logic_view_nest_rendering = function(node, level) {
    if (node === null) {
        return "";
    }

    var levelMode = level % 4;

    var modeClass = "levelMode" + levelMode;

    var html = "<div class='logic_div " + modeClass + "'>";


    if (node.connector !== null) {
        html += '<span class="logic_div_logic">Logic: ' + node.connector + '|:</span>';
    }

    html += '<span class="logic_str">' + this.node2Str(node) + '</span>';

    html += '<span class="logic_div_desc">' + node.desc + '</span>';

    if (node.finished === false) {
        html += '<span class="logic_div_unfinished_alert">Unfinished</span>';
    }

    html += '<button id="' + node.uuid + '" class="logic_div_hint">Click to edit</button>';
    if (!node.single) {
        var lhtml = this.logic_view_nest_rendering(node.left, level + 1);
        var rhtml = this.logic_view_nest_rendering(node.right, level + 1);


        html += lhtml;
        html += rhtml;

    }

    html += "</div>";

    return html;

};

QueryLogic.prototype.getUUID = function() {
    return 'xxxxxxxx-xxxx-adr-4xxx-yxxx-xxxxxxxxxxxx'.
            replace(/[xy]/g, function(c)
    {
        var r = Math.random() * 16 | 0, v = c === 'x' ? r : r & 0x3 | 0x8;
        return v.toString(16);
    });

};

QueryLogic.prototype.finish_status_check = function(node) {
    if (node === null) {
        return false;
    }
    if (!node.single) {


        var l = this.finish_status_check(node.left);
        var r = this.finish_status_check(node.right);
        node.finished = l && r;
    }

    return node.finished;
};

QueryLogic.prototype.toConditionStr = function() {
    return this.node2Str(this.data);
};

QueryLogic.prototype.getShowText = function(sel, v) {
    if (v === '') {
        return "???";
    }
    if (sel === "variable_value" || sel === 'custom_value') {
        return v;
    }
    else {
        return this.uid_show_text_map[sel];
    }
};

QueryLogic.prototype.node2Str = function(node) {

    if (node === null) {
        return null;
    }

    if (node.connector === null && node.condition === null) {
        return ' ***???*** ';
    }

    var re;
    if (node.single) {
        if (node.condition === null) {
            re = " ***???*** ";
        }


        var cond = node.condition;

        var logicV;

        var leftV = this.getShowText(cond.left_select, cond.left_value);
        var rightV = this.getShowText(cond.right_select, cond.right_value);
        var extraV = this.getShowText(cond.extra_select, cond.extra_value);




        if (cond.logic_value === '') {
            logicV = "???";
        }
        else {
            logicV = cond.logic_value;
        }


        if (cond.left_select === 'variable_value') {
            leftV = ":" + leftV;
        }
        if (cond.right_select === 'variable_value') {
            rightV = ":" + rightV;
        }
        if (cond.extra_select === 'variable_value') {
            extraV = ":" + extraV;
        }



        if (logicV === "IS NULL" || logicV === 'IS NOT NULL') {

            re = leftV + " " + logicV;
        }

        else if (logicV === "BETWEEN") {

            re = leftV + " " + logicV + " " + rightV + " AND " + extraV;
        }

        else {
            re = leftV + " " + logicV + " " + rightV;
        }





    }
    else {

        re = "";
        var leftStr = this.node2Str(node.left);
        var rightStr = this.node2Str(node.right);

        if (leftStr === null) {
            leftStr = "___ERROR_left_null___";
        }
        if (rightStr === null) {
            leftStr = "___ERROR_right_null___";
        }

        re += "( " + leftStr + ") ";
        if (node.connector === null) {
            re += "?AND/OR? ";
        }
        else {
            re += node.connector + " ";
        }
        re += "( " + rightStr + " )";

    }
    return re;

};




QueryLogic.prototype.escapeHTML = (function() {
    'use strict';
    var chr = {
        '"': '&quot;', '&': '&amp;', "'": '&#39;',
        '/': '&#47;', '<': '&lt;', '>': '&gt;'
    };
    return function(text) {
        if (typeof text === 'undefined') {
            return text;
        }
        return text.replace(/[\"&'\/<>]/g, function(a) {
            return chr[a];
        });
    };
}());

