<?php
// NEW VERSION 6


$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 

define('FPDF_FONTPATH', plugin_dir_path( __FILE__ ) . '/vendor/setasign/fpdf/font/'); // Maybe not
require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . '/vendor/setasign/fpdi/src/autoload.php';
require_once plugin_dir_path( __FILE__ ) . '/vendor/setasign/tfpdf/tfpdf.php'; // New one


use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class BadgeBuilder {

    //Folder
    public static $badge_folder = ABSPATH . 'wp-content/plugins/eventer/badges';
    public static $badge_folder_url = '/wp-content/plugins/eventer/badges';

    // Outputs
    public $id;
    public $badge_output;
    public $errors;

    // Inputs
    private $is_single_badge;
    private $registration_id;
    private $first_name;
    private $last_name;
    private $role;
    private $company;

    // Predefined coordinates for badge fields
    private $page_1_x_coord;
    private $page_1_y_coord;
    private $page_2_x_coord;
    private $page_2_y_coord;
    private $template_base;

    private $name_format;
    private $job_format;
    private $company_format;

    // Computed props
    public $badge_filename;
    public $template_file;
    


    function __construct($id, $single) {
        $this->is_single_badge = $single;
        $this->registration_id = $id;
        $this->id = $id;
        $this->errors = array();
        $this->badge_output = array();

        self::make_the_badge_folder();

        $this->get_registration_data();

        $this->set_up_badge_vars();

        $this->grab_badge_formatting();


        if(empty($this->errors)) {

            $this->set_badge_url();
            $this->set_badge_filename();
            $this->build_badge();

        }

        if($this->is_single_badge === true) {
            $this->store_badge();
        }

        $this->set_database_record_to_printed();

    }



    private function grab_badge_formatting() {

        $this->name_format = unserialize(get_option('badge_name_format'));
        $this->job_format = unserialize(get_option('badge_job_format'));
        $this->company_format = unserialize(get_option('badge_company_format'));

    }


    private function set_badge_filename() {

        $initial = substr($this->first_name,0,1);
        $last = preg_replace('/[^A-Za-z0-9\-]/', '', $this->last_name);
        $comp = preg_replace('/[^A-Za-z0-9\-]/', '', $this->company);
        $t = time();
        $time = substr($t,4, 5);

        $this->badge_filename = $initial . "_" . $last . "_" . $comp . "_" . $time . ".pdf";

    }


    private function set_badge_url() {

        $template_url = parse_url($this->template_base);
        $path_fix = ltrim($template_url['path'], '/');
        
        if(isset($template_url['query']) && !empty($template_url['query'])) {
    	    $this->template_file = ABSPATH . $path_fix . $template_url['query'];
	    } else {
		    $this->template_file = ABSPATH . $path_fix;
	    }

    }


    private function set_up_badge_vars() {

        if(empty(get_option('badge_x'))) {
            $this->errors[] = "Page 1 x co-ordinate not set";
        } else {
            $this->page_1_x_coord = get_option('badge_x');
        }
        if(empty(get_option('badge_x_p2'))) {
            $this->errors[] = "Page 2 x co-ordinate not set";
        } else {
            $this->page_2_x_coord = get_option('badge_x_p2');
        }
        if(empty(get_option('badge_y'))) {
            $this->errors[] = "Page 1 y co-ordinate not set";
        } else {
            $this->page_1_y_coord = get_option('badge_y');
        }
        if(empty(get_option('badge_y_p2'))) {
            $this->errors[] = "Page 2 y co-ordinate not set";
        } else {
            $this->page_2_y_coord =  get_option('badge_y_p2');
        }
        if(empty(get_option('badge_template'))) {
            $this->errors[] = "Cannot find template file for badge";
        } else {
            $this->template_base = get_option('badge_template');
        }

    }


    private function get_registration_data() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'registrations';
        $sql = "SELECT * FROM $table_name WHERE id = $this->registration_id";
        $result = $wpdb->get_results($sql);

        if (!empty($result)) {
            $this->first_name = $this->rationalize_text(strip_tags(trim($result[0]->name)));
            $this->last_name = $this->rationalize_text(strip_tags(trim($result[0]->surname)));
            $this->role = $this->rationalize_text(strip_tags(trim($result[0]->role)));
            $this->company = $this->rationalize_text(strip_tags(trim($result[0]->company)));
        } else {
            $this->errors[] = "Could not find registration data for this visitor";
        }

    }


    private function rationalize_text($original_text) {

        // Very possibly overkill....
        //$fixed_text = $this->replace_mangled_chars($original_text);
       // $original_text = htmlentities($original_text, ENT_QUOTES, 'UTF-8', false);
		//setlocale(LC_CTYPE, 'en_US');
        //$original_text = iconv('utf-8', 'cp1252//TRANSLIT', $original_text);
        //$original_text = htmlspecialchars($original_text, ENT_QUOTES, 'UTF-8', false);
		//$original_text = iconv('UTF-8', 'windows-1252', html_entity_decode($original_text));
		//$original_text = iconv('utf-8', 'cp1252', $original_text);
		//$original_text = iconv('utf-8', 'iso-8859-2', $original_text);
		//$original_text = iconv('UTF-8','iso-8859-2//TRANSLIT//IGNORE',$original_text); 
		//$original_text = mb_convert_encoding($original_text, 'cp1252', 'UTF-8');
		//$original_text = iconv('UTF-8', 'windows-1252', $original_text);
		// $original_text = iconv('UTF-8', 'windows-1252', $original_text);
		//$original_text = utf8_decode($original_text);		
		
        return $original_text;

    }

	
	private function replace_mangled_chars($text) {
		

		$input = array('&amp;', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
  		$output = array('&', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	
		$new_text = str_replace($input, $output, $text);
		
		return $new_text;
			
	}
	


    private function build_badge() {

        // Instantiate
        //$pdf = new FPDI(); 
        $pdf = new tFPDF();
		$pdf->AddPage(); 
		//$pdf->AddFont('NotoSans-Regular','','NotoSans-Regular.php');
		$pdf->AddFont('NotoSans-Black','B','NotoSans-Black.ttf', true);
		        
        // Create page
        $pdf->setSourceFile($this->template_file); 
        $tplIdx = $pdf->importPage(1); 
        $pdf->useTemplate($tplIdx, 0, 0); 

        // Stored colors
        $black = array(0,0,0);
        $dark = array(35,35,35);
        $luxury = array(210, 189, 142);
        $dagora_blue = array(0,132,203);
        $dagora_green = array(140, 198, 63);



        $name = array(
            'width' => 85,
            'height' => 10,
            'color' => $black,
            'font_size' => 15,
            'caps' => true,
            'text' => $this->first_name . "\n" . $this->last_name,
            'reset' => true,
            'align' => 'L',
        );
        $job = array(
            'width' => 85,
            'height' => 8,
            'color' => $black,
            'font_size' => 10,
            'caps' => false,
            'text' => $this->role,
            'reset' => false,
            'align' => 'L',
        );
        $company = array(
            'width' => 85,
            'height' => 8,
            'color' => $dagora_green,
            'font_size' => 12,
            'caps' => true,
            'text' => $this->company,
            'reset' => false,
            'align' => 'L',
        );  


        // See if we've already saved out some formatting options
        if(!empty($this->name_format)) {
            if(!empty($this->name_format['color'])) {

                switch ($this->name_format['color']) {
                    case 'black':
                        $name['color'] = $black;
                        break;
                    
                    case 'dark':
                        $name['color'] = $dark;
                        break;

                    case 'luxury':
                        $name['color'] = $luxury;
                        break;
                    
                    case 'blue':
                        $name['color'] = $dagora_blue;
                        break;

                    case 'green':
                        $name['color'] = $dagora_green;
                        break;
                    
                    default:
                        $name['color'] = $name['color'];
                        break;
                }

            } 
            if(!empty($this->name_format['fontsize'])) {
                $name['font_size'] = $this->name_format['fontsize'];
            }
            if(!empty($this->name_format['caps'])) {                
                if($this->name_format['caps'] == 'yes') $name['caps'] = true;
                if($this->name_format['caps'] == 'no') $name['caps'] = false;
            }
            if(!empty($this->name_format['align'])) {
                $name['align'] = $this->name_format['align'];
            }
        }

        if(!empty($this->job_format)) {
            if(!empty($this->job_format['color'])) {

                switch ($this->job_format['color']) {
                    case 'black':
                        $job['color'] = $black;
                        break;
                    
                    case 'dark':
                        $job['color'] = $dark;
                        break;

                    case 'luxury':
                        $job['color'] = $luxury;
                        break;
                    
                    case 'dagora_blue':
                        $job['color'] = $dagora_blue;
                        break;

                    case 'dagora_green':
                        $job['color'] = $dagora_green;
                        break;
                    
                    default:
                        $job['color'] = $job['color'];
                        break;
                }

            } 
            if(!empty($this->job_format['fontsize'])) {
                $job['font_size'] = $this->job_format['fontsize'];
            }
            if(!empty($this->job_format['caps'])) {                
                if($this->job_format['caps'] == 'yes') $job['caps'] = true;
                if($this->job_format['caps'] == 'no') $job['caps'] = false;
            }
            if(!empty($this->job_format['align'])) {
                $job['align'] = $this->job_format['align'];
            }
        }

        if(!empty($this->company_format)) {
            if(!empty($this->company_format['color'])) {

                switch ($this->company_format['color']) {
                    case 'black':
                        $company['color'] = $black;
                        break;
                    
                    case 'dark':
                        $company['color'] = $dark;
                        break;

                    case 'luxury':
                        $company['color'] = $luxury;
                        break;
                    
                    case 'blue':
                        $company['color'] = $dagora_blue;
                        break;

                    case 'green':
                        $company['color'] = $dagora_green;
                        break;
                    
                    default:
                        $company['color'] = $company['color'];
                        break;
                }

            } 
            if(!empty($this->company_format['fontsize'])) {
                $company['font_size'] = $this->company_format['fontsize'];
            }
            if(!empty($this->company_format['caps'])) {                
                if($this->company_format['caps'] == 'yes') {
					$company['caps'] = true;
				} else {
					$company['caps'] = false;
				}  
            }
            if(!empty($this->company_format['align'])) {
                $company['align'] = $this->company_format['align'];
            }
        }



        // Write name on p1
        $this->print_badge_text($name, $this->page_1_x_coord, $this->page_1_y_coord, $pdf);

        // Write job on p1
        $this->print_badge_text($job, $this->page_1_x_coord, $this->page_1_y_coord, $pdf);

        // Write company on p1
        $this->print_badge_text($company, $this->page_1_x_coord, $this->page_1_y_coord, $pdf);


        // Write name on p2
        $this->print_badge_text($name, $this->page_2_x_coord, $this->page_2_y_coord, $pdf);

        // Write job on p2
        $this->print_badge_text($job, $this->page_2_x_coord, $this->page_2_y_coord, $pdf);

        // Write company on p2
        $this->print_badge_text($company, $this->page_2_x_coord, $this->page_2_y_coord, $pdf);


        // Storing URL of file to download in the object output var
        if($this->is_single_badge === true) {
            $pdf->Output(self::$badge_folder . '/' . $this->badge_filename, 'F');
            $this->badge_output = site_url() . self::$badge_folder_url . "/" . $this->badge_filename;
        } else {
            if (!file_exists(self::$badge_folder . "/temp/")) {
                mkdir(self::$badge_folder . "/temp/");
            }
            $pdf->Output(self::$badge_folder . '/temp/' . $this->badge_filename, 'F');
        }
    }

    


    private function print_badge_text($opts, $x, $y, $pdf) {

		//$opts['text'] = $this->replace_mangled_chars($opts['text']);
		
        if($opts['caps'] === true) {
            $opts['text'] = strtoupper($opts['text']);
        }
        
        $pdf->SetTextColor($opts['color'][0],$opts['color'][1],$opts['color'][2]);
		$pdf->SetFont('NotoSans-Black', 'B', $opts['font_size']);

        // Only usually reset both x and y for name field, i.e. first element of new page, else just x
        if($opts['reset'] === true) {
            $pdf->SetXY($x, $y);
        } else {
            $pdf->SetX($x);
        }

        $pdf->Multicell($opts['width'], $opts['height'], $opts['text'], 0, $opts['align'], false); 
            
    }



    public static function kill_the_old_badges($dir = null) {

        if($dir === null) {

            unlink(self::$badge_folder . "/all_badges.zip");
        
        } else {
        
            foreach(glob($dir . '/*') as $file) {
                if(is_dir($file)) {
                    
                    self::kill_the_old_badges($file);
                
                } else { 

                    unlink($file);

                }
            }
        }

        rmdir($dir);

    }

    public static function make_the_badge_folder() {
        if (!file_exists(self::$badge_folder . '/')) {
            mkdir(self::$badge_folder . '/');
        }
    }

    public static function zipOutput() {
        // We have a hard-coded folder where we store output badges.
        // On command, we can simply zip up that folder and return the URL to access it.
        // We do not need to know files or anything, just the folder name.
       
        $the_folder = self::$badge_folder . "/temp/";
        $zip_file_name = self::$badge_folder . '/all_badges.zip';
        
        $za = new FlxZipArchive;
        $res = $za->open($zip_file_name, ZipArchive::CREATE);
        if($res === TRUE) {
            $za->addDir($the_folder, basename($the_folder));
            $za->close();

            self::kill_the_old_badges(self::$badge_folder . "/temp/");

            //return $zip_file_name; TESTING WITH URL NOT FILE
            return site_url() . self::$badge_folder_url . '/all_badges.zip';
        } else {
            return 'Could not create a zip archive';
        }


    }


    private function store_badge() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'registrations';
        $sql = "UPDATE $table_name SET badge_link = '$this->badge_output' WHERE id = $this->registration_id";
        $wpdb->query($sql);


    }


    private function set_database_record_to_printed() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'registrations';
        $sql = "UPDATE $table_name SET printed = 1 WHERE id = $this->registration_id";
        $wpdb->query($sql);

    }


}

class FlxZipArchive extends ZipArchive {
    public function addDir($location, $name) {
          $this->addEmptyDir($name);
          $this->addDirDo($location, $name);
    } 
    private function addDirDo($location, $name) {
       $name .= '/';
       $location .= '/';
       $dir = opendir ($location);
       while ($file = readdir($dir)) {
           if ($file == '.' || $file == '..') continue;
           $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
           $this->$do($location . $file, $name . $file);
       }
    } 
   }
