<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class InstallDocFormatterTest extends MediaWikiTestCase {
	/**
	 * @covers InstallDocFormatter::format
	 * @dataProvider provideDocFormattingTests
	 */
	function testFormat( $expected, $unformattedText, $message = '' ) {
		$this->assertEquals(
			$expected,
			InstallDocFormatter::format( $unformattedText ),
			$message
		);
	}

	/**
	 * Provider for testFormat()
	 */
	public static function provideDocFormattingTests() {
		# Format: (expected string, unformattedText string, optional message)
		return array(
			# Escape some wikitext
			array( 'Install &lt;tag>', 'Install <tag>', 'Escaping <' ),
			array( 'Install &#123;&#123;template}}', 'Install {{template}}', 'Escaping [[' ),
			array( 'Install &#91;&#91;page]]', 'Install [[page]]', 'Escaping {{' ),
			array( 'Install ', "Install \r", 'Removing \r' ),

			# Transform \t{1,2} into :{1,2}
			array( ':One indentation', "\tOne indentation", 'Replacing a single \t' ),
			array( '::Two indentations', "\t\tTwo indentations", 'Replacing 2 x \t' ),

			# Transform 'bug 123' links
			array(
				'<span class="config-plainlink">[https://bugzilla.wikimedia.org/123 bug 123]</span>',
				'bug 123', 'Testing bug 123 links' ),
			array(
				'(<span class="config-plainlink">[https://bugzilla.wikimedia.org/987654 bug 987654]</span>)',
				'(bug 987654)', 'Testing (bug 987654) links' ),

			# "bug abc" shouldn't work
			array( 'bug foobar', 'bug foobar', "Don't match bug followed by non-digits" ),
			array( 'bug !!fakefake!!', 'bug !!fakefake!!', "Don't match bug followed by non-digits" ),

			# Transform '$wgFooBar' links
			array(
				'<span class="config-plainlink">[http://www.mediawiki.org/wiki/Manual:$wgFooBar $wgFooBar]</span>',
				'$wgFooBar', 'Testing basic $wgFooBar' ),
			array(
				'<span class="config-plainlink">[http://www.mediawiki.org/wiki/Manual:$wgFooBar45 $wgFooBar45]</span>',
				'$wgFooBar45', 'Testing $wgFooBar45 (with numbers)' ),
			array(
				'<span class="config-plainlink">[http://www.mediawiki.org/wiki/Manual:$wgFoo_Bar $wgFoo_Bar]</span>',
				'$wgFoo_Bar', 'Testing $wgFoo_Bar (with underscore)' ),

			# Icky variables that shouldn't link
			array( '$myAwesomeVariable', '$myAwesomeVariable', 'Testing $myAwesomeVariable (not starting with $wg)' ),
			array( '$()not!a&Var', '$()not!a&Var', 'Testing $()not!a&Var (obviously not a variable)' ),
		);
	}
}
