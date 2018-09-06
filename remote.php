<html>
<head>
<meta charset = "utf-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="jquery.qrcode.min.js"></script>
<style>
  #q9display {
    position: fixed;
    bottom: 0;
    right: 0;
  }
</style>
<title>Q/9 Remote Control Interface</title>
<?php
$externalContent = file_get_contents('http://checkip.dyndns.com/');
preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
$externalIp = $m[1];
?>
</head>
<body>
<div class="ts container">

<div class="ts heading slate">
    <span class="header">Q/9 Remote Input Experimental Interface</span>
    <span class="description">
	Remote ID:
	<div class="ts horizontal statistic">
		<div id="RemoteID" class="value" style="color:red;">Loading...</div>
	</div>
	</span>
	<span class="description" style="color: black;">
	Alternative Input Mode:
	<div class="ts label" style="color: black;">
		<a href="index.php"><i class="desktop icon"></i>Destop Keypad</a>
	</div>
	<div class="ts label" style="color: black;">
		<a href="keyboard.php"><i class="laptop icon"></i>Notebook Keyboard</a>
	</div>
	<div class="ts label" style="color: black;">
		<a href="minterface.php"><i class="mobile icon"></i>手機數字鍵盤界面</a>
	</div>
	<div id="qrcode"></div>
	</span>
</div>
<!-- explain how this works -->
<div class="ts fluid steps">
    <div class="step">
        <i class="hashtag icon"></i>
        <div class="content">
            <div class="title">Enter Remote ID</div>
            <div class="description">在手機上輸入 Remote ID</div>
        </div>
    </div>
    <div class="step">
        <i class="wifi icon"></i>
        <div class="content">
            <div class="title">Connect</div>
            <div class="description">按 Connect 然後等待連接成功</div>
        </div>
    </div>
    <div class="step">
        <i class="keyboard icon"></i>
        <div class="content">
            <div class="title">Type</div>
			<div class="description">然後就可以開始用手機當數字鍵盤打字了</div>
        </div>
    </div>
</div>

<div class="ts segment">
<div id="textbox" class="ts text container" style="width:100%;">
<div class="field" style="width:100%">
<textarea id="inputbox" type="text" name="output" rows="15" style="width: 100%"></textarea>
</div>
</div>
</div>
<div id="debug" class="ts text container">
</div>
<div id="db">
<?php
$fh = fopen('utf8db.txt','r');
while ($line = fgets($fh)) {
  echo($line);
}
fclose($fh);
?>
</div>
<div id="assodb">
<?php
$fh = fopen('utf8asso.txt','r');
while ($line = fgets($fh)) {
  echo($line);
}
fclose($fh);
?>
</div>
<div id="q9display" style="width: 180px; height:295px; background-color: #131c56; background-image: url('interface/shell.png');" >
<canvas id="q9canvas" width="180" height="295">HTML5 Canvas not supported.</canvas>
</div>
<div id="textures">
<?php
$texturedir = 'interface/';
$files = scandir($texturedir);
foreach($files as $file) {
    if($file == '.' || $file == '..') continue;
	if (strpos($file,".png") !== False){//if it is a png file
    //print $file . '<br>';
	$filename = str_replace(".png","",$file);
	$filedir = $texturedir . $file;
	print '<img id="'.$filename.'" width="16" height="20" src="'.$filedir.'">';
	}
}
?>
</div>
</div>
<script>
var preword = ""
var assodb = document.getElementById('assodb').innerHTML.split("0");
var associatedarr = [];
var keycodes = "";
var selectwordmode = false;
var dbtext = document.getElementById('db').innerHTML; //All database info was stored here
var keyblocks = dbtext.split('],'); //Split database info into diffferent number blocks
var layer = 0; //Interface layer, 0 as index
var currentblock = "";
var worddata = [];
var arrindex = 0; //The place to start displaying the block array
var displayarr = [];//The array that show on screen
//Remote Control Functions
var RemoteID = 0;
var serverIP = "<?php echo $externalIp;?>";
var connected = false;

$(document).ready(function(){
   $("#db").hide();  
   $("#textures").hide(); 
   $("#assodb").hide(); 
   drawinterface('s');
   
   //Adding new scripts here for Remote Control Unit
   
   var id = Math.random() * 10000;
   id = parseInt(id);
   $('#RemoteID').html(id);
   RemoteID = id;
   $('#qrcode').qrcode("http://" + serverIP + ":8080/q9/minput.php?id=" + RemoteID);
});

