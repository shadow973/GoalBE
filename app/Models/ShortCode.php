<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortCode extends Model
{

	public static function ContentShortcode($content){

		$regex = "/\[(.*?)\]/";
		preg_match_all($regex, $content, $matches);
		$matches = current($matches);

		for($i = 0; $i < count($matches); $i++)
		{
	    	$match = $matches[$i];

	    	$sortcode_content = self::do_shortcode($match);

			$content = str_replace($match, $sortcode_content, $content);
		}
		return $content;
	}

	public static function do_shortcode($match){
		$data = self::shortcode_parse_atts($match);
		$code_content = '';
		if(!isset($data['type'])){
		    return $code_content;
        }
		switch ($data['type']) {
			case 'video':
				$code_content = \App\Models\NewVideos::generateShortcode($data);
				break;
			case 'poll':
				$code_content = \App\Models\NewPolls::generateShortcode($data);
				break;
			case 'album':
				$code_content = \App\GalleryAlbum::generateShortcode($data);
				break;	
			
		}

		return $code_content;
	}
     
	public static function shortcode_parse_atts( $text ) {
		$atts    = array();
		$pattern = self::get_shortcode_atts_regex();
		$text    = self::GetBetween(  $text,  '[', ']' );
		if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
			foreach ( $match as $m ) {
				if ( ! empty( $m[1] ) ) {
                    $atts[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
				} elseif ( ! empty( $m[3] ) ) {
					$atts[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
				} elseif ( ! empty( $m[5] ) ) {
					$atts[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
				} elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
					$atts[] = stripcslashes( $m[7] );
				} elseif ( isset( $m[8] ) && strlen( $m[8] ) ) {
					$atts[] = stripcslashes( $m[8] );
				} elseif ( isset( $m[9] ) ) {
					$atts[] = stripcslashes( $m[9] );
				}
			}

			// Reject any unclosed HTML elements
			foreach ( $atts as &$value ) {
				if ( false !== strpos( $value, '<' ) ) {
					if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
						$value = '';
					}
				}
			}
		} else {
			$atts = ltrim( $text );
		}
		return $atts;
	}

	public static function get_shortcode_atts_regex() {
		return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
	}

	public static function GetBetween($content,$start,$end){
	    $r = explode($start, $content);
	    if (isset($r[1])){
	        $r = explode($end, $r[1]);
	        return $r[0];
	    }
	    return '';
	}
}
