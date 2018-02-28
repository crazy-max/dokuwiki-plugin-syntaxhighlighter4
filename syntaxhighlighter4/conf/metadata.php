<?php
/**
 * Options for the syntaxhighlighter4 plugin.
 *
 * @author CrazyMax <webmaster@crazyws.fr>
 */

$meta['theme'] = array('multichoice', '_choices' => array(
    'theme-default.css',
    'theme-django.css',
    'theme-eclipse.css',
    'theme-emacs.css',
    'theme-fadetogrey.css',
    'theme-mdultra.css',
    'theme-midnight.css',
    'theme-rdark.css',
    'theme-swift.css',
));

// defaults
$meta['autoLinks'] = array('onoff');
$meta['firstLine'] = array('numeric');
$meta['gutter'] = array('onoff');
$meta['htmlScript'] = array('onoff');
$meta['smartTabs'] = array('onoff');
$meta['tabSize'] = array('numeric');
$meta['override'] = array('multicheckbox', '_choices' => array('code','file'));
