/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function example1(id) {
    //radio example
    $('#' + id).init_item_table_pickup(
            {
                input_type: 'radio', //required
                column_size: 1, //optional
                line_size: 2, //optional, if column_size is gaven, this will be ignor.
                odd_column_class: 'odd_column', //optional
                even_column_class: 'even_column', //optional
                odd_line_class: 'odd_line', //optional
                even_line_class: 'even_line', //optional
                table_id: 'table_id',
                table_class: 'table_class',
                caption: 'my caption',
                item_name: 'radio_name',
                data: [
                    {
                        display: 'aa',
                        'id': 'dddd',
                        name: 'radio_name',
                        value: 'vvv',
                        class: 'myclass',
                        link: 'http://www.google.com',
                        link_class: 'link_class'
                    },
                    'efg',
                    '1231',
                    'asdf',
                    '2134123',
                    'asdf'], //requried
                selected_values: ['vvv', 'asdf']
            }
    );
}
function example2(id) {
    //checkbox example
    $('#' + id).init_item_table_pickup(
            {
                input_type: 'checkbox', //required
                column_size: 1, //optional
                line_size: 2, //optional, if column_size is gaven, this will be ignor.
                odd_column_class: 'odd_column', //optional
                even_column_class: 'even_column', //optional
                odd_line_class: 'odd_line', //optional
                even_line_class: 'even_line', //optional
                table_id: 'table_id',
                table_class: 'table_class',
                caption: 'my caption',
                item_name: 'radio_name',
                data: [
                    {
                        display: 'aa',
                        'id': 'dddd',
                        name: 'radio_name',
                        value: 'vvv',
                        class: 'myclass',
                        link: 'http://www.google.com',
                        link_class: 'link_class'
                    },
                    'efg',
                    '1231',
                    'asdf',
                    '2134123',
                    'asdf'], //requried
                selected_values: ['vvv', 'asdf']
            }
    );
}

jQuery.fn.extend(
        {
            init_item_table_pickup: function(param) {

                if (typeof param.data === 'undefined') {
                    return;
                }

                var tableHtml = "";
                tableHtml += "<table";

                if (typeof param.table_id === 'string') {

                    tableHtml += " id='" + param.table_id + "'";

                }

                if (typeof param.table_class === 'string') {

                    tableHtml += " class='" + param.table_class + "'";

                }


                tableHtml += ">";


                if (typeof param.caption === 'string') {
                    tableHtml += "<caption>" + param.caption + "</caption>";
                }

                tableHtml += "<tbody>";

                var tSize = param.data.length;

                var cSize = 0;

                var lSize = 0;

                var defaultCSize = 5;

                if (typeof param.column_size !== 'undefined') {
                    cSize = parseInt(param.column_size);

                    lSize = tSize / cSize;

                    if (tSize % cSize !== 0) {
                        lSize++;
                    }
                }
                else if (typeof param.line_size !== 'undefined') {
                    lSize = parseInt(param.line_size);
                    cSize = tSize / lSize;

                    if (tSize % lSize !== 0) {
                        cSize++;
                    }
                }
                else {
                    cSize = defaultCSize;
                    lSize = tSize / cSize;

                    if (tSize % cSize !== 0) {
                        lSize++;
                    }
                }


                var oddColumnClass = "";
                if (typeof param.odd_column_class === 'string') {
                    oddColumnClass = param.odd_column_class;
                }
                var evenColumnClass = "";
                if (typeof param.even_column_class === 'string') {
                    evenColumnClass = param.even_column_class;
                }
                var oddLineClass = "";
                if (typeof param.odd_line_class === 'string') {
                    oddLineClass = param.odd_line_class;
                }
                var evenLineClass = "";
                if (typeof param.even_line_class === 'string') {
                    evenLineClass = param.even_line_class;
                }



                var pickupInputClass = typeof param.input_class === 'string' ? param.input_class : "";


                console.log(typeof param.selected_values);
                var selectedValue = {};
                if (typeof param.selected_values === 'object') {
                    for (var ind in param.selected_values) {
                        selectedValue[param.selected_values[ind]] = 1;
                    }
                }

                var itemName = typeof param.item_name === 'string' ? param.item_name : "";



                var dInd = 0;
                for (var li = 0; li < lSize; li++) {

                    tableHtml += "<tr>";
                    for (var co = 0; co < cSize; co++) {
                        var classV = "";
                        if (li % 2 === 0) {
                            classV += evenLineClass;
                        }
                        else {
                            classV += oddLineClass;
                        }

                        if (co % 2 === 0) {
                            classV += evenColumnClass;
                        }
                        else {
                            classV += oddColumnClass;
                        }

                        tableHtml += "<td class='" + classV + "'>";

                        var ty = typeof param.data[dInd];
                        if (ty !== 'undefined') {

                            var sid = "";
                            var sName = itemName === '' ? '' : 'name="' + itemName + '"';
                            var sValue = "";
                            var sClass = "";
                            var sDisplay = "";
                            var aHeader = "";
                            var aTailer = "";
                            if (ty === 'string') {

                                sDisplay = param.data[dInd];
                                sValue = sDisplay;

                                if (pickupInputClass !== '') {
                                    sClass = "class='" + pickupInputClass + "'"
                                }

                            }
                            else if (ty === 'object') {
                                var lObj = param.data[dInd];

                                var tClass = pickupInputClass;
                                if (typeof lObj.id === 'string') {
                                    sid = "id='" + lObj.id + "'";
                                }
                                if (typeof lObj.value === 'string') {
                                    sValue = lObj.value;
                                }
                                if (typeof lObj.display === 'string') {
                                    sDisplay = lObj.display;
                                }
                                if (typeof lObj.name === 'string') {
                                    sName = "name='" + lObj.name + "'";
                                }

                                if (typeof lObj.class === 'string') {
                                    tClass += " ";
                                    tClass += lObj.class;
                                }

                                if (tClass !== '') {
                                    sClass = "class='" + tClass + "'"
                                }


                                if (typeof lObj.link === 'string') {
                                    aHeader = "<a href='" + lObj.link + "'";

                                    if (typeof lObj.link_class === 'string') {
                                        aHeader += " class='" + lObj.link_class + "'";
                                    }

                                    aHeader += ">";
                                    aTailer = "</a>";
                                }



                            }

                            var sChecked = typeof selectedValue[sValue] !== 'undefined' ? "checked='checked'" : "";


                            tableHtml += "<input type='" + param.input_type + "' " +
                                    sid + " " + sName + " value='" + sValue + "' " + sChecked + " " +
                                    sClass + " >" + aHeader + sDisplay + aTailer;

                        }

                        tableHtml += "</td>";

                        dInd++;
                    }
                    tableHtml += "</tr>";
                }
                tableHtml += "</tbody>"
                tableHtml += "</table>";

                this.html(tableHtml);
            }
        }

);