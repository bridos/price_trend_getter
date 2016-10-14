//google.charts.load('current', {'packages':['corechart']});
var card_array=[];
var price_array=[];
var available_cards=[];
var selected_cards=[];
var bought_cards_array=[];
var data;
var day_limit=31;
var cards;
var to_edit;
function fetch_XML(mode)
{
		  var xhttp = new XMLHttpRequest();
  		xhttp.onreadystatechange = function()
  		{
    		if (xhttp.readyState == 4 && xhttp.status == 200)
    		{   //alert("response");
      			var replydata=xhttp.responseXML;
            //alert(replydata);
      			var cards=replydata.documentElement.getElementsByTagName('card');
            //alert(cards.length);

      			for(i=0;i<cards.length;i++)
      			{   var tmparr=[];
                var values=cards[i].getElementsByTagName('value');
                //card_array[i].push([]);
      				  tmparr.push(cards[i].getAttribute("cardname"));
                //alert(cards[i].getAttribute("cardname"));
                for(j=0;j<values.length;j++)
      				  {
      					   tmparr.push(values[j].getAttribute("value"));
      				  }
                card_array.push(tmparr);
                tmparr=[];
                selected_cards.push(cards[i].getAttribute('cardname'));
      			}
            //printinfo(card_array);
            pad_array();
            //mode 1 is called from view_data.html
            //mode 2 is called from analytics.html and test_area.html function
            if (mode==1)
            {  
              fill_lists();
              drawChart(1,0);
            }  
            else if(mode==2)
              analytics();
            else
            {}
    		}
  		};
  xhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+1, true);
  xhttp.send();
}
function show_price()
{
  var str="<table><tr>";
  for(var i=0;i<card_array.length;i++)
    str+="<th>"+card_array[i][0]+"</th>";
  "</tr>"
  for(var i=1;i<card_array[0].length;i++)
  { //console.log(card_array[i]);
    str+="<tr>";
    for(var j=0;j<card_array.length;j++)
      //console.log(card_array[i][j]);
    str+="<td>"+card_array[j][i]+"</td>";
    str+="</tr>";
  }
  str+="</table>";
  //console.log(str);
  document.getElementById("price_div").innerHTML=str;
}
function pad_array()
{   var max=-1;
    //tmparr=[];
    //find maximum length
    for (var i = 0.; i <card_array.length; i++)
      if (card_array[i].length>max)
        max=card_array[i].length;
    //fill extra spaces with the first value
    for (var i = 0; i <card_array.length; i++)
    { 
      if (card_array[i].length<max)
      {
        //alert("I will pad for: "+card_array[i][0]+" I will pad: "+(max-card_array[i].length)+" times.");
        for(j=card_array[i].length;j<max;j++)
        {card_array[i].splice(1,0,card_array[i][1]);}
      }
    }
    //remove extra entries so we have 31 measurments
    for(var i=0;i<card_array.length;i++)
      if(card_array[i].length>day_limit)
        for(var j=card_array[i].length-1;j>day_limit;j--)
          card_array[i].splice(1,1);
}
function printinfo(arr)
{ var str='';
  for (var i = 0.; i <arr.length; i++)
  { var miniarr=arr[i];
    for (var j = 0 ; j<miniarr.length; j++)
     str+="\n"+arr[i][j]+" ";
  }
  console.log(str);
}
function drawChart2(data,pieces,graph)
{ 
  var selectionarr=[];
  var length=card_array.length;
  var value_num=card_array[0].length;
  for (var i = 0 ; i<pieces; i++)
  {
    selectionarr.push([0]);
  }  
  for (var j = 0 ; j < length; j++)
  {
    selectionarr[j%pieces].push(j+1);
  }
  //for (var j = 0 ; j < selectionarr.length; j++)
    //console.log(selectionarr[j]);
    
  var options = {
          title: 'Price Trend',
          curveType: 'function',
          legend: { position: 'bottom' }
        };
  
  var myview= new google.visualization.DataView(data);
  myview.setColumns(selectionarr[graph]);
  var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
  chart.draw(myview, options);
  generatedropdown(length,pieces);
}
function generatedropdown(num,pieces)
{
  var str='';
  str+="Select how many separate diagrams you want: <select onchange='drawChart(this.value,0)'>";
  str+="<option selected='selected'>Pieces</option>";
  for (var i = 1 ; i <= num; i++)
    str+="<option " + i + ">" + i + "</option>";
  str+="</select>";
  str+=" Select which of the " + pieces + " diagrams you want to see: ";
  str+="<select onchange='drawChart("+pieces+",this.value-1)'>";
  str+="<option selected='selected'>Graphs</option>";
  for (var i = 1; i<=pieces; i++)
    str+="<option "+i+">"+i+"</option>";
  str+="</select>";
  //console.log(str);
  //document.getElementById("graph_control").innerHTML=str;
 } 
