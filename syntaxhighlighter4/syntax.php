<?php
/**
 * DokuWiki Plugin syntaxhighlighter4 (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Cr@zy <webmaster@crazyws.fr>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_syntaxhighlighter4 extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'protected';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 195;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<sxh(?=[^\r\n]*?>.*?</sxh>)',$mode,'plugin_syntaxhighlighter4');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</sxh>','plugin_syntaxhighlighter4');
    }

    /**
     * Handle matches of the syntaxhighlighter4 syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $this->syntax = substr($match, 1);
                return false;

            case DOKU_LEXER_UNMATCHED:
                // will include everything from <sxh ... to ... </sxh>
                list($attr, $content) = preg_split('/>/u',$match,2);

                if ($this->syntax == 'sxh') {
                    $attr = trim($attr);
                    if ($attr == NULL) {
                        // No brush and no options, use "text" with default options.
                        $attr = 'text';
                    } else if (substr($attr,0,1) == ";") {
                        // Options but no brush, add "text" brush.
                        $attr = 'text' . $attr;
                    }
                } else {
                    $attr = NULL;
                }

                return array($this->syntax, $attr, $content);
        }

        return false;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        if($mode != 'xhtml') return false;

        if (count($data) != 3) {
            return true;
        }

        list($syntax, $attr, $content) = $data;
        if ($syntax == 'sxh') {
            $title = $this->procTitle($attr);
            $highlight = $this->procHighlight($attr);
            $renderer->doc .= '<pre class="brush: ' . strtolower($attr . $highlight) . '"' . $title . '>' . $renderer->_xmlEntities($content) . '</pre>';
        } else {
            $renderer->file($content);
        }

        return true;
    }

    private function procTitle(&$attr) {
        $title = "";

        if (preg_match("/title:/i", $attr)) {
            // Extract title(s) from $attr string.
            $attr_array = explode(";",$attr);
            $title_array = preg_grep("/title:/i", $attr_array);
            // Extract everything BUT title(s) from $attr string.
            $not_title_array =  preg_grep("/title:/i", $attr_array, PREG_GREP_INVERT);
            $attr = implode(";",$not_title_array);
            // If there are multiple titles, use the last one.
            $title = array_pop($title_array);
            $title = ' title="' . preg_replace("/.*title:\s{0,}(.*)/i","$1", $title) . '"';
        }

        return $title;
    }

    private function procHighlight(&$attr) {
        $highlight = "";

        // Check highlight attr for lines ranges
        if (preg_match("/highlight:/i", $attr, $matches)) {
            // Extract highlight from $attr string.
            $attr_array = explode(";",$attr);
            $highlight_array = preg_grep("/highlight:/i", $attr_array);
            // Extract everything BUT highlight from $attr string.
            $not_highlight_array = preg_grep("/highlight:/i", $attr_array, PREG_GREP_INVERT);
            $attr = implode(";",$not_highlight_array);
            // If there are multiple hightlights, use the last one.
            $highlight_str = array_pop($highlight_array);
            $highlight_str = preg_replace("/.*highlight:\s{0,}(.*)/i","$1", $highlight_str);
            // Remove [ ]
            $highlight_str = str_replace(array('[', ']'), '', $highlight_str);
            // Process ranges if exists
            $highlight_exp = explode(',', $highlight_str);
            foreach ($highlight_exp as $highlight_elt) {
                if (!empty($highlight)) {
                    $highlight .= ',';
                }
                $highlight_elt = trim($highlight_elt);
                $highlight_elt_exp = explode('-', $highlight_elt);
                if (count($highlight_elt_exp) == 2) {
                    foreach (range($highlight_elt_exp[0], $highlight_elt_exp[1]) as $key => $lineNumber) {
                        if ($key > 0) {
                            $highlight .= ',';
                        }
                        $highlight .= $lineNumber;
                    }
                } else {
                    $highlight .= $highlight_elt;
                }
            }
            $highlight = ' highlight: [' . $highlight . ']';
        }

        return $highlight;
    }
}

// vim:ts=4:sw=4:et:
