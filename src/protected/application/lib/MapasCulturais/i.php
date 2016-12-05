<?php
namespace MapasCulturais;

use POMO\MO;

/**
 * The MapasCulturais internationalization class.
 *
 * This class uses the POMO class
 *
 */
class i {

    static function get_locale() {
        $app = App::i();
        return $app->config['app.lcode'];
    }

    /**
     * Load default translated strings based on locale.
     *
     * Loads the .mo file in WP_LANG_DIR constant path from WordPress root.
     * The translated (.mo) file is named based on the locale.
     *
     * @see load_textdomain()
     *
     * @since 1.5.0
     *
     * @param string $locale Optional. Locale to load. Default is the value of {@see get_locale()}.
     * @return bool Whether the textdomain was loaded.
     */
    static function load_default_textdomain( $locale = null ) {
    	if ( null === $locale ) {
    		$locale = self::get_locale();
    	}

    	// Unload previously loaded strings so we can switch translations.
    	self::unload_textdomain( 'default' );

    	$return = self::load_textdomain( 'default', LANGUAGES_PATH . "/$locale/LC_MESSAGES/messages.mo" );

    	// Load Base Theme into default domain

    	self::load_textdomain( 'default', THEMES_PATH . "/BaseV1/languages/$locale.mo" );

    	return $return;
    }

    /**
     * Retrieve the translation of $text.
     *
     * If there is no translation, or the text domain isn't loaded, the original text is returned.
     *
     * *Note:* Don't use {@see translate()} directly, use `{@see __()} or related functions.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text
     */
    static function translate( $text, $domain = 'default' ) {
    	$translations = self::get_translations_for_domain( $domain );
    	$translations = $translations->translate( $text );
        return $translations;
    }

    /**
     * Retrieve the translation of $text in the context defined in $context.
     *
     * If there is no translation, or the text domain isn't loaded the original
     * text is returned.
     *
     * @param string $text    Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text on success, original text on failure.
     */
    static function translate_with_gettext_context( $text, $context, $domain = 'default' ) {
    	$translations = self::get_translations_for_domain( $domain );
    	$translations = $translations->translate( $text, $context );
        return $translations;
    }

    /**
     * Retrieve the translation of $text. If there is no translation,
     * or the text domain isn't loaded, the original text is returned.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text.
     */
    static function __( $text, $domain = 'default' ) {
    	return self::translate( $text, $domain );
    }

    /**
     * Retrieve the translation of $text and escapes it for safe use in an attribute.
     *
     * If there is no translation, or the text domain isn't loaded, the original text is returned.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text on success, original text on failure.
     */
    static function esc_attr__( $text, $domain = 'default' ) {
        return htmlspecialchars(self::translate( $text, $domain )) ;
    }

    /**
     * Retrieve the translation of $text and escapes it for safe use in HTML output.
     *
     * If there is no translation, or the text domain isn't loaded, the original text is returned.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text
     */
    static function esc_html__( $text, $domain = 'default' ) {
    	return esc_html( self::translate( $text, $domain ) );
    }

    /**
     * Display translated text.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     */
    static function _e( $text, $domain = 'default' ) {
    	echo self::translate( $text, $domain );
    }
    
    /**
     * Display translated text that has been escaped for safe use in an attribute.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     */
    static function esc_attr_e( $text, $domain = 'default' ) {
        echo self::esc_attr__( $text, $domain ) ;
    }

    /**
     * Display translated text that has been escaped for safe use in HTML output.
     *
     * @param string $text   Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     */
    static function esc_html_e( $text, $domain = 'default' ) {
    	echo esc_html( self::translate( $text, $domain ) );
    }

    /**
     * Retrieve translated string with gettext context.
     *
     * Quite a few times, there will be collisions with similar translatable text
     * found in more than two places, but with different translated context.
     *
     * By including the context in the pot file, translators can translate the two
     * strings differently.
     *
     * @param string $text    Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated context string without pipe.
     */
    static function _x( $text, $context, $domain = 'default' ) {
    	return self::translate_with_gettext_context( $text, $context, $domain );
    }

    /**
     * Display translated string with gettext context.
     *
     * @param string $text    Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated context string without pipe.
     */
    static function _ex( $text, $context, $domain = 'default' ) {
    	echo _x( $text, $context, $domain );
    }

    /**
     * Translate string with gettext context, and escapes it for safe use in an attribute.
     *
     * @param string $text    Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text
     */
    static function esc_attr_x( $text, $context, $domain = 'default' ) {
    	return esc_attr( self::translate_with_gettext_context( $text, $context, $domain ) );
    }

    /**
     * Translate string with gettext context, and escapes it for safe use in HTML output.
     *
     * @param string $text    Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Translated text.
     */
    static function esc_html_x( $text, $context, $domain = 'default' ) {
    	return esc_html( self::translate_with_gettext_context( $text, $context, $domain ) );
    }

