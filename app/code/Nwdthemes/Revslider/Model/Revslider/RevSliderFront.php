<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */
 
namespace Nwdthemes\Revslider\Model\Revslider;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderBase;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderCssParser;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctionsWP;

class RevSliderFront extends \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderBaseFront {

	protected $_framework;
    protected $_query;
    protected $_curl;
    protected $_filesystem;
    protected $_images;
    protected $_resource;
    protected $_backendUrl;
    protected $_googleFonts;

	protected static $framework;
	protected static $query;
	protected static $curl;
	protected static $filesystem;
	protected static $images;
	protected static $resource;
    protected static $googleFonts;

	/**
	 *	Constructor
	 */

	public function __construct(
        \Nwdthemes\Revslider\Helper\Framework $framework,
        \Nwdthemes\Revslider\Helper\Query $query,
        \Nwdthemes\Revslider\Helper\Curl $curl,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystem,
        \Nwdthemes\Revslider\Helper\Images $images,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Backend\Model\UrlInterface $backendUrl,
		\Nwdthemes\Revslider\Model\Revslider\RevSliderOperations $revSliderOperations,
		\Nwdthemes\Revslider\Model\Revslider\RevSliderOutput $revSliderOutput,
        \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctions $revSliderFunctions,
        \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderFunctionsWP $revSliderFunctionsWP,
        \Nwdthemes\Revslider\Model\Revslider\GoogleFonts $googleFonts
    ) {
        $this->_framework = $framework;
        $this->_query = $query;
        $this->_curl = $curl;
        $this->_filesystem = $filesystem;
        $this->_images = $images;
        $this->_resource = $resource;
        $this->_backendUrl = $backendUrl;
        $this->_googleFonts = $googleFonts;

		self::$framework = $this->_framework;
		self::$query = $this->_query;
		self::$curl = $this->_curl;
		self::$filesystem = $this->_filesystem;
        self::$images = $this->_images;
		self::$resource = $this->_resource;
        self::$googleFonts = $this->_googleFonts;
		
		parent::__construct($this, $this->_filesystem, $this->_framework, $this->_query, $this->_images, $this->_backendUrl);
		
		//set table names
		RevSliderGlobals::$table_sliders = self::$table_prefix.RevSliderGlobals::TABLE_SLIDERS_NAME;
		RevSliderGlobals::$table_slides = self::$table_prefix.RevSliderGlobals::TABLE_SLIDES_NAME;
		RevSliderGlobals::$table_static_slides = self::$table_prefix.RevSliderGlobals::TABLE_STATIC_SLIDES_NAME;
		RevSliderGlobals::$table_settings = self::$table_prefix.RevSliderGlobals::TABLE_SETTINGS_NAME;
		RevSliderGlobals::$table_css = self::$table_prefix.RevSliderGlobals::TABLE_CSS_NAME;
		RevSliderGlobals::$table_layer_anims = self::$table_prefix.RevSliderGlobals::TABLE_LAYER_ANIMS_NAME;
		RevSliderGlobals::$table_navigation = self::$table_prefix.RevSliderGlobals::TABLE_NAVIGATION_NAME;
		
		$this->_framework->add_filter('punchfonts_modify_url', array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', 'modify_punch_url'));
		
		$this->_framework->add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
	}
	
	
	/**
	 *
	 * a must function. you can not use it, but the function must stay there!
	 */		
	public static function onAddScripts(){
		$wp_version = self::$framework->getVersion();

		$slver = self::$framework->apply_filters('revslider_remove_version', RevSliderGlobals::SLIDER_REVISION);

		$style_pre = '';
		$style_post = '';
		if($wp_version < 3.7){
			$style_pre = '<style type="text/css">';
			$style_post = '</style>';
		}
		
		$operations = new RevSliderOperations(self::$framework, self::$query, self::$curl, self::$filesystem, self::$images, self::$resource, self::$googleFonts);
		$arrValues = $operations->getGeneralSettingsValues();

		$includesGlobally = RevSliderFunctions::getVal($arrValues, "includes_globally","on");
		$includesFooter = RevSliderFunctions::getVal($arrValues, "js_to_footer","off");
		$strPutIn = RevSliderFunctions::getVal($arrValues, "pages_for_includes");
		$isPutIn = RevSliderOutput::isPutIn($strPutIn,true);
		
        $do_inclusion = self::$framework->apply_filters('revslider_include_libraries', false);

		//put the includes only on pages with active widget or shortcode
		// if the put in match, then include them always (ignore this if)			
        if($isPutIn == false && $includesGlobally == "off" && $do_inclusion == false){
			$hasShortcode = RevSliderFunctionsWP::hasShortcode("rev_slider");
			
			if($isWidgetActive == false && $hasShortcode == false)
				return(false);
		}
		
		self::$framework->wp_enqueue_style('rs-plugin-settings', Framework::$RS_PLUGIN_URL .'public/assets/css/settings.css', array(), $slver);

		$custom_css = RevSliderOperations::getStaticCss();
		$custom_css = RevSliderCssParser::compress_css($custom_css);

		if(trim($custom_css) == '') $custom_css = '#rs-demo-id {}';

		self::$framework->wp_add_inline_style( 'rs-plugin-settings', $style_pre.$custom_css.$style_post );

		$setBase = (self::$framework->is_ssl()) ? "https://" : "http://";

		self::$framework->wp_enqueue_script(array('jquery'));

        $waitfor = array('jquery');
        
        $enable_logs = RevSliderFunctions::getVal($arrValues, "enable_logs",'off');
        if($enable_logs == 'on'){
            self::$framework->wp_enqueue_script('enable-logs', Framework::$RS_PLUGIN_URL .'public/assets/js/jquery.themepunch.enablelog.js', $waitfor, $slver);
            $waitfor[] = 'enable-logs';
		}


        $ft = ($includesFooter == "off") ? false : true;

        self::$framework->wp_enqueue_script('tp-tools', Framework::$RS_PLUGIN_URL .'public/assets/js/jquery.themepunch.tools.min.js', $waitfor, $slver, $ft);
        self::$framework->wp_enqueue_script('revmin', Framework::$RS_PLUGIN_URL .'public/assets/js/jquery.themepunch.revolution.min.js', 'tp-tools', $slver, $ft);


		self::$framework->add_action('wp_head', array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', 'add_meta_generator'));
		self::$framework->add_action("wp_footer", array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', "load_icon_fonts") );

		// Async JS Loading
		$js_defer = RevSliderBase::getVar($arrValues, 'js_defer', 'off');
		if($js_defer!='off') self::$framework->add_filter('clean_url', array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', 'add_defer_forscript'), 11, 1);

		self::$framework->add_action('wp_before_admin_bar_render', array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', 'add_admin_menu_nodes'));
		self::$framework->add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront', 'putAdminBarMenus'));
		
	}

	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 */
	public static function putAdminBarMenus () {
		if(!self::$framework->is_super_admin() || !self::$framework->is_admin_bar_showing()) return;

		?>
		<script>
			jQuery(document).ready(function() {
				
				if (jQuery('#wp-admin-bar-revslider-default').length>0 && jQuery('.rev_slider_wrapper').length>0) {
					var aliases = new Array();
					jQuery('.rev_slider_wrapper').each(function() {
						aliases.push(jQuery(this).data('alias'));
					});
					if 	(aliases.length>0)
						jQuery('#wp-admin-bar-revslider-default li').each(function() {
							var li = jQuery(this),
								t = jQuery.trim(li.find('.ab-item .rs-label').data('alias')); //text()
								
							if (jQuery.inArray(t,aliases)!=-1) {
							} else {
								li.remove();
							}
						});
				} else {
					jQuery('#wp-admin-bar-revslider').remove();
				}
			});
		</script>
		<?php
	}

	/**
	 * add admin nodes
	 * @since: 5.0.5
	 */
	public static function add_admin_menu_nodes(){
		if(!self::$framework->is_super_admin() || !self::$framework->is_admin_bar_showing()) return;
		
		self::_add_node('<span class="rs-label">Slider Revolution</span>', false, self::$framework->admin_url('admin.php?page=revslider'), array('class' => 'revslider-menu' ), 'revslider'); //<span class="wp-menu-image dashicons-before dashicons-update"></span>

		//add all nodes of all Slider
		$sl = new RevSliderSlider(self::$framework, self::$query, self::$curl, self::$filesystem, self::$images, self::$resource, self::$googleFonts);
		$sliders = $sl->getAllSliderForAdminMenu();

		if(!empty($sliders)){
			foreach($sliders as $id => $slider){
				self::_add_node('<span class="rs-label" data-alias="'.self::$framework->esc_attr($slider['alias']).'">'.self::$framework->esc_attr($slider['title']).'</span>', 'revslider', self::$framework->admin_url('admin.php?page=revslider&view=slide&id=new&slider='.intval($id)), array('class' => 'revslider-sub-menu' ), self::$framework->esc_attr($slider['alias'])); //<span class="wp-menu-image dashicons-before dashicons-update"></span>
			}
		}

	}


	/**
	 * add admin node
	 * @since: 5.0.5
	 */
	public static function _add_node($title, $parent = false, $href = '', $custom_meta = array(), $id = ''){
		global $wp_admin_bar;

		if(!self::$framework->is_super_admin() || !self::$framework->is_admin_bar_showing()) return;
		
		if($id == '') $id = strtolower(str_replace(' ', '-', $title));

		// links from the current host will open in the current window
		$meta = strpos( $href, site_url() ) !== false ? array() : array( 'target' => '_blank' ); // external links open in new tab/window
		$meta = array_merge( $meta, $custom_meta );

		$wp_admin_bar->add_node(array(
			'parent' => $parent,
			'id'     => $id,
			'title'  => $title,
			'href'   => $href,
			'meta'   => $meta,
		));
	}

	
	/**
	 *
	 * create db tables
	 */
	public static function createDBTables(){
		self::createTable(RevSliderGlobals::TABLE_SLIDERS_NAME);
		self::createTable(RevSliderGlobals::TABLE_SLIDES_NAME);
		self::createTable(RevSliderGlobals::TABLE_STATIC_SLIDES_NAME);
		self::createTable(RevSliderGlobals::TABLE_CSS_NAME);
		self::createTable(RevSliderGlobals::TABLE_LAYER_ANIMS_NAME);
		self::createTable(RevSliderGlobals::TABLE_NAVIGATION_NAME);
		
		self::updateTables();
        
        self::$framework->update_option('revslider_change_database', false);
	}
	
	public static function load_icon_fonts(){
		global $fa_icon_var,$pe_7s_var;
		if($fa_icon_var) echo "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-fa-icon-css'  href='" . Framework::$RS_PLUGIN_URL . "public/assets/fonts/font-awesome/css/font-awesome.css' type='text/css' media='all' />";
		if($pe_7s_var) echo "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-pe-7s-css'  href='" . Framework::$RS_PLUGIN_URL . "public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css' type='text/css' media='all' />";
	}

	public static function updateTables(){
		$cur_ver = self::$framework->get_option('revslider_table_version', '1.0.0');
        if(self::$framework->get_option('revslider_change_database', false)){
            $cur_ver = '1.0.0';
        }
        
		if(version_compare($cur_ver, '1.0.1', '<')){ //add missing settings field, for new creates lines in slide editor for example
			//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$tableName = RevSliderGlobals::TABLE_SLIDES_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  slider_id int(9) NOT NULL,
						  slide_order int not NULL,
                          params LONGTEXT NOT NULL,
                          layers LONGTEXT NOT NULL,
						  settings text NOT NULL,
						  UNIQUE KEY id (id)
						);";
			self::$query->dbDelta($sql);
			
			$tableName = RevSliderGlobals::TABLE_STATIC_SLIDES_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  slider_id int(9) NOT NULL,
                          params LONGTEXT NOT NULL,
                          layers LONGTEXT NOT NULL,
						  settings text NOT NULL,
						  UNIQUE KEY id (id)
						);";
			self::$query->dbDelta($sql);
			
			self::$framework->update_option('revslider_table_version', '1.0.1');
			$cur_ver = '1.0.1';
		}

		if(version_compare($cur_ver, '1.0.2', '<')){
			//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			$tableName = RevSliderGlobals::TABLE_SLIDERS_NAME;
			
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  title tinytext NOT NULL,
						  alias tinytext,
                          params LONGTEXT NOT NULL,
						  settings text NULL,
						  UNIQUE KEY id (id)
						);";
			self::$query->dbDelta($sql);

			self::$framework->update_option('revslider_table_version', '1.0.2');
			$cur_ver = '1.0.2';
		}
		
		if(version_compare($cur_ver, '1.0.3', '<')){
			//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			$tableName = RevSliderGlobals::TABLE_CSS_NAME;
			
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  handle TEXT NOT NULL,
                          settings LONGTEXT,
                          hover LONGTEXT,
						  advanced MEDIUMTEXT,
						  params TEXT NOT NULL,
						  UNIQUE KEY id (id)
						);";
			self::$query->dbDelta($sql);
			
			self::$framework->update_option('revslider_table_version', '1.0.3');
			$cur_ver = '1.0.3';
		}
		
		if(version_compare($cur_ver, '1.0.4', '<')){
			//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_SLIDERS_NAME ." (
					  UNIQUE KEY id (id)
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_SLIDES_NAME ." (
					  UNIQUE KEY id (id)
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_STATIC_SLIDES_NAME ." (
					  UNIQUE KEY id (id)
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_CSS_NAME ." (
					  UNIQUE KEY id (id)
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_LAYER_ANIMS_NAME ." (
					  UNIQUE KEY id (id)
					);";
			self::$query->dbDelta($sql);
			
			self::$framework->update_option('revslider_table_version', '1.0.4');
			$cur_ver = '1.0.4';
		}
		
		if(version_compare($cur_ver, '1.0.5', '<')){
			//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			
            $sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_LAYER_ANIMS_NAME ." (
                      settings text NULL
                    );";
            self::$query->dbDelta($sql);
            
            self::$framework->update_option('revslider_table_version', '1.0.5');
            $cur_ver = '1.0.5';
        }
        