//Remote control stuffs via websocket
var ws = new WebSocket('ws://'+serverIP+':1010/');

ws.onmessage = function(event) {
  console.log('IMUS_Datapath:' + event.data);
  var content = event.data;
  if (content.includes("Q/9") && content.includes(RemoteID)){
	//This command is send to this interface  
	if(content.includes("connect")){
		//The other side has been connected.
		$('#RemoteID').css('color', '#49ff9e');
		connected = true;
		$('#qrcode').hide();
	}else{
		//This is button command I think?
		//console.log(content);
		var items = content.split("_");
		var code = items[2];
		console.log(code);
		
		//The following part is for replacing the keypress event.
		switch(code) {
        case '0': // Keypad 0
		if (layer<3 && keycodes != "09" && keycodes != "0" && associatedarr.length == 0){
			//Selecting nothing as 2nd part or punchuations
			keycodes += "0";
			layer+=1;
			break;
		}else if (layer == 0 && associatedarr.length > 0){
			//User want to select associated words
			drawfinalreltexts(associatedarr);
			break;
		}else{
			//Next page in searching for word
			arrindex += 9
			console.log("Next Page");
			break;
		}
		case '1': // Keypad 1
		if (layer < 3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "1";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[7]);
			var tmptxt = associatedarr[7]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			//Select the word using array indexing
			selectword(7);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '2': // Keypad 2
		if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "2";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[8]);
			var tmptxt = associatedarr[8]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(8);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '3': // Keypad 3
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "3";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[9]);
			var tmptxt = associatedarr[9]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(9);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '4': // Keypad 4
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "4";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[4]);
			var tmptxt = associatedarr[4]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(4);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '5': // Keypad 5
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "5";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[5]);
			var tmptxt = associatedarr[5]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(5);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '6': // Keypad 6
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "6";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[6]);
			var tmptxt = associatedarr[6]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(6);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '7': // Keypad 7
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "7";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[1]);
			var tmptxt = associatedarr[1]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(1);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '8': // Keypad 8
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "8";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[2]);
			var tmptxt = associatedarr[2]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(2);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case '9': // Keypad 9
			if (layer<3 && keycodes != "09" && selectwordmode == false){
			keycodes += "9";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[3]);
			var tmptxt = associatedarr[3]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(3);
			keycodes = "";
			layer = 0;
			break;
		}
		case 13: // Keypad Enter
			settext('Enter pressed');
			break;
		case "dot": // Keypad dot
			settext('Dot pressed');
			if (keycodes != "" || selectwordmode == true){
				keycodes = "";
				layer = 0;
				arrindex = 0;
				preword = "";
				selectwordmode = false;
				associatedarr = [];
				preword = "";
			}else if (preword != "" && associatedarr.length > 0){
				clearmemory();
				keycodes += "0";
				layer+=1;
				break;
			}
			break;
		case 8: //Backspace
		  //e.preventDefault();
		  //$('#textbox').html(document.getElementById('textbox').innerHTML.substring(0, document.getElementById('textbox').innerHTML.length-1));
		break; 
		default:
			return;
    }
	//Debugs
	/*
	console.log("Keycodes: " + keycodes);
	console.log("Layer: " + layer);
	console.log("Array Shifting: " + arrindex);
	console.log("Associated Array: " + associatedarr);
	console.log("Previous word: " + preword);
	*/
	checkinterface();
	if (dbtext.includes('"' + keycodes + '"') == true && keycodes != ""){
		//alert("true");
		keyblocks.forEach(checkkeyblock)
	}else{
		settext(keycodes);
	}
	
	}
  }
};




//Canvas handling...stuffs?
var canvas = document.getElementById("q9canvas");
var ctx = canvas.getContext("2d");

function drawtexts(dataarr){
	if (dataarr[arrindex + 1] != undefined){
		console.log('moving to next page');
	}else{
		arrindex = 0;
	}
		ctx.font = "30px Arial";
		ctx.fillText(dataarr[arrindex + 1],18,72);
		ctx.fillText(dataarr[arrindex + 2],70,72);
		ctx.fillText(dataarr[arrindex + 3],120,72);
		ctx.fillText(dataarr[arrindex + 4],18,122);
		ctx.fillText(dataarr[arrindex + 5],70,122);
		ctx.fillText(dataarr[arrindex + 6],120,122);
		ctx.fillText(dataarr[arrindex + 7],18,172);
		ctx.fillText(dataarr[arrindex + 8],70,172);
		ctx.fillText(dataarr[arrindex + 9],120,172);
}

