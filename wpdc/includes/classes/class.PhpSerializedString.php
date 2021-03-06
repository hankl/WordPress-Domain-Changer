<?php
class PhpSerializedString {

    public $string = "";

    public function __construct( $string ) {
        $this->string = $string;
    }

    /**
     * Replace $find with $replace in a string segment and still keep the integrity of the PHP serialized string.
     *
     * Example:
     *   $sps = new SerializedString('s:13:"look a string"')
     *   $sps->replace('string', 'function')->toString() => s:15:"look a function"
     *
     *
     * @param string;
     * @param string;
     * @return string;
     */
    public function replace( $find, $replace ) {
        while ( $this->string != ( $changed_string = $this->replaceFirstOccurance( $find, $replace, $this->string ) ) ) {
            $this->string = $changed_string;
        }
        return $this;
    }

    /**
     * Returns true if a php serialized string is contained in $string.
     *
     * @param string;
     * @return boolean;
     */
    public static function detect( $string ) {
        return preg_match( '/s:[0-9]+:".*";/', $string );
    }

    public function toString() {
        return $this->string;
    }

    protected function replaceFirstOccurance( $find, $replace, $string, $after_offset = 0 ) {
        $length_diff     = mb_strlen( $replace ) - mb_strlen( $find );
        $offset_of_find_match = mb_stripos( $string, $find, $after_offset );


        if ( $offset_of_find_match !== false ) {
            // Prevent Infinet loops when working with $find => localhost:8000, $replace => localhost:80001
            $offset_of_replace_match = mb_stripos( $string, $replace, $after_offset );
            if ( $offset_of_replace_match == $offset_of_find_match ) {
                return $this->replaceFirstOccurance( $find, $replace, $string, $offset_of_replace_match + mb_strlen( $replace ) );
            }

            $characters = $this->getCharacters( $string );

            // Substring Find & Replace
            $replacement = $this->getCharacters( $replace );
            $length      = mb_strlen( $find );
            array_splice( $characters, $offset_of_find_match, $length, $replacement );

            // Serialized string definition's length
            $offset_of_def    = $this->getStringDefinitionOffset( $characters, $offset_of_find_match );
            $offset_of_length = $offset_of_def + 2;

            preg_match( '/s:([0-9]+)/', $this->getStringDefinition( $characters, $offset_of_def ), $matches );

            $old_length = (int) $matches[1];
            $new_length = $old_length + $length_diff;

            $replacement = $this->getCharacters( (string) $new_length );
            $length      = mb_strlen( (string) $old_length );
            array_splice( $characters, $offset_of_length, $length, $replacement );

            $string = implode( "", $characters );
        }
        return $string;
    }

    protected function getStringDefinition( $characters, $offset ) {
        $string = "";
        $count  = count( $characters );
        for ( $i = $offset; $i <= $count; $i++ ) {
            $string .= $characters[$i];
            if ( preg_match( "/^s:[0-9]+:$/", $string ) ) break;
        }
        return $string;
    }

    protected function getStringDefinitionOffset( $characters, $offset ) {
        for ( $i = $offset; $i > 0; $i-- ) if ( preg_match( "/s:[0-9]/", implode( "", array_slice( $characters, $i, 3 ) ) ) ) return $i;
            return false;
    }

    protected function getCharacters( $string ) {
        return preg_split( '/(?<!^)(?!$)/u', $string );
    }

}
