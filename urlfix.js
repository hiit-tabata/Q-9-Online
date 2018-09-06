//IMUS url redirect system for website under iframe and redirected subdomains using javascript.
console.log("IMUS urlfix.js working in process...");
var dirshort = "q9";
var url = (window.location != window.parent.location)
            ? document.referrer
            : document.location.href;
if (url.includes("/" + dirshort + "/") == false){ 
//Switch to sub-domain system
console.log("Subdomain system not found. Coverting to subdomain system.");
top.window.location.href = "http://" + dirshort + ".imuslab.com";
}else{
console.log("Handling by subdomain redirection system. DONE");	
}
