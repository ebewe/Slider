<?php
/**
 * NOTICE OF LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * ...........................................................................
 *
 * @package   Slider
 * @author    Paul MORA
 * @copyright Copyright (c) 2012-2014 EURL ébewè - www.ebewe.net - Paul MORA
 * @license   MIT license
 * Support by mail  :  contact@ebewe.net
 */

class Slideshow extends AdminTab
{
  private $module = 'slider';

	public function __construct()
	{
		global $cookie;
		
		$this->url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->module;
		
		parent::__construct();
	}

	public function display()
	{
		global $cookie;
        
        $db = Db::getInstance();
        if(isset($_GET['country']) && !empty($_GET['country'])){
            $id_lang = $_GET['country'];
            $iso = Language::getIsoById($id_lang);
        }else{
            $id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
            $iso = Language::getIsoById($id_lang);
        }
        
        $languages = Language::getLanguages();

		if(isset($_POST['submitSlides'])) {
            $result = $db->ExecuteS('SELECT id_slider FROM `'._DB_PREFIX_.'slider` WHERE id_lang='.$id_lang);
            foreach($result as $slide)
                $slides[]=$slide['id_slider'];

            $j=1;
            for($i=1; $i<=10; $i++){
            
                if(!empty($_POST['delete_'.$i])){
                    $img = $db->ExecuteS('SELECT img FROM `'._DB_PREFIX_.'slider` WHERE `id_slider`='.$_POST['slide_'.$i].' AND id_lang='.$id_lang);
                    // echo $img[0]['img'];
                    unlink(_PS_MODULE_DIR_.'slider/'.$img[0]['img']);
                    $db->Execute('DELETE FROM `'._DB_PREFIX_.'slider` WHERE `id_slider`='.$_POST['slide_'.$i]);
                }else{
                    if((!empty($_POST['bdd_img_'.$i]) || $_FILES['img_'.$i]['tmp_name']!="") || !empty($_POST['link_'.$i]) || !empty($_POST['title_'.$i]) || !empty($_POST['alt_'.$i])){
                
                        // Upload d'une image
                        if ($_FILES['img_'.$i]['tmp_name']!=""){
                            if ($_FILES['img_'.$i]['error']) {
                                switch ($_FILES['img_'.$i]['error']){
                                    case 1: // UPLOAD_ERR_INI_SIZE
                                    echo $this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                                    break;
                                    case 2: // UPLOAD_ERR_FORM_SIZE
                                    echo $this->l('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                                    break;
                                    case 3: // UPLOAD_ERR_PARTIAL
                                    echo $this->l('The uploaded file was only partially uploaded');
                                    break;
                                    case 4: // UPLOAD_ERR_NO_FILE
                                    echo $this->l('No file was uploaded');
                                    break;
                                }
                            }else{
                                $ext = pathinfo($_FILES['img_'.$i]['name'], PATHINFO_EXTENSION);
                                $img = $iso.'_'.$_POST['slide_'.$i].'.'.$ext;
                                move_uploaded_file($_FILES['img_'.$i]['tmp_name'], _PS_MODULE_DIR_.'slider/'.$img);
                                echo '<div class="conf"><img alt="" src="../img/admin/ok2.png">'.$this->l('Your image was uploaded succesfuly').'</div>';
                            }
                        }else{
                            $img = $_POST['bdd_img_'.$i];
                        }

                        $link = $_POST['link_'.$i];
                        $title = $_POST['title_'.$i];
                        $alt = $_POST['alt_'.$i];
                        $active = $_POST['active_'.$i];
                    
                        if(isset($slides) && in_array($_POST['slide_'.$i], $slides)){
                            $query = 'UPDATE `'._DB_PREFIX_.'slider` SET `id_position`='.$j.', `img`="'.$img.'", `link`="'.$link.'", `title`="'.$title.'", `alt`="'.$alt.'", `active`="'.$active.'" WHERE `id_slider`='.$_POST['slide_'.$i].' AND `id_lang`='.$id_lang;
                            $db->Execute($query);
                        }else{
                            $query = 'INSERT INTO `'._DB_PREFIX_.'slider`
                                (`id_slider`, `id_lang`, `id_position`, `img`, `link`, `title`, `alt`, `active`)
                                VALUES ('.$_POST['slide_'.$i].','.$id_lang.','.$j.',"'.$img.'","'.$link.'","'.$title.'","'.$alt.'","'.$active.'" )';
                            $db->Execute($query);
                        }
                    $j++;
                    }
                }
            }
        }elseif(isset($_GET['slide']) && !empty($_GET['slide']) && isset($_GET['position']) && !empty($_GET['position']) && isset($_GET['way']) && !empty($_GET['way'])){
            if($_GET['way']==1){
                $db->Execute('UPDATE `'._DB_PREFIX_.'slider` SET `id_position`='.$_GET['position'].' WHERE `id_position`='.($_GET['position']-1).' AND `id_lang`='.$id_lang);
                $db->Execute('UPDATE `'._DB_PREFIX_.'slider` SET `id_position`='.($_GET['position']-1).' WHERE `id_slider`='.$_GET['slide'].' AND `id_lang`='.$id_lang);
            }else{
                $db->Execute('UPDATE `'._DB_PREFIX_.'slider` SET `id_position`='.$_GET['position'].' WHERE `id_position`='.($_GET['position']+1).' AND `id_lang`='.$id_lang);
                $db->Execute('UPDATE `'._DB_PREFIX_.'slider` SET `id_position`='.($_GET['position']+1).' WHERE `id_slider`='.$_GET['slide'].' AND `id_lang`='.$id_lang);
            }
            header('Location: index.php?tab=Slideshow&country='.$id_lang.'&token='.$_GET['token']);
        }elseif(isset($_POST['submitSlideshow'])) {
            $xml = simplexml_load_file(_PS_MODULE_DIR_.'slider/slider.xml');
            $xml->effect = $_POST['effect'];
            $xml->slices = $_POST['slices'];
            $xml->boxcols = $_POST['boxcols'];
            $xml->boxrows = $_POST['boxrows'];
            $xml->animspeed = $_POST['animspeed'];
            $xml->pausetime = $_POST['pausetime'];
            $xml->directionnav = $_POST['directionnav'];
            $xml->directionnavhide = $_POST['directionnavhide'];
            $xml->controlnav = $_POST['controlnav'];
            $xml->pauseonhover = $_POST['pauseonhover'];
            $xml->width = $_POST['width'];
            $xml->height = $_POST['height'];
            $xml->dirnavpos = $_POST['dirnavpos'];
            $xml->ctrlnavhorizpos = $_POST['ctrlnavhorizpos'];
            $xml->ctrlnavpos = $_POST['ctrlnavpos'];
            $output = $xml->asXML(_PS_MODULE_DIR_.'slider/slider.xml');
            
            if ($_FILES['nivo-nav']['tmp_name']!=""){
        
                // Upload d'une image
                if ($_FILES['nivo-nav']['tmp_name']!="" AND $_FILES['nivo-nav']['error']) {
                // unlink(_PS_MODULE_DIR_.'slider/img/nivo_nav.png');

                    switch ($_FILES['nivo-nav']['error']){
                        case 1: // UPLOAD_ERR_INI_SIZE
                        echo $this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                        break;
                        case 2: // UPLOAD_ERR_FORM_SIZE
                        echo $this->l('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                        break;
                        case 3: // UPLOAD_ERR_PARTIAL
                        echo $this->l('The uploaded file was only partially uploaded');
                        break;
                        case 4: // UPLOAD_ERR_NO_FILE
                        echo $this->l('No file was uploaded');
                        break;
                    }
                }elseif($_FILES['nivo-nav']['tmp_name']!="" && $_FILES['nivo-nav']['type']!='image/png'){
                    echo '<div class="error"><img alt="" src="../img/admin/error2.png">'.$this->l('Your image is not a valid png file').'</div>';
                }elseif ($_FILES['nivo-nav']['tmp_name']!="" && $_FILES['nivo-nav']['type']=='image/png') {
                    move_uploaded_file($_FILES['nivo-nav']['tmp_name'], _PS_MODULE_DIR_.'slider/img/nivo_nav.png');
                    echo '<div class="conf"><img alt="" src="../img/admin/ok2.png">'.$this->l('Your image was uploaded succesfuly').'</div>';
                }
                
            }
            
            if ($_FILES['bullets']['tmp_name']!=""){
                // unlink(_PS_MODULE_DIR_.'slider/img/bullets.png');
                
                // Upload d'une image
                if ($_FILES['bullets']['tmp_name']!="" AND $_FILES['bullets']['error']) {
                    switch ($_FILES['bullets']['error']){
                        case 1: // UPLOAD_ERR_INI_SIZE
                        echo $this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                        break;
                        case 2: // UPLOAD_ERR_FORM_SIZE
                        echo $this->l('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                        break;
                        case 3: // UPLOAD_ERR_PARTIAL
                        echo $this->l('The uploaded file was only partially uploaded');
                        break;
                        case 4: // UPLOAD_ERR_NO_FILE
                        echo $this->l('No file was uploaded');
                        break;
                    }
                }elseif($_FILES['bullets']['tmp_name']!="" && $_FILES['bullets']['type']!='image/png'){
                    echo '<div class="error"><img alt="" src="../img/admin/error2.png">'.$this->l('Your image is not a valid png file').'</div>';
                }elseif ($_FILES['bullets']['tmp_name']!="" && $_FILES['bullets']['type']=='image/png') {
                    move_uploaded_file($_FILES['bullets']['tmp_name'], _PS_MODULE_DIR_.'slider/img/bullets.png');
                    echo '<div class="conf"><img alt="" src="../img/admin/ok2.png">'.$this->l('Your image was uploaded succesfuly').'</div>';
                }
                
            }
		}


		// Lecture de la base de donnée
		$slides = $db->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'slider` WHERE `id_lang`='.$id_lang.' ORDER BY id_position ASC');
		$max = $db->ExecuteS('SELECT MAX(`id_slider`) FROM `'._DB_PREFIX_.'slider`');
        $max = $max[0]['MAX(`id_slider`)']+1;
		$position = $db->ExecuteS('SELECT MAX(`id_position`) FROM `'._DB_PREFIX_.'slider` WHERE `id_lang`='.$id_lang);
        $position = $position[0]['MAX(`id_position`)'];

		// Affichage des informations de configuration des slides
		echo '
        <style type="text/css">
            hr {border: 1px solid;}
        </style>
		<form action="" name="save" id="save" method="post" enctype="multipart/form-data" >
		<fieldset>
			<legend>'.$this->l('Configuration of the slides').'</legend>
			<div class="margin-form">
				<input type="submit" name="submitSlides" value="'.$this->l('Update slides').'" class="button" />&nbsp;&nbsp;&nbsp;&nbsp;';
            foreach($languages as $language){
                echo '<a href="index.php?tab=Slideshow&country='.$language['id_lang'].'&token='.$_GET['token'].'"><img '.($language['id_lang']!=$id_lang ? 'style="opacity:.4"' : '').' src="../img/l/'.$language['id_lang'].'.jpg" alt="'.Language::getIsoById($language['id_lang']).'" /></a>';
            };
			echo '</div>
            <hr />
            <br />';
            for($i=0; $i<=9; $i++){
                echo '<a href="#" onclick="$(\'#add_slide_'.$i.'\').slideToggle();return false;"><b>'.$this->l('Slide').' '.($i+1).'</b></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                
                if(!empty($slides[$i]['img'])){
                    echo '<input type="checkbox" name="delete_'.($i+1).'" />&nbsp;'.$this->l('Delete').'&nbsp;&nbsp;&nbsp;&nbsp;';
                        if(($i+1)!=1)
                            echo '<a href="'.$_SERVER['REQUEST_URI'].'&slide='.$slides[$i]['id_slider'].'&position='.($i+1).'&way=1"><img title="'.$this->l('Up').'" alt="'.$this->l('Up').'" src="../img/admin/up.gif"></a>';
                        if(($i+1)!=$position)
                            echo '<a href="'.$_SERVER['REQUEST_URI'].'&slide='.$slides[$i]['id_slider'].'&position='.($i+1).'&way=2"><img title="'.$this->l('Down').'" alt="'.$this->l('Down').'" src="../img/admin/down.gif"></a>';
                }    

                echo '<div id="add_slide_'.$i.'" '.(empty($slides[$i]['img']) ? 'style="display: none;"' : '').'>
                    <input type="hidden" name="slide_'.($i+1).'" value="'.(!empty($slides[$i]['id_slider']) ? $slides[$i]['id_slider'] : $max).'" />
                    <label>'.$this->l('Image').'</label>
                    <div class="margin-form">
                        '.(!empty($slides[$i]['img']) ? '<img style="width:600px" src="'.$this->url.'/'.$slides[$i]['img'].'" alt="'.$slides[$i]['alt'].'" />' : '').'
                        <input type="hidden" name="bdd_img_'.($i+1).'" value="'.(!empty($slides[$i]['img']) ? $slides[$i]['img'] : '').'" />
                        <input type="file" name="img_'.($i+1).'" value="" />
                    </div>
                    <label>'.$this->l('Link').'</label>
                    <div class="margin-form">
                        <input type="text" name="link_'.($i+1).'" class="link" value="'.(!empty($slides[$i]['link']) ? $slides[$i]['link'] : '').'" />
                    </div>
                    <label>'.$this->l('Title').'</label>
                    <div class="margin-form">
                        <input type="text" name="title_'.($i+1).'" class="title" value="'.(!empty($slides[$i]['title']) ? $slides[$i]['title'] : '').'" />
                    </div>
                    <label>'.$this->l('Alt text').'</label>
                    <div class="margin-form">
                        <input type="text" name="alt_'.($i+1).'" class="alt" value="'.(!empty($slides[$i]['alt']) ? $slides[$i]['alt'] : '').'" />
                    </div>
                    <label>'.$this->l('Active').'</label>
                    <div class="margin-form">
                        <img src="../img/admin/disabled.gif" alt="No" /><input type="radio" name="active_'.($i+1).'" class="active" value="0" '.((!isset($slides[$i]['active']) || $slides[$i]['active'] == '0') ? 'checked="checked"' : '').' /> '.$this->l('No').'&nbsp;&nbsp;
                        <img src="../img/admin/enabled.gif" alt="Yes" /><input type="radio" name="active_'.($i+1).'" class="active" value="1" '.((isset($slides[$i]['active']) && $slides[$i]['active'] == '1') ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
                    </div>
                </div>
                <br />
                <hr />
                <br />
                ';
            (isset($slides[$i]['id_slider']) ? '' : $max++);
            }
        
        echo '
			<div class="margin-form">
				<input type="submit" name="submitSlides" value="'.$this->l('Update slides').'" class="button" />
			</div>
		</fieldset>
		</form>
        <br />
		';
        
        // Affichage des informations de configuration des slides

		// Lecture du fichier de configuration
        $xml = simplexml_load_file(_PS_MODULE_DIR_.'slider/slider.xml');
        
		echo '
		<form action="" name="saveslideshow" id="saveslideshow" method="post" enctype="multipart/form-data" >
		<fieldset>
			<legend>'.$this->l('Configuration of the slideshow').'</legend>
			<div class="margin-form">
				<input type="submit" name="submitSlideshow" value="'.$this->l('Update').'" class="button" />
			</div>
            <hr />
            <br />
            <label>'.$this->l('Effect').'</label>
            <div class="margin-form">
                <select name="effect">
                    <option '.($xml->effect=="sliceDown" ? 'selected="selected"' : '').' value="sliceDown">sliceDown</option>
                    <option '.($xml->effect=="sliceDownLeft" ? 'selected="selected"' : '').' value="sliceDownLeft">sliceDownLeft</option>
                    <option '.($xml->effect=="sliceUp" ? 'selected="selected"' : '').' value="sliceUp">sliceUp</option>
                    <option '.($xml->effect=="sliceUpLeft" ? 'selected="selected"' : '').' value="sliceUpLeft">sliceUpLeft</option>
                    <option '.($xml->effect=="sliceUpDown" ? 'selected="selected"' : '').' value="sliceUpDown">sliceUpDown</option>
                    <option '.($xml->effect=="sliceUpDownLeft" ? 'selected="selected"' : '').' value="sliceUpDownLeft">sliceUpDownLeft</option>
                    <option '.($xml->effect=="fold" ? 'selected="selected"' : '').' value="fold">fold</option>
                    <option '.($xml->effect=="fade" ? 'selected="selected"' : '').' value="fade">fade</option>
                    <option '.($xml->effect=="random" ? 'selected="selected"' : '').' value="random">random</option>
                    <option '.($xml->effect=="slideInRight" ? 'selected="selected"' : '').' value="slideInRight">slideInRight</option>
                    <option '.($xml->effect=="slideInLeft" ? 'selected="selected"' : '').' value="slideInLeft">slideInLeft</option>
                    <option '.($xml->effect=="boxRandom" ? 'selected="selected"' : '').' value="boxRandom">boxRandom</option>
                    <option '.($xml->effect=="boxRain" ? 'selected="selected"' : '').' value="boxRain">boxRain</option>
                    <option '.($xml->effect=="boxRainReverse" ? 'selected="selected"' : '').' value="boxRainReverse">boxRainReverse</option>
                    <option '.($xml->effect=="boxRainGrow" ? 'selected="selected"' : '').' value="boxRainGrow">boxRainGrow</option>
                    <option '.($xml->effect=="boxRainGrowReverse" ? 'selected="selected"' : '').' value="boxRainGrowReverse">boxRainGrowReverse</option>
                </select>
            </div>
            <label>'.$this->l('Slices').'</label>
			<div class="margin-form">
                <input type="text" name="slices" value="'.$xml->slices.'" /> '.$this->l('Necessary for slice effects').'
            </div>
            <label>'.$this->l('Box columns').'</label>
			<div class="margin-form">
                <input type="text" name="boxcols" value="'.$xml->boxcols.'" /> '.$this->l('Necessary for box effects').'
            </div>
            <label>'.$this->l('Box rows').'</label>
			<div class="margin-form">
                <input type="text" name="boxrows" value="'.$xml->boxrows.'" /> '.$this->l('Necessary for box effects').'
            </div>
            <label>'.$this->l('Animation speed').'</label>
			<div class="margin-form">
                <input type="text" name="animspeed" value="'.$xml->animspeed.'" /> '.$this->l('Transition time').'
            </div>
            <label>'.$this->l('Pause time').'</label>
			<div class="margin-form">
                <input type="text" name="pausetime" value="'.$xml->pausetime.'" /> '.$this->l('Time to show image').'
            </div>
            <label>'.$this->l('Direction navigation').'</label>
			<div class="margin-form">
                <img src="../img/admin/disabled.gif" alt="No" /><input type="radio" name="directionnav" value="false" '.((isset($xml->directionnav) && $xml->directionnav == 'false') ? 'checked="checked"' : '').' /> '.$this->l('No').'&nbsp;&nbsp;
                <img src="../img/admin/enabled.gif" alt="Yes" /><input type="radio" name="directionnav" value="true" '.((!isset($xml->directionnav) || empty($xml->directionnav) || $xml->directionnav == 'true') ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->l('Show right and left arrows').'
            </div>
            <label>'.$this->l('Hide direction navigation').'</label>
			<div class="margin-form">
                <img src="../img/admin/disabled.gif" alt="No" /><input type="radio" name="directionnavhide" value="false" '.((isset($xml->directionnavhide) && $xml->directionnavhide == 'false') ? 'checked="checked"' : '').' /> '.$this->l('No').'&nbsp;&nbsp;
                <img src="../img/admin/enabled.gif" alt="Yes" /><input type="radio" name="directionnavhide" value="true" '.((!isset($xml->directionnavhide) || empty($xml->directionnavhide) || $xml->directionnavhide == 'true') ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->l('Only show on hover').'
            </div>
            <label>'.$this->l('Control navigation').'</label>
			<div class="margin-form">
                <img src="../img/admin/disabled.gif" alt="No" /><input type="radio" name="controlnav" value="false" '.((isset($xml->controlnav) && $xml->controlnav == 'false') ? 'checked="checked"' : '').' /> '.$this->l('No').'&nbsp;&nbsp;
                <img src="../img/admin/enabled.gif" alt="Yes" /><input type="radio" name="controlnav" value="true" '.((!isset($xml->controlnav) || empty($xml->controlnav) || $xml->controlnav == 'true') ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->l('Show bullets').'
            </div>
            <label>'.$this->l('Pause on hover').'</label>
			<div class="margin-form">
                <img src="../img/admin/disabled.gif" alt="No" /><input type="radio" name="pauseonhover" value="false" '.((isset($xml->pauseonhover) && $xml->pauseonhover == 'false') ? 'checked="checked"' : '').' /> '.$this->l('No').'&nbsp;&nbsp;
                <img src="../img/admin/enabled.gif" alt="Yes" /><input type="radio" name="pauseonhover" value="true" '.((!isset($xml->pauseonhover) || empty($xml->pauseonhover) || $xml->pauseonhover == 'true') ? 'checked="checked"' : '').' /> '.$this->l('Yes').'
            </div>
            <br />
            <hr />
            <br />
            <label>'.$this->l('Slideshow width').'</label>
			<div class="margin-form">
                <input type="text" name="width" value="'.$xml->width.'" /> px
            </div>
            <label>'.$this->l('Slideshow height').'</label>
			<div class="margin-form">
                <input type="text" name="height" value="'.$xml->height.'" /> px
            </div>
            <hr />
            <label>'.$this->l('Direction navigation image').'</label>
			<div class="margin-form">
                <input type="file" name="nivo-nav" value="" /> '.$this->l('Actual image').' <img src="'.$this->url.'/img/nivo_nav.png" alt="" />
            </div>
            <label>'.$this->l('Direction navigation position').'</label>
			<div class="margin-form">
                <input type="text" name="dirnavpos" value="'.$xml->dirnavpos.'" /> % '.$this->l('from top').'
            </div>
            <hr />
            <label>'.$this->l('Control navigation image').'</label>
			<div class="margin-form">
                <input type="file" name="bullets" value="" /> '.$this->l('Actual image').' <img src="'.$this->url.'/img/bullets.png" alt="" />
            </div>
            <label>'.$this->l('Control navigation position').'</label>
			<div class="margin-form">
                <input type="radio" name="ctrlnavhorizpos" value="left" '.((isset($xml->ctrlnavhorizpos) && $xml->ctrlnavhorizpos == 'left') ? 'checked="checked"' : '').' /> '.$this->l('Left').'&nbsp;&nbsp;
                <input type="radio" name="ctrlnavhorizpos" value="right" '.((!isset($xml->ctrlnavhorizpos) || empty($xml->ctrlnavhorizpos) || $xml->ctrlnavhorizpos == 'right') ? 'checked="checked"' : '').' /> '.$this->l('Right').'
                <br /><br /><input type="text" name="ctrlnavpos" value="'.$xml->ctrlnavpos.'" /> % '.$this->l('from top').'
            </div>
            <br />
            <hr />
            <br />
			<div class="margin-form">
				<input type="submit" name="submitSlideshow" value="'.$this->l('Update').'" class="button" />
			</div>
		</fieldset>
		</form>
        <br />
		';

	}
}

?>