function drawChart(num,graph)
{    
      //alert(card_array.join('\n'));
     data = new google.visualization.DataTable();
      for(var i=0;i<=card_array.length;i++)
      { 
        if(i==0)
          data.addColumn('string','Name');
        else
          data.addColumn('number',card_array[i-1][0]);
      }
      
      var size=card_array[0].length;
      for(var i=0;i<size-1;i++)
      { var temparr=[];
        temparr.push((i+1).toString());
        for(var j=0;j<card_array.length;j++)
        {
            temparr.push(parseFloat(card_array[j][i+1]));
        }
        //alert(temparr);
        data.addRow(temparr);
      }
      //var jsonarr=data.toJSON();
      //alert(jsonarr);
        var options = {
          title: 'Price Trend',
          curveType: 'function',
          legend: { position: 'bottom' }
        };
       
        drawChart2(data,num,graph);
        //var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        //chart.draw(data, options);
}
function get_latest_price()
{ //alert("latest price");
  var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function()
      {
          if (xhttp.readyState == 4 && xhttp.status == 200)
          {   //alert("response");
            var replydata=xhttp.responseXML;
            //alert(replydata);
            var cards=replydata.documentElement.getElementsByTagName('card');
            //alert(cards.length);

            for(i=0;i<cards.length;i++)
            {   var tmparr=[];
                //card_array.push(cards[i].getAttribute("name"));
                //card_array.push([]);
                //card_array[i]=cards[i].getAttribute("name");
                var values=cards[i].getElementsByTagName('value');
                //card_array[i].push([]);
                tmparr.push(cards[i].getAttribute("cardname"));
                //alert(cards[i].getAttribute("cardname"));
                for(j=0;j<values.length;j++)
                {
                   tmparr.push(values[j].getAttribute("value"));
                   //card_array[i][j]=values[j].getAttribute("value");
                   //alert(j+" value is: "+values[j].getAttribute("value"));
                }
                card_array.push(tmparr);
                tmparr=[];
            }
            pad_array();
            //printinfo(card_array);
            drawChart(1,0);
          }
      }
      xhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+2, true);
      xhttp.send();
}
function show_hint(event,str)
{
  if(event.keyCode==40||event.keyCode==38||event.keyCode==13)
    return;
  if (str.length == 0)
  {
        //document.getElementById("txtHint").innerHTML = "";
        return;
  } 
  else
  {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                var replydata=xmlhttp.responseXML;
                var cards=replydata.documentElement.getElementsByTagName('card');
                var str='';
                //str+="<input id='setname' onkeyup='show_hint(this.value)' type='text'>";
                //str+='<select>';
                for (var i = 0 ; i < cards.length; i++)
                {
                  str+='<option value="'+cards[i].getAttribute('name')+'">';
                }
                //str+='</select>';
                //document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
                document.getElementById('setlist').innerHTML=str;
            }
        };
        xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?q=" + str+"&mode="+4, true);
        xmlhttp.send();
  }
}
function add_price()
{ val=document.getElementById('pricein').value;
  
    document.getElementById('pricelbl').innerHTML+=(' '+val);
    price_array.push(val);
    document.getElementById('pricein').value='';
}
function pop_price()
{ var str='';
  if (price_array.length==0)
    return;
  price_array.pop();
  for(var i=0;i<price_array.length;i++)
    str+=price_array[i]+' ';
  document.getElementById('pricelbl').innerHTML=str;
}
function add_card(mode)
{
  var name,set;
  var prices,copies,price,action;
  name=document.getElementById('cardname').value;
  set=document.getElementById('setname').value;
  var exists=document.getElementById('copies');
  if(exists===null)
  {}// console.log("no id");
  else 
    copies=document.getElementById('copies').value;
  exists=document.getElementById('price');
  if(exists===null)
  {}//  console.log('no id');
  else
    price=document.getElementById('price').value;
  //console.log("ajax"+mode);
  if(name==''||set==''||copies==''||price=='')
    return;
  prices=JSON.stringify(price_array);
  //alert(prices);
  mode==2?action='buy':action='sell';
  console.log(action);
  var xmlhttp = new XMLHttpRequest();
  if(mode==1)
  { //console.log(mode);
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
        document.getElementById('cardname').value='';
        document.getElementById('setname').value='';
        document.getElementById('pricelbl').innerHTML='';
        //document.getElementById("txtHint").innerHTML='';
        price_array=[];
                //document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
      }
    };
    xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+3+"&name="+name+"&set="+set+"&prices="+prices, true);
    xmlhttp.send();
    console.log('"GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+3+"&name="'+name+'"&set="'+set+'"&prices="'+prices+', true');
  }
  else if(mode==2||mode==3)
  { //console.log(mode);
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
        document.getElementById('cardname').value='';
        document.getElementById('setname').value='';
        document.getElementById('price').value='';
        document.getElementById('copies').value='';
      }
    };
    xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+8+"&name="+name+"&set="+set+"&price="+price+"&copies="+copies+"&action="+action, true);
    xmlhttp.send();
    console.log('"GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+8+"&name="'+name+'"&set="'+set+'"&price="'+price+'"&copies="'+copies+'"&action="'+action+', true');
  }
  else if (mode==4)
  {
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
        document.getElementById('cardname').value='';
        document.getElementById('setname').value='';
        document.getElementById('price').value='';
        document.getElementById('copies').value='';
      }
    };
    xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+12+"&name="+name+"&set="+set+"&price="+price+"&copies="+copies, true);
    xmlhttp.send();
    console.log('"GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+12+"&name="'+name+'"&set="'+set+'"&price="'+price+'"&copies="'+copies+', true');

  }
}
function get_cards()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='List of cards: <select id="del_card_sel">';
      var size=cards.length;
      //alert(size);
      for (var i = 0; i<size; i++)
      { 
        //alert(cards[i].getAttribute['cardname']);
        str+='<option>'+cards[i].getAttribute('cardname')+'</option>';
      }
      str+='</select><br><br><button  class="submit" onclick="edit_menu()()">Back</button>';
      str+='<button class = "submit" onclick="delete_card()">Delete</button>';
      document.getElementById("analytics_info").innerHTML = str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+5, true);
  xmlhttp.send();
}
function del_function(mode)
{
  if (mode==1)
    get_cards();
  else if(mode==2)
    get_bought_cards();
}
function get_bought_cards()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='List of cards: <select id="del_card_sel2">';
      var size=cards.length;
      //alert(size);
      //console.log(mode);
      for (var i = 0; i<size; i++)
      { 
        //if(mode==cards[i].getAttribute('action'))//alert(cards[i].getAttribute['cardname']);
          str+='<option value='+cards[i].getAttribute('id')+'>'+cards[i].getAttribute('name')+'</option>';
      }
      str+='</select><br><br>';
      str+='<button  class="submit" onclick="edit_menu()">Back</button>';
      str+='<button class = "submit" onclick="delete_bought_card()">Delete</button>';
      document.getElementById("analytics_info").innerHTML = str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+7, true);
  xmlhttp.send();
}
function delete_card_options()
{
  var str='';
  str+="What do you want to do: ";
  str+="<select id='del_card_mode' onchange='del_function(this.value)'>";
  str+="<option value='0' selected>---- Select an action ----</option>";
  str+="<option value='1'>Delete a monitored card</option>";
  str+="<option value='2'>Delete a bought card</option>";
  str+="</select>";
  document.getElementById('cardlist').innerHTML=str;
}
function delete_bought_card()
{ 
  var e = document.getElementById('del_card_sel2');
  var name=e.options[e.selectedIndex].value;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      get_bought_cards();
    }
  };
  xmlhttp.open("GET","http://localhost/tests_folder/price_trend_getter/functions.php?mode="+9+"&name="+name, true);
  xmlhttp.send();
}
function delete_card()
{
  //var pos=document.getElementById('del_card_sel').selectedIndex;
  var e = document.getElementById('del_card_sel');
  var pos=e.options[e.selectedIndex].value;
  //alert(pos);
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      get_cards();
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+6+"&num="+pos, true);
  xmlhttp.send();
}
function fill_lists()
{
  var str='';
  //str+="<button onclick='add_element();generate_graph3();'>Add</button>";
  str+='<select id="available_cards" size="5" style="width:150px; oveflow:scroll;" class="addlist">';
  for (var i = 0; i<available_cards.length; i++)
  {  
      if(i==0)
        str+="<option value="+i+" selected draggable='true' class='addlist'>"+available_cards[i]+"</option>";
      else
        str+="<option value="+i+" draggable='true' class='addlist'>"+available_cards[i]+"</option>";
      //console.log("card"+card_array[i][0]);
      //alert(card_array[i][0]);
  }
  str+="</select>";
  document.getElementById("to_add").innerHTML=str;
str="";
str+="<select id='selected_cards' size='5' style='width:150px; oveflow:scroll;' class='removelist'>";
  for (var i = selected_cards.length - 1; i >= 0; i--) {
    if(i==0)
        str+="<option value="+i+" selected draggable='true' class='removelist' >"+selected_cards[i]+"</option>";
      else
        str+="<option value="+i+" draggable='true' class='removelist'>"+selected_cards[i]+"</option>";
  }
  str+="</select>";
  //str+="<button onclick='remove_element();generate_graph3();'>Remove</button>";
  document.getElementById("to_remove").innerHTML=str;
  document.getElementById('selected_cards').ondblclick = function(){
    remove_element();
    var pos=document.getElementById('selected_cards');
    var value=pos.options[pos.selectedIndex].text;
    var x=document.getElementById("available_cards");
    var option=document.createElement("option");
    option.text=value;
    option.setAttribute('draggable','true');
    x.appendChild(option);
    pos.remove(pos.selectedIndex);
    //alert(this.selectedIndex);
    // or alert(this.options[this.selectedIndex].value);
    generate_graph3();
};
}
function add_element()
{
  var e=document.getElementById("available_cards");
  selected_cards.push(e.options[e.selectedIndex].text);
  //console.log(e.options[e.selectedIndex].text);
  var index = available_cards.indexOf(e.options[e.selectedIndex].text);
  available_cards.splice(index,1);
  //fill_lists();
}
function remove_element()
{
  var e=document.getElementById("selected_cards");
  available_cards.push(e.options[e.selectedIndex].text);
  //console.log(e.options[e.selectedIndex].text);
  var index = selected_cards.indexOf(e.options[e.selectedIndex].text);
  selected_cards.splice(index,1);
  //fill_lists();
}
function generate_graph3()
{
  var selectionarr=[];
  selectionarr.push(0);
  for(i=0;i<selected_cards.length;i++)
  {
    //console.log("card name is: "+selected_cards[i]+" position: "+ (index(selected_cards[i])+1));
    selectionarr.push(index(selected_cards[i])+1);
  }
  //console.log(selectionarr);
  
  var myview= new google.visualization.DataView(data);
  myview.setColumns(selectionarr);
  var options = {
          title: 'Price Trend',
          curveType: 'function',
          legend: { position: 'bottom' }
        };
  var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
  chart.draw(myview, options);//selectionarr=[0];

}
function index(name)
{  for(var i=0;i<card_array.length;i++)
    if(card_array[i][0]==name)
      return i;
}
document.addEventListener("dragstart",function(event)
{
  event.dataTransfer.setData("Text",event.target.innerHTML);
  event.target.style.opacity = "0.4";
  //console.log(event.dataTransfer.getData("Text"));
})
document.addEventListener("dragend",function(event)
{
  event.target.style.opacity="1";
})
document.addEventListener("drop",function(event)
{
  event.preventDefault();
  var data=event.dataTransfer.getData("Text");
  console.log(event.target.className);
  if(event.target.className=='addlist')
  {
    //console.log('in if');
    remove_element();
    var x=document.getElementById("available_cards");
    var option=document.createElement("option");
    option.text=data;
    option.setAttribute('draggable','true');
    x.appendChild(option);
    var pos=document.getElementById('selected_cards');
    pos.remove(pos.selectedIndex);

    //console.log(document.getElementById('available_cards').getElementsByTagName('option')[0]);
    generate_graph3();
  }
  else if(event.target.className=='removelist')
  {
    //console.log('in else if');
    add_element();
    var x=document.getElementById("selected_cards");
    var option=document.createElement("option");
    option.text=data;
    option.setAttribute('draggable','true');
    //option.setAttribute("ondblclick",'double_click_remove');
    x.add(option);
    var pos=document.getElementById('available_cards');
    pos.remove(pos.selectedIndex);
    //console.log(document.getElementById('available_cards').getElementsByTagName('option')[0]);
    generate_graph3();
  }
  else if(event.target.className=="main")
  {}
  else if(event.target.className=="site_content")
  {}
   else if(event.target.className=="")
  {}
  else
  {
    add_element();
    var x=document.getElementById("selected_cards");
    var option=document.createElement("option");
    option.text=data;
    option.setAttribute('draggable','true');
    //option.setAttribute("ondblclick",'double_click_remove');
    x.add(option);
    var pos=document.getElementById('available_cards');
    pos.remove(pos.selectedIndex);
    //console.log(document.getElementById('available_cards').getElementsByTagName('option')[0]);
    generate_graph3();
  }
  
})
document.addEventListener("dragover", function(event) {
    event.preventDefault();
});
function allowDrop(ev) {
    ev.preventDefault();
}