        if(version_compare($cur_ver, '1.0.6', '<')){
            //require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_SLIDERS_NAME ." (
                     type VARCHAR(191) NOT NULL DEFAULT '',
                     params LONGTEXT NOT NULL
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_SLIDES_NAME ." (
                      settings text NOT NULL DEFAULT '',
                      params LONGTEXT NOT NULL,
                      layers LONGTEXT NOT NULL
					);";
			self::$query->dbDelta($sql);
			$sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_STATIC_SLIDES_NAME ." (
                      params LONGTEXT NOT NULL,
                      layers LONGTEXT NOT NULL
					);";
			self::$query->dbDelta($sql);
            $sql = "CREATE TABLE " .self::$table_prefix.RevSliderGlobals::TABLE_CSS_NAME ." (
                      advanced LONGTEXT
					);";
			self::$query->dbDelta($sql);
			
            self::$framework->update_option('revslider_table_version', '1.0.6');
            $cur_ver = '1.0.6';
		}

	}
	
	
	/**
	 * create tables
	 */
	public static function createTable($tableName){

		$parseCssToDb = false;

		$checkForTablesOneTime = self::$framework->get_option('revslider_checktables', '0');
		
		if($checkForTablesOneTime == '0'){
			self::$framework->update_option('revslider_checktables', '1');
			if(RevSliderFunctionsWP::isDBTableExists(self::$table_prefix.RevSliderGlobals::TABLE_CSS_NAME)){ //$wpdb->tables( 'global' )
				//check if database is empty
				$result = $wpdb->get_row("SELECT COUNT( DISTINCT id ) AS NumberOfEntrys FROM ".self::$table_prefix.RevSliderGlobals::TABLE_CSS_NAME);
				if($result->NumberOfEntrys == 0) $parseCssToDb = true;
			}
		}

		if($parseCssToDb){
			$RevSliderOperations = new RevSliderOperations(self::$framework, self::$query, self::$curl, self::$filesystem, self::$images, self::$resource, self::$googleFonts);
			$RevSliderOperations->importCaptionsCssContentArray();
			$RevSliderOperations->moveOldCaptionsCss();
		}
        
        if(!self::$framework->get_option('revslider_change_database', false)){
            //if table exists - don't create it.
            $tableRealName = self::$table_prefix.$tableName;
            if(RevSliderFunctionsWP::isDBTableExists($tableRealName))
                return(false);
        }
        
		switch($tableName){
			case RevSliderGlobals::TABLE_SLIDERS_NAME:
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  title tinytext NOT NULL,
						  alias tinytext,
                          params LONGTEXT NOT NULL,
						  UNIQUE KEY id (id)
						);";
			break;
			case RevSliderGlobals::TABLE_SLIDES_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  slider_id int(9) NOT NULL,
							  slide_order int not NULL,
                              params LONGTEXT NOT NULL,
                              layers LONGTEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			case RevSliderGlobals::TABLE_STATIC_SLIDES_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  slider_id int(9) NOT NULL,
                              params LONGTEXT NOT NULL,
                              layers LONGTEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			case RevSliderGlobals::TABLE_CSS_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  handle TEXT NOT NULL,
                              settings LONGTEXT,
                              hover LONGTEXT,
                              params LONGTEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
				$parseCssToDb = true;
			break;
			case RevSliderGlobals::TABLE_LAYER_ANIMS_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  handle TEXT NOT NULL,
							  params TEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			case RevSliderGlobals::TABLE_NAVIGATION_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  name VARCHAR(191) NOT NULL,
							  handle VARCHAR(191) NOT NULL,
                              css LONGTEXT NOT NULL,
                              markup LONGTEXT NOT NULL,
                              settings LONGTEXT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			default:
				RevSliderFunctions::throwError("table: $tableName not found");
			break;
		}
		
		//require_once(Framework::ABSPATH . 'wp-admin/includes/upgrade.php');
		self::$query->dbDelta($sql);
		
        if(!self::$framework->get_option('revslider_change_database', false)){
            if($parseCssToDb){
                $RevSliderOperations = new RevSliderOperations(self::$framework, self::$query, self::$curl, self::$filesystem, self::$images, self::$resource, self::$googleFonts);
                $RevSliderOperations->importCaptionsCssContentArray();
                $RevSliderOperations->moveOldCaptionsCss();
            }
		}

	}
	
	
	
	public function enqueue_styles(){
		
	}
	
	
	/**
	 * Change FontURL to new URL (added for chinese support since google is blocked there)
	 * @since: 5.0
	 */
	public static function modify_punch_url($url){
		$operations = new RevSliderOperations(self::$framework, self::$query, self::$curl, self::$filesystem, self::$images, self::$resource, self::$googleFonts);
		$arrValues = $operations->getGeneralSettingsValues();
		
		$set_diff_font = RevSliderFunctions::getVal($arrValues, "change_font_loading",'');
		if($set_diff_font !== ''){
			return $set_diff_font;
		}else{
			return $url;
		}
	}
	
	
	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.0
	 */
	public static function add_meta_generator(){
		$revSliderVersion = RevSliderGlobals::SLIDER_REVISION;
		
		echo self::$framework->apply_filters('revslider_meta_generator', '<meta name="generator" content="Powered by Slider Revolution '.$revSliderVersion.' - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />'."\n");
	}

	/**
	 *
	 * adds async loading
	 * @since: 5.0
	 */
	public static function add_defer_forscript($url)
	{
	    if ( strpos($url, 'themepunch.enablelog.js' )===false && strpos($url, 'themepunch.revolution.min.js' )===false  && strpos($url, 'themepunch.tools.min.js' )===false )
	        return $url;
	    else if (is_admin())
	        return $url;
	    else
	        return $url."' defer='defer"; 
	}
	
}