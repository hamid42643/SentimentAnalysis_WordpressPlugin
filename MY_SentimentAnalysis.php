<?php
/**
 * Plugin Name: Sntiment Analysis
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A brief description of the Plugin.
 * Version: v.1
 * Author: Hamid Hooshmandi
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: A "Slug" license name e.g. GPL2
 */
 

require 'classify.php';
 
 class options_page {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		//echo $this->NBMModel2("dsdsds");
	}
	function admin_menu () {
		add_options_page( 'Page Title','Sentiment Analysis of comments','manage_options','options_page_slug', array( $this, 'settings_page' ) );
	}
	function  settings_page () {

	?><div class="wrap">
		<form id="comments-form" method="get" action="">
	<?php
	
		screen_icon();
		$args = array(
		// args here
		);

		// The Query
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( $args );

?><table class = "widefat fixed comments"><?php


		$str="";
		
		//get all the comments
		foreach ( $comments as $comment ) {
			$str .= trim(preg_replace('/\s+/', ' ', $comment->comment_content));
			$str .="\n";
		}

		
		if(!isset($_POST['submit'])){
			$countrows=5;
			
			if ( $comments ) { 
				$arr = $this->NB_PositiveOrNegetive($str);
				//$arr = $this->SVM_PositiveOrNegetive($str);
				//print_r($arr);
				foreach ( $comments as $comment ) {
					?><tr><?php 
						?><td style="width: 200px;" class="submitted-on"><?php echo '<p>'. $comment->comment_date . '</p>' ; ?></td><?php //category
						?><td style="width: 500px;"class="comment column-comment"><?php echo '<p>' . $comment->comment_content . '</p>';?></td><?php
						?><td style="width: 200px;"class=""><b><?php echo $arr[$countrows][4]?></b></td><?php //category
						?><td style="width: 200px;"class=""><b><?php echo $arr[$countrows][5]?></b></td><?php //probability
					?></tr><?php
					$countrows++;
				}
			}
		}
		
		else{
			if ( $comments ) {
				foreach ( $comments as $comment ) {
					?><tr><?php
					?><td class="comment column-comment"><?php echo '<p>' . $comment->comment_content . '</p>';?></td><?php
					?><td class=""><?php //echo $arr[$countrows][4]?></td><?php //category
					?><td class=""><?php //echo $arr[$countrows][5]?></td><?php //probability
					?></tr><?php
				}
			}
		}
		?> 
		
<div class="tablenav bottom">

		<div class="alignleft actions bulkactions">
			<select name="action2">
<option value="-1" selected="selected">Bulk Actions</option>
	<option value="Camera">Unapprove</option>
	<option value="Movies">Approve</option>
	<option value="Books">Mark as Spam</option>
</select>
<input type="submit" name="submit" id="doaction2" class="button action" value="Analyze comments!">
		</div>
		<div class="alignleft actions">
</div>
	</div>
	
	</table> </form> </div>
		
		
		<?php
		
		echo "<h2>".round($this->getOveralScore($arr))."% of the comments are Positive </h2>";
	}
	
 	function getOveralScore($arr){
		$countPositive=-1;
		$countNegetive=0;
		for($i=0 ; $i<count($arr) ; $i++){
			for($j=0 ; $j<count($arr[$i]) ; $j++){
					if($arr[$i][$j]=="positiv"){
						$countPositive++;
					}else if($arr[$i][$j]=="negetiv"){
						$countNegetive++;
					}
			}
		}
		
		echo "<h3>Positive:".$countPositive."<br />"."Negetive:".$countNegetive."<br /></h3>";
		return ($countPositive * 100)/($countNegetive+$countPositive);
	}
	
	
	
	
	function NB_PositiveOrNegetive($text){
		$header =
		"
@relation Camera__468Negetive556Positive-weka.filters.unsupervised.attribute.NominalToString-C1

@attribute 'Got this yesterday and like it, had before. Just to note, PowerShot A2500 is an entry-level pocket camera which is very similar to PowerShot A2300: 16-megapixel 1/2.3\" sensor, DiGiC 4 processor, and 5x zoom 28mm wide angle lens, 2.7\" LCD. Even 720p HD video capabilities are the same.Pros:1. What really distinguishes it from its predecessor is Smart Auto Mode recognizing 32 shooting environments and adjusts settings for better quality. It automatically selects the best shooting settings for optimal quality based on the environmental factors (lightning I guess) to provide point\'n\'shoot simplicity.2. 16.0 Megapixels, with loads of resolution pictures are still clear. High resolution is also good for producing biggest printouts.3. 5x Optical Zoom is sufficient in most cases.4. DIGIC 4 Image Processor. Not as fast as DIGIC 5 though fast and powerful enough to give you advanced system options, provide quick-shoot with reliable performance and low battery consumption. As far as I know DIGIC 4 is currently Canon\'s most efficient processor for budget cameras. BTW it has some Eco mode, that is said to be providing even faster warm-up times and saves the standard battery, but I haven\'t tested it yet.5. Very lightweight, just put it into your pocket, can take it everywhere.6. Price tag - $99. You can\'t get any better camera for the price.Cons:1. Like A2300 it lacks optical image stabilization, though it\'s got digital image stabilization.2. 1/2.3\" sensor. Well, entry level CCD providing good pictures, not of a DSLR quality, that\'s all I can say.Summary:Sure this is not the best camera in the world. I definitely knew it when I bought the camera. BUT, I was pleasantly surprised with the quality of pictures I shot.If you\'re like me (not a pro) and looking for a budget point-and-shoot camera taking family photos and events, mostly shoot in daylight or good lighting, I\'d definitely recommend it, taking into account its price tag of $99 - just about right.' string
@attribute ' positive' {' positive',' negetive'}

@data
		";
	
		$t = new test("NB_PositiveOrNegetiveV.2.model", $text, $header);
	
		$t->writeARFF();
	
		if($t->classifyUsingWEKA()){
	
			$t->readPredictionResult();
			$arr = $t->getPredictionResult();
	
			return $arr;
		}
	}
	
	
	
	
	function SVM_PositiveOrNegetive($text){
		$header =
		"
@relation Camera__468Negetive556Positive-weka.filters.unsupervised.attribute.NominalToString-C1
	
@attribute 'Got this yesterday and like it, had before. Just to note, PowerShot A2500 is an entry-level pocket camera which is very similar to PowerShot A2300: 16-megapixel 1/2.3\" sensor, DiGiC 4 processor, and 5x zoom 28mm wide angle lens, 2.7\" LCD. Even 720p HD video capabilities are the same.Pros:1. What really distinguishes it from its predecessor is Smart Auto Mode recognizing 32 shooting environments and adjusts settings for better quality. It automatically selects the best shooting settings for optimal quality based on the environmental factors (lightning I guess) to provide point\'n\'shoot simplicity.2. 16.0 Megapixels, with loads of resolution pictures are still clear. High resolution is also good for producing biggest printouts.3. 5x Optical Zoom is sufficient in most cases.4. DIGIC 4 Image Processor. Not as fast as DIGIC 5 though fast and powerful enough to give you advanced system options, provide quick-shoot with reliable performance and low battery consumption. As far as I know DIGIC 4 is currently Canon\'s most efficient processor for budget cameras. BTW it has some Eco mode, that is said to be providing even faster warm-up times and saves the standard battery, but I haven\'t tested it yet.5. Very lightweight, just put it into your pocket, can take it everywhere.6. Price tag - $99. You can\'t get any better camera for the price.Cons:1. Like A2300 it lacks optical image stabilization, though it\'s got digital image stabilization.2. 1/2.3\" sensor. Well, entry level CCD providing good pictures, not of a DSLR quality, that\'s all I can say.Summary:Sure this is not the best camera in the world. I definitely knew it when I bought the camera. BUT, I was pleasantly surprised with the quality of pictures I shot.If you\'re like me (not a pro) and looking for a budget point-and-shoot camera taking family photos and events, mostly shoot in daylight or good lighting, I\'d definitely recommend it, taking into account its price tag of $99 - just about right.' string
@attribute ' positive' {' positive',' negetive'}
	
@data
		";
	
		$t = new test("SVM.v.1.model", $text, $header);
	
		$t->writeARFF();
	
		if($t->classifyUsingWEKA()){
	
			$t->readPredictionResult();
			$arr = $t->getPredictionResult();
	
			return $arr;
		}
	}
	

	
	function NBMModel2($text){
		$header =
		"
	@relation Top26Cat_500FromEachCat-weka.filters.unsupervised.attribute.NominalToString-C1

	@attribute 'Education is the most powerful weapon which you can use to change the world.' string
	@attribute ' Education' {' Education',' Health',' Morning',' Money',' Business',' Family',' Peace',' Dreams',' War',' Art',' Home',' Happiness',' Success',' Change',' Nature',' Truth',' Power',' Time',' Love',' God',' Life',' Intelligence',' Courage',' Friendship'}

	@data
		";
		
		$t = new test("nbmV2.model", $text, $header);

		$t->writeARFF();

		if($t->classifyUsingWEKA()){

			$t->readPredictionResult();
			$arr = $t->getPredictionResult();

			return $arr;
		}
	}
	
	
}
new options_page;
 ?>
 


	
 
 