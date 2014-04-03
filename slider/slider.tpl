{if $slides|@count != 0}
<script type="text/javascript" src="{$this_path}js/jquery.nivo.slider.pack.js"></script>
<link rel="stylesheet" type="text/css" href="{$this_path}css/nivo-slider.css" />
<style type="text/css">
    #slide_holder{
        width:{$xml->width}px;
        height:{$xml->height}px;
    }
    .nivoSlider{
        width:{$xml->width}px;
        height:{$xml->height}px;
    }
    .nivo-prevNav {
        width:{math equation="x / y" x=$nav.0 y=2}px;
        height:{math equation="x / y" x=$nav.1 y=2}px;
    }
    .nivo-nextNav {
        width:{math equation="x / y" x=$nav.0 y=2}px;
        height:{math equation="x / y" x=$nav.1 y=2}px;
    }
    .nivo-directionNav a {
        top:{$xml->dirnavpos}%;
    }
    .nivo-controlNav {
        {$xml->ctrlnavhorizpos}: 0;
        top: {$xml->ctrlnavpos}%;
    }
    .nivo-controlNav a {
        width: {$bullets.0}px;
        height: {math equation="x / y" x=$bullets.1 y=2}px;
    }
</style>
        <!-- SLIDER ---------------------------------------------------------------------------------------------------------------------------------------> 
<div id="slide_holder"> 	
    <div id="slider">
            {foreach from=$slides item=slide}
                {if $slide.active==1}
                    <a href="{$slide.link}" title="{$slide.title}"><img src="{$this_path}{$slide.img}" alt="{$slide.alt}" title="{$slide.title}" /></a>
                {/if}
            {/foreach}
        
	</div>
</div>    
<script type="text/javascript">
$(window).load(function() {
	$('#slider').nivoSlider({
		effect:'{$xml->effect}', //Specify sets like: 'fold,fade,sliceDown'
        slices: {$xml->slices}, // For slice animations
        boxCols: {$xml->boxcols}, // For box animations
        boxRows: {$xml->boxrows}, // For box animations
		animSpeed:{$xml->animspeed}, //Slide transition speed
		pauseTime:{$xml->pausetime},
		directionNav:{$xml->directionnav}, //Next & Prev
		directionNavHide:{$xml->directionnavhide}, //Only show on hover
		controlNav:{$xml->controlnav}, //1,2,3...
		pauseOnHover:{$xml->pauseonhover}, //Stop animation while hovering
	});
});

</script>
 
<!-- SLIDER --------------------------------------------------------------------------------------------------------------------------------------->
{/if}

