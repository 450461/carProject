function showBorder($id){
	document.getElementById($id).style.border='1px dotted gray';
};
function fontWeightBold($id){
	document.getElementById($id).style.fontWeight='bold';
};
function hideBorder($id){
	document.getElementById($id).style.border='1px solid white';
}
function fontWeightNormal($id){
	document.getElementById($id).style.fontWeight='normal';
};
function onOff($target, $hidden){
	$txt=document.getElementById($target);
	$hiddenTxt=document.getElementById($hidden);
	if ($txt.style.display=='none'){
		$txt.style.display='block';
		$hiddenTxt.innerHTML='Скрыть подробности.';		
	}else{
		$txt.style.display='none';
		$hiddenTxt.innerHTML='Показать подробности ..';		
	};
}
$(function () {
   var body = $("body");
    var previousWidth = null;
    // Function that applies padding to the body
   // to adjust its position.
   var resizeBody = function () {
       var currentWidth = body.width();
       if (currentWidth != previousWidth) {
           previousWidth = currentWidth;
            // Measure the scrollbar size
           body.css("overflow", "hidden");
           var scrollBarWidth = body.width() - currentWidth;
           body.css("overflow", "auto");
            body.css("margin-left", scrollBarWidth + "px");
       }
   };
    // setInterval is required because the resize event
   // is not fired when a scrollbar appears or disappears.
   setInterval(resizeBody, 100);
   resizeBody();
});
   function validateForm($formName, $errorDiv){
       //alert('ok');
       var arr= [];
       for (var i=0; i<document.forms.greenForm.elements.length; i++){
           if (greenForm.elements[i].value==''){
           //    arr[i]=greenForm.elements[i].value;  
               alert(greenForm.elements[i].value);        
           }
     
      // alert(arr.length);
/*       if (arr.length>2){
          // document.getElementById.($errorDiv).innderHTML='123'; 
           alert('в массиве чтото есть ');
       }else{
           alert('массив пустой');
       } 
*/               
   }
   }