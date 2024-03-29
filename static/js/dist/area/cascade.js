/*
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * @name        jQuery Cascdejs plugin
 * @author        zdy
 * @version    1.0
 */

//首先需要初始化
var xmlDoc;
var TopnodeList;
var citys;
var countyNodes;
var nodeindex = 0;
var childnodeindex = 0;
//获取xml文件
function cascdeInit(v1, v2, v3, v4) {
    id1 = "sel-provance";
    id2 = "sel-city";
    id3 = "sel-area";

    if (v4) {
        id1 += v4;
        id2 += v4;
        id3 += v4;
    }
    //alert("-"+id1+"-");return false;

    //打开xlmdocm文档
    xmlDoc = loadXmlFile('../addons/ching_leeing/static/js/dist/area/Area.xml?v=3');
    var dropElement1 = document.getElementById(id1);
    var dropElement2 = document.getElementById(id2);
    var dropElement3 = document.getElementById(id3);
    RemoveDropDownList(dropElement1);
    RemoveDropDownList(dropElement2);
    RemoveDropDownList(dropElement3);
    if (window.ActiveXObject) {
        TopnodeList = xmlDoc.selectSingleNode("address").childNodes;
    }
    else {
        TopnodeList = xmlDoc.childNodes[0].getElementsByTagName("province");
    }
    if (TopnodeList.length > 0) {
        //省份列表
        var county;
        var province;
        var city;
        for (var i = 0; i < TopnodeList.length; i++) {
            //添加列表项目
            county = TopnodeList[i];
            var option = document.createElement("option");
            option.value = county.getAttribute("name");
            option.text = county.getAttribute("name");
            if (v1 == option.value) {
                option.selected = true;
                nodeindex = i;
            }
            dropElement1.add(option);
        }
        if (TopnodeList.length > 0) {
            //城市列表
            citys = TopnodeList[nodeindex].getElementsByTagName("city")
            for (var i = 0; i < citys.length; i++) {
                var id = dropElement1.options[nodeindex].value;
                //默认为第一个省份的城市
                province = TopnodeList[nodeindex].getElementsByTagName("city");
                var option = document.createElement("option");
                option.value = province[i].getAttribute("name");
                option.text = province[i].getAttribute("name");
                if (v2 == option.value) {
                    option.selected = true;
                    childnodeindex = i;
                }
                dropElement2.add(option);
            }
            selectcounty(v3, v4);
        }
    }
}

/*
 //依据省设置城市，县
 */
function selectCity(v4) {

    id1 = "sel-provance";
    id2 = "sel-city";
    id3 = "sel-area";

    if (v4) {
        id1 += v4;
        id2 += v4;
        id3 += v4;
    }
    var dropElement1 = document.getElementById(id1);
    var name = dropElement1.options[dropElement1.selectedIndex].value;
    countyNodes = TopnodeList[dropElement1.selectedIndex];
    var province = document.getElementById(id2);
    var city = document.getElementById(id3);
    RemoveDropDownList(province);
    RemoveDropDownList(city);
    var citynodes;
    var countycodes;
    if (window.ActiveXObject) {
        citynodes = xmlDoc.selectSingleNode('//address/province [@name="' + name + '"]').childNodes;
    } else {
        citynodes = countyNodes.getElementsByTagName("city")
    }
    if (window.ActiveXObject) {
        countycodes = citynodes[0].childNodes;
    } else {
        countycodes = citynodes[0].getElementsByTagName("county")
    }

    if (citynodes.length > 0) {
        //城市
        for (var i = 0; i < citynodes.length; i++) {
            var provinceNode = citynodes[i];
            var option = document.createElement("option");
            option.value = provinceNode.getAttribute("name");
            option.text = provinceNode.getAttribute("name");
            province.add(option);
        }
        if (countycodes.length > 0) {
            //填充选择省份的第一个城市的县列表
            for (var i = 0; i < countycodes.length; i++) {
                var dropElement2 = document.getElementById(id2);
                var dropElement3 = document.getElementById(id3);
                //取当天省份下第一个城市列表

                //alert(cityNode.childNodes.length); 
                var option = document.createElement("option");
                option.value = countycodes[i].getAttribute("name");
                option.text = countycodes[i].getAttribute("name");
                dropElement3.add(option);
            }
        }
    }
}
/*
 //设置县,区
 */
function selectcounty(v3, v4) {
    id1 = "sel-provance";
    id2 = "sel-city";
    id3 = "sel-area";

    if (v4) {
        id1 += v4;
        id2 += v4;
        id3 += v4;
    }

    var dropElement1 = document.getElementById(id1);
    var dropElement2 = document.getElementById(id2);
    var name = dropElement2.options[dropElement2.selectedIndex].value;
    var city = document.getElementById(id3);
    var countys = TopnodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getElementsByTagName("county")
    RemoveDropDownList(city);
    for (var i = 0; i < countys.length; i++) {
        var countyNode = countys[i];
        var option = document.createElement("option");
        option.value = countyNode.getAttribute("name");
        option.text = countyNode.getAttribute("name");
        if (v3 == option.value) {
            option.selected = true;
        }
        city.add(option);
    }
}
function RemoveDropDownList(obj) {
    if (obj) {
        var len = obj.options.length;
        if (len > 0) {
            for (var i = len; i >= 0; i--) {
                obj.remove(i);
            }
        }
    }
}
/*
 //读取xml文件
 */
function loadXmlFile(xmlFile) {
    var xmlDom = null;
    if (window.ActiveXObject) {
        xmlDom = new ActiveXObject("Microsoft.XMLDOM");
        xmlDom.async = false;
        xmlDom.load(xmlFile) || xmlDom.loadXML(xmlFile);//如果用的是XML字符串//如果用的是xml文件  
    } else if (document.implementation && document.implementation.createDocument) {
        var xmlhttp = new window.XMLHttpRequest();
        xmlhttp.open("GET", xmlFile, false);
        xmlhttp.send(null);
        xmlDom = xmlhttp.responseXML;
    } else {
        xmlDom = null;
    }
    return xmlDom;
}