function analytics()
{
  var str='';
  for(var i=0;i<bought_cards_array.length;i++)
    for(var j=0;j<card_array.length;j++)
      if(bought_cards_array[i][0]==card_array[j][0])
      { //console.log("in if for card: "+bought_cards_array[i][0]);
        if(bought_cards_array[i][4]=='buy')
        { str+='<br>You have bought: '+bought_cards_array[i][2]+'x '+bought_cards_array[i][0];
          str+=" at: "+bought_cards_array[i][3]+' each.';
          str+=' Current price is: '+card_array[j][card_array[j].length-1];
        //console.log(card_array[j])
          str+=' If you sell now you will make: '+profit_or_loss(bought_cards_array[i][2],bought_cards_array[i][3],card_array[j][card_array[j].length-1],bought_cards_array[i][4]);
          str+='';
        }
        else
        { 
          str+="<br>You have sold: "+bought_cards_array[i][2]+'x '+bought_cards_array[i][0];
          str+=" at: "+bought_cards_array[i][3]+' each.';
          str+=' Current price is: '+card_array[j][card_array[j].length-1];
          str+=' If you rebuy now you will make: '+profit_or_loss(bought_cards_array[i][2],bought_cards_array[i][3],card_array[j][card_array[j].length-1],bought_cards_array[i][4]);
          str+='';
        }
      }  
      str+="<button onclick='analytics_menu()'>Back</button>";
      document.getElementById('analytics_info').innerHTML=str;
        //console.log(str);
}
function profit_or_loss(copies,buy,current,action)
{
  //console.log("copies: "+copies+" sell: "+buy+" current: "+current+" action: "+action);
  var ret='';
  var value;
  if(action=='buy')
    value=(current-buy)*copies;
  else
    value=(buy-current)*copies;
  var value=value.toFixed(2);
  if (value>0)
    ret+='<content class= "positive">';
  else
    ret+='<content class= "negative">';

  ret+=value+'</content><br>';
  return ret;
}
function my_bought_cards()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var size=cards.length;
      for (var i = 0; i<size; i++)
      { 
        var tmp_arr=[];
        tmp_arr.push(cards[i].getAttribute('name'));
        tmp_arr.push(cards[i].getAttribute('set'));
        tmp_arr.push(cards[i].getAttribute('copies'));
        tmp_arr.push(cards[i].getAttribute('price'));
        tmp_arr.push(cards[i].getAttribute('action'));
        bought_cards_array.push(tmp_arr);
      }
      fetch_XML(2);
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+7, true);
  xmlhttp.send();
}
function add_bought_card()
{
  var name,set,copies,price;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {};
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+8+"&name="+name+"&set="+set+"&price="+price+"&copies="+copies, true);
  xmlhttp.send();
}
function control_function(mode)
{
  var str='';
  //console.log(mode);

  if(mode==1)
  { str+="<table><tr><td>Card Name: </td><td><input id='cardname' type='text'></td><td></td></tr>";
    str+="<tr><td>Card Set: </td><td><input  id='setname' type='text' "+ /*onkeyup='show_hint(event,this.value)' */+"list='setlist'></td><td></td></tr>";
    //str+="</label><br>";
    str+="<datalist id='setlist' style='list-style-type:none'></datalist>";
    str+="<tr><td>Previous Prices: </td><td><input id='pricein' type='text'></td>";
    str+="<td><button onclick='add_price()'>+</button>";
    str+="<button onclick='pop_price()'>-</button> Prices to add: <label id='pricelbl'></label></td></tr>";
    str+="<tr><td><button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='add_card("+mode+")'>Add Card</button></td><td></td><td></td></tr></table>";
  }
  else if(mode==2)
  {
    str+="<table><tr><td>Card Name: </td><td><input id='cardname' type='text'></td></tr>";
    str+="<tr><td>Card Set: </td><td><input  id='setname' type='text'  "+ /*onkeyup='show_hint(event,this.value)' */+"list='setlist'></td></tr>";
    str+="<datalist id='setlist' style='list-style-type:none'></datalist>";
    str+="<tr><td>Quantity: </td><td><input id='copies' type='text'></td></tr>";
    str+="<tr><td>Price: </td><td><input id='price' type='text'></td></tr>";
    str+="<tr><td><button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='add_card("+mode+")'>Add Bought Card</button></td><td></td></tr></table>";
  }
  else
  {
    str+="<table><tr><td>Card Name: </td><td><input id='cardname' type='text'></td></tr>";
    str+="<tr><td>Card Set: </td><td><input  id='setname' type='text'  "+ /*onkeyup='show_hint(event,this.value)' */+"list='setlist'>";
    str+="<datalist id='setlist' style='list-style-type:none'></datalist>";
    str+="<tr><td>Quantity: </td><td><input id='copies' type='text'></td></tr>";
    str+="<tr><td>Price: </td><td><input id='price' type='text'></td></tr>";
    str+="<tr><td><button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='add_card("+mode+")'>Add Sold Card</button></td><td></td></tr></table>";
  }
  document.getElementById('analytics_info').innerHTML=str;
}
function back()
{
  var str='<br>';
  str+='<p>I want to: ';
  str+='<select onchange="control_function(this.value)">';
  str+='<option value="0" selected>---- Select an action ----</option>'
  str+='<option value="1">Add monitored card</option>';
  str+='<option value="2">Add bought card</option>';
  str+='<option value="3">Add sold card</option>';
  str+='</select></p>';
  document.getElementById('').innerHTML=str;
}
function compare_sell_trend()
{ console.log('call');
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var size=cards.length;
      str='';
      for (var i = 0; i<size; i++)
      {
        str+="I am selling: "+cards[i].getAttribute('name')+" for: "+cards[i].getAttribute('price');
        str+=" it is selling now for: "+cards[i].getAttribute('current')+' difference: ';
        str+=profit_or_loss(cards[i].getAttribute('copies'),cards[i].getAttribute('price'),cards[i].getAttribute('current'))+'<br>';
        
      }
      document.getElementById('analytics_info').innerHTML=str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+10, true);
  xmlhttp.send();
}
function edit_menu()
{
  var str='';
  str+='I want to: <select id="sel_option">';
  str+="<option value = '0' selected>--Choose an action--</option>";
  str+="<option value = '1'>Edit monitored cards</option>";
  str+="<option value = '2'>Edit transactions</option>";
  str+="<option value = '3'>Edit my offers</option>";
  str+="</select><br>";
  str+="<button onclick='next()'>Go</button>";
  document.getElementById('analytics_info').innerHTML=str;
}
function next()
{
  var sel=document.getElementById('sel_option');
  var str='';
  console.log(sel.selectedIndex);
  if(sel.selectedIndex==0)
    return;
  else if(sel.selectedIndex==1)
  {
    str+="<select id='monitored_options'>";
    str+="<option value='0' selected>--Choose an action--</option>";
    str+="<option value='1'>Add monitored Card</option>";
    str+="<option value='2'>Remove monitored Card</option>";
    str+="<option value='3'>Erase latest Values</option>";
    str+="<select><br>";
    str+="<button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='next2_1()'>Go</button>";
    document.getElementById('analytics_info').innerHTML=str;
  }
  else if(sel.selectedIndex==2)
  {
    str+="<select id='monitored_options'>";
    str+="<option value='0' selected>--Choose an action--</option>";
    str+="<option value='1'>Add bought Card</option>";
    //str+="<option value='2'>Remove bought Card</option>";
    str+="<option value='2'>Add Sold Card</option>";
    str+="<option value='3'>Remove bought/sold card</option>";
    str+="<select><br>";
    str+="<button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='next2_2()'>Go</button>";
    document.getElementById('analytics_info').innerHTML=str;
  }
  else
  {
    str+="<select id='monitored_options'>";
    str+="<option value='0' selected>--Choose an action--</option>";
    str+="<option value='1'>Add offer</option>";
    str+="<option value='2'>Remove offer</option>";
    str+="<option value='3'>Edit offer</option>";
    str+="<select><br>";
    str+="<button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='next2_3()'>Go</button>";
    document.getElementById('analytics_info').innerHTML=str;
  }
}
function next2_1()
{ //manage monitored_cards
  var sel=document.getElementById('monitored_options');
  var str='';
  if(sel.selectedIndex==0)
    return;
  else if(sel.selectedIndex==1)
    control_function(1);
  else if(sel.selectedIndex==2)
    get_cards();
  else if(sel.selectedIndex==3)
    erase_latest();
  else
  {}
}
function next2_2()
{ //manage bought/sold_cards
  var sel=document.getElementById('monitored_options');
  var str='';
  console.log('next2_2');
  if(sel.selectedIndex==0)
    return;
  else if(sel.selectedIndex==1)
    control_function(2);
  //else if(sel.selectedIndex==2)
   // get_bought_cards('buy');
  else if(sel.selectedIndex==2)
    control_function(3);
  else if(sel.selectedIndex==3)
    get_bought_cards();
  else{}
}
function next2_3()
{ //manage selling cards
  var sel=document.getElementById('monitored_options');
  var str='';
  if(sel.selectedIndex==0)
    return;
  else if(sel.selectedIndex==1)
    add_selling_card();
  else if(sel.selectedIndex==2)
    select_selling_card();
   else if(sel.selectedIndex==3)
    edit_selling_card();  
  else{}
}
function add_selling_card()
{   var str='';
    str+="<table><tr><td>Card Name: </td><td><input id='cardname' type='text'></td></tr>";
    str+="<tr><td>Card Set: </td><td><input  id='setname' type='text'  "+ /*onkeyup='show_hint(event,this.value)' */+"list='setlist'><td/><tr>";
    str+="<datalist id='setlist' style='list-style-type:none'></datalist>";
    str+="<tr><td>Quantity: </td><td><input id='copies' type='text'></td></tr>";
    str+="<tr><td>Price: </td><td><input id='price' type='text'></td></tr>";
    str+="<tr><td><button onclick='edit_menu()'>Back</button>";
    str+="<button onclick='add_card("+4+")'>Add Offer</button></td><td></td></tr></table>";
    document.getElementById('analytics_info').innerHTML=str;
}
function select_selling_card()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='List of cards: <select id="del_card_sel2">';
      var size=cards.length;
      for (var i = 0; i<size; i++)
      { 
        str+='<option value='+cards[i].getAttribute('id')+'>'+cards[i].getAttribute('name')+'</option>';
      }
      str+='</select><br><br>';
      str+='<input id="update_sold" type="checkbox" value="">Add to sold cards<br>';
      str+='<button  class="submit" onclick="edit_menu()">Back</button>';
      str+='<button class = "submit" onclick="remove_selling_card()">Delete</button>';
      document.getElementById("analytics_info").innerHTML = str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+10, true);
  xmlhttp.send();
}
function remove_selling_card()
{
  var sel=document.getElementById('del_card_sel2');
  var to_del=sel.options[sel.selectedIndex].value;
  var chkbox=document.getElementById('update_sold');
  var update;
  if(chkbox.checked)
    update=1;
  else
    update=0;
  //console.log(update);
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      select_selling_card();
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+13+"&id="+to_del+"&update="+update, true);
  xmlhttp.send();
}
function edit_selling_card()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      cards=replydata.documentElement.getElementsByTagName('card');
      //var o=document.createElement('<select id="del_card_sel2">');
      //o.innerHTML
      
      var str='List of cards: <select id="del_card_sel2">';
      var size=cards.length;
      for (var i = 0; i<size; i++)
      { 
        str+='<option value='+cards[i].getAttribute('id')+'>'+cards[i].getAttribute('name')+'</option>';
      }
      str+='</select><br><br>';
      str+='<button  class="submit" onclick="edit_menu()">Back</button>';
      str+='<button class = "submit" onclick="edit_offer()">Edit</button>';
      document.getElementById("analytics_info").innerHTML = str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+10, true);
  xmlhttp.send();
}
function edit_offer()
{
  var str='';
  var sel=document.getElementById('del_card_sel2');
  var e=sel.selectedIndex;
  to_edit=sel.options[sel.selectedIndex].value;
  console.log(e);
  
  str+="<table>";
  str+="<tr><th>Stored</th><th>New</th></tr>";
  str+="<tr><td>Name: <label id='oname'>"+cards[e].getAttribute('name')+"</label></td><td><input id='cname'></td></tr>";
  str+="<tr><td>Set: <label id='oset'>"+cards[e].getAttribute('set')+"</label></td><td><input id='cset'></td></tr>";
  str+="<tr><td>Quantity: <label id='ocopies'>"+cards[e].getAttribute('copies')+"</label></td><th><input id='ccopies'></td></tr>";
  str+="<tr><td>Price: <label id='oprice'>"+cards[e].getAttribute('price')+"</label></td><td><input id='cprice'></td></tr>";
  str+="</table><br><button onclick='edit()'>Edit</button>";
  document.getElementById('analytics_info').innerHTML=str;
}
function edit()
{
  var sel=document.getElementById('del_card_sel2');
  //to_edit=sel.options[sel.selectedIndex].value;
  var name=document.getElementById('cname').value;
  var set=document.getElementById('cset').value;
  var copies=document.getElementById('ccopies').value;
  var price=document.getElementById('cprice').value;
  if(name==''&&set==''&&copies==''&&price=='')
    return;
  if (name=='')
    name=document.getElementById('oname').innerHTML;
  if (set=='')
    set=document.getElementById('oset').innerHTML;
  if (copies=='')
    copies=document.getElementById('ocopies').innerHTML;
  if (price=='')
    price=document.getElementById('oprice').innerHTML;
  if(price%1===0)
    price=price+'.0';
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
       edit_selling_card();
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+14+"&name="+name+"&set="+set+"&price="+price+"&copies="+copies+"&price="+price+"&id="+to_edit, true);
  xmlhttp.send();
  //console.log(name+set+copies+price);
}
function erase_latest()
{
  var str='';

  str+="Press this to erase last entry in daily updated cards: <button onclick='erase()''>Erase</button>";
  document.getElementById('analytics_info').innerHTML=str;
}
function erase()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
       edit_menu();
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+15, true);
  xmlhttp.send();
}
function displaydata()
{
  var sel=document.getElementById('data_list');
  var e=sel.selectedIndex;
  //console.log(e);
  if(e==1)
    my_bought_cards();  
  else if(e==2)
    my_offers();
  else{}
}
function my_offers()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='';
      var size=cards.length;
      for (var i = 0; i<size; i++)
      { 
        str+='You are selling: '+cards[i].getAttribute('name')+' at: '+cards[i].getAttribute('price');
        str+=' it is now selling for: '+cards[i].getAttribute('current')+"<br>";
      }
      str+="<button onclick='analytics_menu()'>Back</button>";
      document.getElementById("analytics_info").innerHTML = str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+11, true);
  xmlhttp.send();
}
function analytics_menu()
{
  var str='';
  str+="Select which analysis to view:";
  str+="<select id='data_list'>";
  str+="<option value='0'>-------Choose one-------</option>";
  str+="<option value='1'>Data for bought/sold cards</option>";
  str+="<option value='2'>Data for offers</option>";
  str+="</select>";
  str+="<button onclick='displaydata()'>Go</button>"; 
  document.getElementById('analytics_info').innerHTML=str;
}
function view_data()
{
  var str='Select an action';
  str+="<select id='data_list_2'>";
  str+="<option value='0'>----------Choose one----------</option>";
  str+="<option value='1'>View data for monitored cards</option>";
  str+="<option value='2'>View offers</option>";
  str+="<option value='3'>View bought/sold cards</option>";
  str+="/<select>";
  str+="<button onclick=view_menu()>View</button>";
  document.getElementById('curve_chart').innerHTML=str;
}
function view_menu()
{
  var sel=document.getElementById('data_list_2');
  var e=sel.selectedIndex;
  if (e==0)
    return;
  else if(e==1)
    fetch_XML(1);
  else if(e==2)
    view_offers();
  else if(e==3)
    view_bought_sold();
}
function view_offers()
{ 
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='';
      str+='<table>';
      str+="<tr><th>Card name</th><th>Card Set</th><th>Copies</th><th>Price</th></tr>"
      for(var i=0;i<cards.length;i++)
      {
        str+="<tr><td>"+cards[i].getAttribute('name')+"</td><td>"+cards[i].getAttribute('set')+"</td>";
        str+="<td style='text-align:center'>"+cards[i].getAttribute('copies')+"</td><td>"+cards[i].getAttribute('price')+"</td></tr>";
      }
      str+="</table>";
      str+="<button onclick='view_data()'>Back</button>"
      document.getElementById('curve_chart').innerHTML=str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+10, true);
  xmlhttp.send();
}
function view_bought_sold()
{
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
      var replydata=xmlhttp.responseXML;
      var cards=replydata.documentElement.getElementsByTagName('card');
      var str='';
      str+='<table>';
      str+="<tr><th>Card name</th><th>Card Set</th><th>Copies</th><th>Price</th><th>Action</th></tr>"
      for(var i=0;i<cards.length;i++)
      {
        str+="<tr><td>"+cards[i].getAttribute('name')+"</td><td>"+cards[i].getAttribute('set')+"</td>";
        str+="<td style='text-align:center'>"+cards[i].getAttribute('copies')+"</td><td>"+cards[i].getAttribute('price')+"</td>";
        str+="<td>"+cards[i].getAttribute('action')+"</td></tr>";
      }
      str+="</table>";
      str+="<button onclick='view_data()'>Back</button>"
      document.getElementById('curve_chart').innerHTML=str;
    }
  };
  xmlhttp.open("GET", "http://localhost/tests_folder/price_trend_getter/functions.php?mode="+7, true);
  xmlhttp.send();
}
