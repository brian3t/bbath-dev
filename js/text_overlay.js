jQuery(function() {
    //instructions and change log is in the jquery.transformable.js
    //click "Manage Resouces to the left to get the link.
    var b=jQuery('.to_box');
    var ib=jQuery('.to_infobox');
    b.draggable({stop: showinfo});
    b.resizable({stop: showinfo});
    b.transformable( {
        rotateStop: showinfo,
        skewStop: showinfo,
        scaleStop: showinfo,
        rotate: showrotate,
        scale: constrainscale
    });

    //containment is still a bit expiramental
    jQuery('#to_box3').draggable('option','containment','parent')
        .transformable('option','containment',true)
        .resizable('option','containment','parent');

    function constrainscale(e,ui) {
        //uncomment to see how you can set value to constrain size
        //if (ui.scalex<0.5) ui.scalex=0.5;
        //if (ui.scaley<0.5) ui.scaley=0.5;
    }

    function showrotate(e,ui) {
        jQuery('#rotate').html(ui.angle.rad);
        //uncomment these to see how you can set the value to constrain angle
        //if(ui.angle.rad>1)ui.angle.rad=1;
        //if(ui.angle.rad<-1) ui.angle.rad=-1;
    }

    function showinfo (e,ui) {
        var u=jQuery(this).getTransform();
        var o=jQuery(this).tOffset();
        function xytostr(c) {
            return(parseInt(c.x)+','+parseInt(c.y));
        }
        ib.html(
            'Div Transformation Info:<ul>Current Matrix:<li>'
                + jQuery(this).matrixToArray()+'</li></ul><ul>Rotation:<li id="rotate">'
                +u.rotate+'</li></ul><ul>Skew:<li>'
                +u.skew+"</li></ul><ul>Scale:<li>"
                +u.scale+'</li></ul><ul>Offset:<li>'
                +'left:'+parseInt(o.left)+", top:"+parseInt(o.top)
                +', right:'+parseInt(o.right)+'bottom:'+parseInt(o.bottom)
                +'</li></ul>'
                +'<ul>Corners:<li>lt:'+xytostr(o.corners[0])
                +', rt:'+xytostr(o.corners[1])
                +', rb:'+xytostr(o.corners[2])
                +', lb:'+xytostr(o.corners[3])
        );
    }
    jQuery('.reset').click(function() {
        jQuery(this).parent().transformable('reset');
        showinfo.call(jQuery(this).parent());
    });
});
jQuery(document).ready(function(){
//    <div id="to_container" class='to_containerbox to_box1'>
//        <div id='to_box3' class='to_box to_box2'>
//            <button class='reset to'>Reset Positioning</button><br></br>
//        This one is contained
//        </div>
//
//    </div>
//    <div class='to_infobox'>Div Transformation Info:</div>
});