    /**
     * Retrieve the plural or single form based on the supplied amount.
     *
     * If the text domain is not set in the $l10n list, then a comparison will be made
     * and either $plural or $single parameters returned.
     *
     * If the text domain does exist, then the parameters $single, $plural, and $number
     * will first be passed to the text domain's ngettext method. Then it will be passed
     * to the 'ngettext' filter hook along with the same parameters. The expected
     * type will be a string.
     *
     * @param string $single The text that will be used if $number is 1.
     * @param string $plural The text that will be used if $number is not 1.
     * @param int    $number The number to compare against to use either $single or $plural.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Either $single or $plural translated text.
     */
    static function _n( $single, $plural, $number, $domain = 'default' ) {
    	$translations = self::get_translations_for_domain( $domain );
    	$translation = $translations->translate_plural( $single, $plural, $number );
    	return $translation;
    }

    /**
     * Retrieve the plural or single form based on the supplied amount with gettext context.
     *
     * This is a hybrid of _n() and _x(). It supports contexts and plurals.
     *
     * @param string $single  The text that will be used if $number is 1.
     * @param string $plural  The text that will be used if $number is not 1.
     * @param int    $number  The number to compare against to use either $single or $plural.
     * @param string $context Context information for the translators.
     * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return string Either $single or $plural translated text with context.
     */
    static function _nx($single, $plural, $number, $context, $domain = 'default') {
    	$translations = self::get_translations_for_domain( $domain );
    	$translation = $translations->translate_plural( $single, $plural, $number, $context );
    	return $translation;
    }

    /**
     * Register plural strings in POT file, but don't translate them.
     *
     * Used when you want to keep structures with translatable plural
     * strings and use them later.
     *
     * Example:
     *
     *     $messages = array(
     *      	'post' => _n_noop( '%s post', '%s posts' ),
     *      	'page' => _n_noop( '%s pages', '%s pages' ),
     *     );
     *     ...
     *     $message = $messages[ $type ];
     *     $usable_text = sprintf( translate_nooped_plural( $message, $count ), $count );
     *
     * @param string $singular Single form to be i18ned.
     * @param string $plural   Plural form to be i18ned.
     * @param string $domain   Optional. Text domain. Unique identifier for retrieving translated strings.
     * @return array array($singular, $plural)
     */
    static function _n_noop( $singular, $plural, $domain = null ) {
    	return array( 0 => $singular, 1 => $plural, 'singular' => $singular, 'plural' => $plural, 'context' => null, 'domain' => $domain );
    }

    /**
     * Register plural strings with context in POT file, but don't translate them.
     *
     * @param string $singular
     * @param string $plural
     * @param string $context
     * @param string|null $domain
     * @return array
     */
    static function _nx_noop( $singular, $plural, $context, $domain = null ) {
    	return array( 0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context, 'domain' => $domain );
    }

    /**
     * Translate the result of _n_noop() or _nx_noop().
     *
     * @param array  $nooped_plural Array with singular, plural and context keys, usually the result of _n_noop() or _nx_noop()
     * @param int    $count         Number of objects
     * @param string $domain        Optional. Text domain. Unique identifier for retrieving translated strings. If $nooped_plural contains
     *                              a text domain passed to _n_noop() or _nx_noop(), it will override this value.
     * @return string Either $single or $plural translated text.
     */
    static function translate_nooped_plural( $nooped_plural, $count, $domain = 'default' ) {
    	if ( $nooped_plural['domain'] )
    		$domain = $nooped_plural['domain'];

    	if ( $nooped_plural['context'] )
    		return self::_nx( $nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain );
    	else
    		return self::_n( $nooped_plural['singular'], $nooped_plural['plural'], $count, $domain );
    }

    /**
     * Load a .mo file into the text domain $domain.
     *
     * If the text domain already exists, the translations will be merged. If both
     * sets have the same string, the translation from the original value will be taken.
     *
     * On success, the .mo file will be placed in the $l10n global by $domain
     * and will be a MO object.
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @param string $mofile Path to the .mo file.
     * @return bool True on success, false on failure.
     */
    static function load_textdomain( $domain, $mofile ) {
        global $i18n;
    	if ( !is_readable( $mofile ) ) return false;

    	$mo = new MO();
    	if ( !$mo->import_from_file( $mofile ) ) return false;

    	if ( isset( $i18n[$domain] ) )
    		$mo->merge_with( $i18n[$domain] );

    	$i18n[$domain] = &$mo;

    	return true;
    }

    /**
     * Unload translations for a text domain.
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @return bool Whether textdomain was unloaded.
     */
    static function unload_textdomain( $domain ) {
        global $i18n;
    	if ( isset( $i18n[$domain] ) ) {
    		unset( $i18n[$domain] );
    		return true;
    	}

    	return false;
    }


    /**
     * Return the Translations instance for a text domain.
     *
     * If there isn't one, returns empty Translations instance.
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @return NOOP_Translations A Translations instance.
     */
    static function get_translations_for_domain( $domain ) {
        global $i18n;
        if ( !isset( $i18n[$domain] ) ) {
    		$i18n[$domain] = new \POMO\Translations\NOOPTranslations;
    	}
    	return $i18n[$domain];
    }

    /**
     * Whether there are translations for the text domain.
     *
     * @since 3.0.0
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @return bool Whether there are translations.
     */
    static function is_textdomain_loaded( $domain ) {
        global $i18n;
        return isset( $i18n[$domain] );
    }

}
