<?php

/**

 * Rotator front page theme.

 *

 * @package WordPress

 * @subpackage Expand2Web SmallBiz

 * @since Expand2Web SmallBiz 3.3

 */



 $hideSidebar = true;

?> 

	

	

<!--[if IE 7]>

<style type="text/css">

#slideshow {

float:left;

margin-left: 10px;

width:350px;

height:225px;

margin-top:20px;}

 

#business {

width:560px;

padding-left:11px;

float:left;

}



</style>

<![endif]--> 



<!--[if IE 8]>

<style type="text/css">

#slideshow {

margin-left: 0px;

width:350px;

height:225px;

margin-top:20px;}

 

#business {

width:520px;

padding-left:11px;

float:right;

}





#devider{

margin-top:260px;

}

</style>

<![endif]--> 

	



		

<!--IMAGE ROTATOR START-->



<div id="wraptop">



<div id="slideshow">

				

<script language="JavaScript1.2">



//Presentational Slideshow Script- By Dynamic Drive

//For full source code and more DHTML scripts, visit http://www.dynamicdrive.com

//This credit MUST stay intact for legal use



var slideshow_width='320px' //SET SLIDESHOW WIDTH (set to largest image's width if multiple dimensions exist)

var slideshow_height='215px' //SET SLIDESHOW HEIGHT (set to largest image's height if multiple dimensions exist)

var pause=3000 //SET PAUSE BETWEEN SLIDE (2000=2 seconds)

var slidebgcolor="white"



var dropimages=new Array();

var droplinks=new Array()



<?php 

$imageJS = "";

$linkJS  = "";

for($i = 0; $i < 5; $i++){

    if((biz_option('smallbiz_rotator_imgs'.($i+1)))){

        $imageJS .= 'dropimages['.$i.']="' . biz_option('smallbiz_rotator_imgs'.($i+1)) .'"'.";\n";

    }

    if((biz_option('smallbiz_rotator_lks'.($i+1)))){

        $linkJS  .= 'droplinks['.$i.'] ="' . biz_option('smallbiz_rotator_lks'.($i+1))  .'"'.";\n";

    }

}

?>

//SET IMAGE PATHS. Extend or contract array as needed

<?php echo $imageJS; ?>



//SET IMAGE URLs. Use "" if you wish particular image to NOT be linked:

<?php echo $linkJS; ?>







////NO need to edit beyond here/////////////



var preloadedimages=new Array()

for (p=0;p<dropimages.length;p++){

preloadedimages[p]=new Image()

preloadedimages[p].src=dropimages[p]

}



var ie4=document.all

var dom=document.getElementById



if (ie4||dom)

document.write('<div style="position:relative;width:'+slideshow_width+';height:'+slideshow_height+';overflow:hidden"><div id="canvas0" style="position:absolute;width:'+slideshow_width+';height:'+slideshow_height+';background-color:'+slidebgcolor+';left:-'+slideshow_width+'"></div><div id="canvas1" style="position:absolute;width:'+slideshow_width+';height:'+slideshow_height+';background-color:'+slidebgcolor+';left:-'+slideshow_width+'"></div></div>')

else

document.write('<a href="javascript:rotatelink()"><img name="defaultslide" src="'+dropimages[0]+'" border=0></a>')



var curpos=parseInt(slideshow_width)*(-1)

var degree=10

var curcanvas="canvas0"

var curimageindex=linkindex=0

var nextimageindex=1





function movepic(){

if (curpos<0){

curpos=Math.min(curpos+degree,0)

tempobj.style.left=curpos+"px"

}

else{



clearInterval(dropslide)

nextcanvas=(curcanvas=="canvas0")? "canvas0" : "canvas1"

tempobj=ie4? eval("document.all."+nextcanvas) : document.getElementById(nextcanvas)

var slideimage='<img src="'+dropimages[curimageindex]+'" border=0>'

tempobj.innerHTML=(droplinks[curimageindex]!="")? '<a href="'+droplinks[curimageindex]+'">'+slideimage+'</a>' : slideimage

nextimageindex=(nextimageindex<dropimages.length-1)? nextimageindex+1 : 0

setTimeout("rotateimage()",pause)

}

}



function rotateimage(){

if (ie4||dom){

resetit(curcanvas)

var lastcanvas=(curcanvas=="canvas1")? "canvas0" : "canvas1";

var lastobj=tempobj=ie4? eval("document.all."+lastcanvas) : document.getElementById(lastcanvas);

lastobj.style.zIndex = 1;

var crossobj=tempobj=ie4? eval("document.all."+curcanvas) : document.getElementById(curcanvas);

crossobj.style.zIndex++;

var temp='setInterval("movepic()",50)';

dropslide=eval(temp);

curcanvas=(curcanvas=="canvas0")? "canvas1" : "canvas0";

}

else

document.images.defaultslide.src=dropimages[curimageindex]

linkindex=curimageindex

curimageindex=(curimageindex<dropimages.length-1)? curimageindex+1 : 0

}



function rotatelink(){

if (droplinks[linkindex]!="")

window.location=droplinks[linkindex]

}



function resetit(what){

curpos=parseInt(slideshow_width)*(-1)

var crossobj=ie4? eval("document.all."+what) : document.getElementById(what)

crossobj.style.left=curpos+"px"

}



function startit(){

var crossobj=ie4? eval("document.all."+curcanvas) : document.getElementById(curcanvas)

crossobj.innerHTML='<a href="'+droplinks[curimageindex]+'"><img src="'+dropimages[curimageindex]+'" border=0></a>'

rotateimage()

}



if (ie4||dom)

window.onload=startit

else

setInterval("rotateimage()",pause)



</script>

</div><!--slideshow-->



<div id="business" style="color:#<?php echo get_option('smallbiz_main_text_color') ?>;">

<?php echo do_shortcode(biz_option('smallbiz_rotator_main_text'))?>


<div style="clear: both;"></div>


</div><!--business-->

</div> <!--wraptop-->



<div id="devider">

</div>

<div id="home">



<div id="homepage-box1">





<?php echo biz_option('smallbiz_rotator_box1')?>





</div>



<div id="homepage-box2">



<?php echo biz_option('smallbiz_rotator_box2')?>





</div>



<div id="homepage-box3">



<?php echo biz_option('smallbiz_rotator_box3')?>







</div>



<div id="homepage-box4">



<?php echo biz_option('smallbiz_rotator_box4')?>





</div>

</div>

</div>