function drawreltexts(lasttext){
		var findass = false;
		var associate = [];
		assodb.forEach(function(item){
			var words = item.split("");
			if (item[0] == lasttext){
				//There is associated words
				//console.log("Associated Words: " + item.split(""));
				findass = true;
				associate = words;
			}
			
		});
		
		if (associate[7] == undefined){
			associate[7] = "個";
		}
		if (associate[8] == undefined){
			associate[8] = "能";
		}
		if (associate[9] == undefined){
			associate[9] = "的";
		}
		if (associate[4] == undefined){
			associate[4] = "到";
		}
		if (associate[5] == undefined){
			associate[5] = "資";
		}
		if (associate[6] == undefined){
			associate[6] = "就";
		}
		if (associate[1] == undefined){
			associate[1] = "你";
		}
		if (associate[2] == undefined){
			associate[2] = "這";
		}
		if (associate[3] == undefined){
			associate[3] = "好";
		}
		
		if (findass == true){
		drawinterface(String('c'));
		ctx.font = "26px Arial";
		ctx.fillText(associate[7],12,62);
		ctx.fillText(associate[8],60,62);
		ctx.fillText(associate[9],110,62);
		ctx.fillText(associate[4],12,110);
		ctx.fillText(associate[5],60,110);
		ctx.fillText(associate[6],110,110);
		ctx.fillText(associate[1],12,158);
		ctx.fillText(associate[2],60,158);
		ctx.fillText(associate[3],110,158);
		associatedarr = ["NULL",associate[7],associate[8],associate[9],associate[4],associate[5],associate[6],associate[1],associate[2],associate[3]];
		//console.log("Associated Array: " + associatedarr);
		}
}

function drawfinalreltexts(dataarr){
		selectwordmode = true;
		ctx.font = "30px Arial";
		drawinterface('e');
		ctx.fillText(dataarr[1],18,72);
		ctx.fillText(dataarr[2],70,72);
		ctx.fillText(dataarr[3],120,72);
		ctx.fillText(dataarr[4],18,122);
		ctx.fillText(dataarr[5],70,122);
		ctx.fillText(dataarr[6],120,122);
		ctx.fillText(dataarr[7],18,172);
		ctx.fillText(dataarr[8],70,172);
		ctx.fillText(dataarr[9],120,172);
}
function drawinterface(num){
	var img=document.getElementById("q9_" + num);
    ctx.drawImage(img,3,30);
}

function clearmemory(){
	keycodes = "";
	layer = 0;
	arrindex = 0;
	preword = "";
	selectwordmode = false;
	associatedarr = [];
	preword = "";
}

$(document).keydown(function(e) {
    switch(e.which) {
        case 96: // Keypad 0
		e.preventDefault();
		if (layer<3 && keycodes != "09" && keycodes != "0" && associatedarr.length == 0){
			//Selecting nothing as 2nd part or punchuations
			keycodes += "0";
			layer+=1;
			break;
		}else if (layer == 0 && associatedarr.length > 0){
			//User want to select associated words
			drawfinalreltexts(associatedarr);
			break;
		}else{
			//Next page in searching for word
			arrindex += 9
			console.log("Next Page");
			break;
		}
		case 97: // Keypad 1
		e.preventDefault();
		if (layer < 3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "1";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[7]);
			var tmptxt = associatedarr[7]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			//Select the word using array indexing
			selectword(7);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 98: // Keypad 2
		e.preventDefault();
		if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "2";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[8]);
			var tmptxt = associatedarr[8]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(8);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 99: // Keypad 3
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "3";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[9]);
			var tmptxt = associatedarr[9]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(9);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 100: // Keypad 4
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "4";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[4]);
			var tmptxt = associatedarr[4]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(4);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 101: // Keypad 5
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "5";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[5]);
			var tmptxt = associatedarr[5]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(5);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 102: // Keypad 6
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "6";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[6]);
			var tmptxt = associatedarr[6]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(6);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 103: // Keypad 7
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "7";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[1]);
			var tmptxt = associatedarr[1]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(1);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 104: // Keypad 8
		e.preventDefault();
			if (layer<3 && keycodes != "09" && keycodes != "0" && selectwordmode == false){
			keycodes += "8";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[2]);
			var tmptxt = associatedarr[2]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(2);
			keycodes = "";
			arrindex = 0;
			layer = 0;
			break;
		}
		case 105: // Keypad 9
		e.preventDefault();
			if (layer<3 && keycodes != "09" && selectwordmode == false){
			keycodes += "9";
			layer+=1;
			associatedarr = [];
			break;
		}else if(selectwordmode == true){
			addtext(associatedarr[3]);
			var tmptxt = associatedarr[3]
			clearmemory();
			preword = tmptxt;
			break;
		}else{
			selectword(3);
			keycodes = "";
			layer = 0;
			break;
		}
		case 13: // Keypad Enter
			settext('Enter pressed');
			break;
		case 110: // Keypad dot
		e.preventDefault();
			settext('Dot pressed');
			if (keycodes != "" || selectwordmode == true){
				keycodes = "";
				layer = 0;
				arrindex = 0;
				preword = "";
				selectwordmode = false;
				associatedarr = [];
				preword = "";
			}else if (preword != "" && associatedarr.length > 0){
				clearmemory();
				keycodes += "0";
				layer+=1;
				break;
			}
			break;
		case 8: //Backspace
		  //e.preventDefault();
		  //$('#textbox').html(document.getElementById('textbox').innerHTML.substring(0, document.getElementById('textbox').innerHTML.length-1));
		break; 
		default:
			return;
    }
	//Debugs
	console.log("Keycodes: " + keycodes);
	console.log("Layer: " + layer);
	console.log("Array Shifting: " + arrindex);
	console.log("Associated Array: " + associatedarr);
	console.log("Previous word: " + preword);
	checkinterface();
	if (dbtext.includes('"' + keycodes + '"') == true && keycodes != ""){
		//alert("true");
		keyblocks.forEach(checkkeyblock)
	}else{
		settext(keycodes);
	}
	
});

