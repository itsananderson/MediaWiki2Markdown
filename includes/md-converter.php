<?php

class MD_Converter {

	private $in_header_section;
	private $new_lines = array();

	public function MD_Converter() {
		$this->reset();
	}

	private function reset() {
		$this->in_header_section = false;
		$this->new_lines = array();
	}

	public function convert( $content ) {
		$lines = preg_split( "/\r?\n/", $content );

		foreach ( $lines as $line_string ) {
			$matches = array();
			if ( preg_match( "/=== (.+) ===/", $line_string, $matches ) ) {
				$this->in_header_section = true;
				$this->new_lines[] = $matches[1];
				$this->new_lines[] = preg_replace( '/./', '=', $matches[1] );
			} elseif ( preg_match( "/== (.+) ==/", $line_string, $matches ) ) {
				$this->in_header_section = false;
				$this->new_lines[] = $matches[1];
				$this->new_lines[] = preg_replace( '/./', '-', $matches[1] );
			} elseif ( preg_match( "/= (.+) =/", $line_string, $matches ) ) {
				$this->new_lines[] = '#### ' . $matches[1] . ' ####';
			} elseif ( self::is_header_line( $line_string ) ) {
				$this->new_lines[] = '* ' . self::format_header_line($line_string);
			} else {
				$this->new_lines[] = $line_string;
			}
		}

		return implode( "\n", $this->new_lines );
	}

	private function is_header_line( $line ) {

		// If we're not in the header section, there's no way this is a header line
		if ( !$this->in_header_section ) {
			return false;
		}

		// Possible header lines
		$header_lines = array(
			'contributors',
			'donate link',
			'tags',
			'requires at least',
			'tested up to',
			'stable tag',
			'license',
			'license uri'
		);

		// Loop through possible header lines and check if they match
		foreach ( $header_lines as $header_line ) {
			$regex = '/' . $header_line . ':.*/i';
			if ( 1 === preg_match( $regex, $line ) ) {
				return true;
			}
		}
		return false;
	}

	private function format_header_line( $line ) {
		if ( false !== stripos( $line, 'Tags:' ) ) {
			$tags = preg_split( '/,\s*/', trim( preg_replace( '/Tags:\s*/i', '', $line ) ) );
			$taglinks = array();
			foreach ( $tags as $tag ) {
				$taglinks[] = "[$tag](http://wordpress.org/extend/plugins/tags/$tag)";
			}
			return 'Tags: ' . implode( ', ', $taglinks );
		}
		return $line;
	}
}