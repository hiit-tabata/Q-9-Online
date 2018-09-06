<html>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1"/>
<meta charset = "utf-8">
<meta name="mobile-web-app-capable" content="yes">

<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<?php
$id = -1;
if (isset($_GET['id']) && $_GET['id'] != ""){
	$id = $_GET['id'];
	echo '<title>Q/9 Client ' . $id . '</title>';
}
$externalContent = file_get_contents('http://checkip.dyndns.com/');
preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
$externalIp = $m[1];
?>
</head>
<body style="margin: 10px;">
<div class="ts grid">
    <div id="7" class="five wide column" style="height:21%;border: 3px solid #848484;">7</div>
    <div id="8" class="five wide column" style="height:21%;border: 3px solid #848484;">8</div>
    <div id="9" class="five wide column" style="height:21%;border: 3px solid #848484;">9</div>
	<div id="4" class="five wide column" style="height:21%;border: 3px solid #848484;">4</div>
    <div id="5" class="five wide column" style="height:21%;border: 3px solid #848484;">5</div>
    <div id="6" class="five wide column" style="height:21%;border: 3px solid #848484;">6</div>
	<div id="1" class="five wide column" style="height:21%;border: 3px solid #848484;">1</div>
    <div id="2" class="five wide column" style="height:21%;border: 3px solid #848484;">2</div>
    <div id="3" class="five wide column" style="height:21%;border: 3px solid #848484;">3</div>
	<div id="0" class="ten wide column" style="height:28%;border: 3px solid #848484;">0</div>
    <div id="dot" class="five wide column" style="height:28%;border: 3px solid #848484;">.</div>
</div>
<div style="position: fixed;
    z-index: 100; 
    bottom: 0; 
    left: 0;
    width: 100%;" align="right">
Experimental Project from IMUS Laboratory<br>Some Right Reserved.
</div>
<script>
var serverIP = "<?php echo $externalIp;?>";
var RemoteID = "<?php echo $id;?>";
var ws = new WebSocket('ws://'+serverIP+':1010/');
toggleFullScreen();

$("#1").click(function(){
    wss(1);
}); 

$("#2").click(function(){
    wss(2);
}); 

$("#3").click(function(){
    wss(3);
}); 

$("#4").click(function(){
    wss(4);
}); 

$("#5").click(function(){
    wss(5);
}); 

$("#6").click(function(){
    wss(6);
}); 

$("#7").click(function(){
    wss(7);
}); 

$("#8").click(function(){
    wss(8);
}); 

$("#9").click(function(){
    wss(9);
}); 

$("#0").click(function(){
    wss(0);
}); 

$("#dot").click(function(){
    wss("dot");
}); 

function wss(id){
	ws.send("Q/9_" + RemoteID + "_" + id);
	console.log("Q/9_" + RemoteID + "_" + id);
}

ws.addEventListener('open', function (event) {
    ws.send("Q/9_" + RemoteID + '_connect');
});

ws.onmessage = function(event) {
  console.log('reflex:' + event.data);
  var content = event.data;
}

function toggleFullScreen() {
  var doc = window.document;
  var docEl = doc.documentElement;

  var requestFullScreen = docEl.requestFullscreen || docEl.mozRequestFullScreen || docEl.webkitRequestFullScreen || docEl.msRequestFullscreen;
  var cancelFullScreen = doc.exitFullscreen || doc.mozCancelFullScreen || doc.webkitExitFullscreen || doc.msExitFullscreen;

  if(!doc.fullscreenElement && !doc.mozFullScreenElement && !doc.webkitFullscreenElement && !doc.msFullscreenElement) {
    requestFullScreen.call(docEl);
  }
  else {
    cancelFullScreen.call(doc);
  }
}

</script>
</body>
</html>