function checkinterface(){
	if (keycodes == "" && selectwordmode == false){
		drawinterface('s');
		if (preword != ""){
			//If the user selected a word before, let user choose an ass. word
			drawreltexts(preword);
		}
	}else if (keycodes.length == 1 && keycodes != '0'){
		//Selecting the 1st part of the word
		var interfaceno = keycodes.substring(0,1);
		console.log("Changing Interface To: " + interfaceno);
		drawinterface(String(interfaceno));
	}else if (keycodes.length == 2 &&  keycodes != '09'){
		drawinterface(String('n'));
	}else if (keycodes.length == 3){
		//Words selections
		drawinterface(String('e'));
	}else if (keycodes == '0' || keycodes == '09'){
		drawinterface(String('e'));
	}
}


function selectword(btn){
	addtext(worddata[btn + arrindex]);
	//console.log(worddata[btn + arrindex]);
		
	
}


function checkkeyblock(block){
	if (block.includes('"' + keycodes + '":[') == true){
		settext(block);
		block = block.replace(":[",",");
		block = block.substring(0,block.length -1);
		worddata = block.split('","');
		drawtexts(worddata);
		//console.log(worddata);
		return;
	}
	
}

function settext(text){
	$('#debug').html(text);
	console.log(text);
}
function addtext(text){
	if (text.includes('"')){
		text = text.replace('"','');
	}
	insertAtCaret("inputbox",text)
	preword = text;
	//$("#inputbox").val(document.getElementById("inputbox").value + text);
	//$('#textbox').html(document.getElementById('textbox').innerHTML + text);
}

function insertAtCaret(areaId, text) {
	var txtarea = document.getElementById(areaId);
	if (!txtarea) { return; }

	var scrollPos = txtarea.scrollTop;
	var strPos = 0;
	var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
		"ff" : (document.selection ? "ie" : false ) );
	if (br == "ie") {
		txtarea.focus();
		var range = document.selection.createRange();
		range.moveStart ('character', -txtarea.value.length);
		strPos = range.text.length;
	} else if (br == "ff") {
		strPos = txtarea.selectionStart;
	}

	var front = (txtarea.value).substring(0, strPos);
	var back = (txtarea.value).substring(strPos, txtarea.value.length);
	txtarea.value = front + text + back;
	strPos = strPos + text.length;
	if (br == "ie") {
		txtarea.focus();
		var ieRange = document.selection.createRange();
		ieRange.moveStart ('character', -txtarea.value.length);
		ieRange.moveStart ('character', strPos);
		ieRange.moveEnd ('character', 0);
		ieRange.select();
	} else if (br == "ff") {
		txtarea.selectionStart = strPos;
		txtarea.selectionEnd = strPos;
		txtarea.focus();
	}

	txtarea.scrollTop = scrollPos;
}
</script>

</body>
</html>